<?php

namespace App\Http\Controllers;

use App\Models\GoodsReceivedNote;
use App\Models\MaterialInventory;
use App\Models\PurchaseOrder;
use App\Models\StockTransaction;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class GoodsReceivedNoteController extends Controller
{
    public function index(Request $request)
    {
        $grns = GoodsReceivedNote::query()
            ->with(['purchaseOrder', 'warehouse', 'receiver'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $query->where('grn_number', 'like', '%'.$request->search.'%');
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->latest()
            ->paginate(10);
        $warehouses = Warehouse::where('status', 1)->orderBy('name')->get();

        return view('grns.index', compact('grns', 'warehouses'));
    }

    public function create(Request $request)
    {
        $purchaseOrders = PurchaseOrder::whereIn('status', ['Approved', 'Partially Received'])
            ->with(['supplier', 'items.material'])
            ->latest()
            ->get();
        $selectedOrder = $request->filled('purchase_order')
            ? $purchaseOrders->firstWhere('id', (int) $request->purchase_order)
            : null;

        return view('grns.create', compact('purchaseOrders', 'selectedOrder'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'purchase_order_id' => 'required|exists:purchase_orders,id',
            'warehouse_name' => 'required|string|max:255',
            'received_date' => 'required|date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.purchase_order_item_id' => 'required|exists:purchase_order_items,id',
            'items.*.material_id' => 'required|exists:materials,id',
            'items.*.received_quantity' => 'required|numeric|min:0.001',
        ]);

        $grn = DB::transaction(function () use ($validated, $request) {
            $purchaseOrder = PurchaseOrder::whereKey($validated['purchase_order_id'])
                ->lockForUpdate()
                ->firstOrFail();
            if (! in_array($purchaseOrder->status, ['Approved', 'Partially Received'], true)) {
                abort(422, 'GRNs can only be created for approved purchase orders.');
            }

            $warehouse = $this->resolveWarehouse($validated['warehouse_name']);
            $nextId = (GoodsReceivedNote::withTrashed()->max('id') ?? 0) + 1;
            $grn = GoodsReceivedNote::create([
                'grn_number' => 'GRN-'.now()->format('Y').'-'.str_pad($nextId, 5, '0', STR_PAD_LEFT),
                'purchase_order_id' => $purchaseOrder->id,
                'warehouse_id' => $warehouse->id,
                'received_by' => $request->user()->id,
                'received_date' => $validated['received_date'],
                'notes' => $validated['notes'] ?? null,
                'status' => 'Draft',
            ]);

            foreach ($validated['items'] as $item) {
                $poItem = $purchaseOrder->items()
                    ->whereKey($item['purchase_order_item_id'])
                    ->lockForUpdate()
                    ->first();
                if (! $poItem || (int) $poItem->material_id !== (int) $item['material_id']) {
                    abort(422, 'A GRN item does not belong to the selected purchase order.');
                }

                $alreadyReceived = $poItem->grnItems()
                    ->whereHas('grn', function ($query) {
                        $query->where('status', '!=', 'Cancelled');
                    })
                    ->sum('received_quantity');
                $remaining = (float) $poItem->ordered_quantity - (float) $alreadyReceived;
                if ((float) $item['received_quantity'] > $remaining + 0.0001) {
                    abort(422, 'Received quantity cannot exceed the ordered remaining quantity.');
                }

                $grn->items()->create([
                    'purchase_order_item_id' => $poItem->id,
                    'material_id' => $poItem->material_id,
                    'ordered_quantity' => $poItem->ordered_quantity,
                    'received_quantity' => $item['received_quantity'],
                    'accepted_quantity' => 0,
                    'rejected_quantity' => 0,
                ]);
            }

            return $grn;
        });

        return redirect()->route('grns.show', $grn)
            ->with('success', 'GRN created successfully.');
    }

    public function show(GoodsReceivedNote $grn)
    {
        $grn->load(['purchaseOrder.supplier', 'warehouse', 'receiver', 'items.material', 'items.inspections.inspector']);

        return view('grns.show', compact('grn'));
    }

    public function destroy(GoodsReceivedNote $grn)
    {
        if ($grn->status !== 'Draft') {
            return redirect()->route('grns.index')
                ->with('error', 'Only draft GRNs can be deleted.');
        }
        $grn->delete();

        return redirect()->route('grns.index')->with('success', 'GRN deleted successfully.');
    }

    public function confirm(Request $request, GoodsReceivedNote $grn)
    {
        if ($grn->status !== 'Inspected') {
            return redirect()->route('grns.show', $grn)
                ->with('error', 'The GRN must be fully inspected before confirmation.');
        }

        DB::transaction(function () use ($request, $grn) {
            $lockedGrn = GoodsReceivedNote::whereKey($grn->id)->lockForUpdate()->firstOrFail();
            if ($lockedGrn->status !== 'Inspected') {
                abort(422, 'The GRN must be fully inspected before confirmation.');
            }
            if (StockTransaction::where('reference_type', GoodsReceivedNote::class)
                ->where('reference_id', $lockedGrn->id)
                ->exists()) {
                abort(422, 'This GRN has already been posted to inventory.');
            }

            foreach ($lockedGrn->items()->lockForUpdate()->get() as $item) {
                $accepted = (float) $item->accepted_quantity;
                if ($accepted <= 0) {
                    continue;
                }

                $inventory = MaterialInventory::where('warehouse_id', $lockedGrn->warehouse_id)
                    ->where('material_id', $item->material_id)
                    ->lockForUpdate()
                    ->first();

                if (! $inventory) {
                    $inventory = MaterialInventory::create([
                        'warehouse_id' => $lockedGrn->warehouse_id,
                        'material_id' => $item->material_id,
                        'quantity' => 0,
                    ]);
                }

                $inventory->quantity = (float) $inventory->quantity + $accepted;
                $poItem = $item->purchaseOrderItem()->with('purchaseOrder')->first();
                $unitCost = $poItem ? (float) $poItem->unit_price : 0;
                $previousQuantity = (float) $inventory->quantity - $accepted;
                $previousCost = (float) $inventory->average_cost;
                $inventory->average_cost = $previousQuantity + $accepted > 0
                    ? round((($previousQuantity * $previousCost) + ($accepted * $unitCost)) / ($previousQuantity + $accepted), 2)
                    : 0;
                $inventory->save();

                StockTransaction::create([
                    'warehouse_id' => $lockedGrn->warehouse_id,
                    'material_id' => $item->material_id,
                    'transaction_type' => 'GRN_RECEIPT',
                    'quantity' => $accepted,
                    'balance_quantity' => $inventory->quantity,
                    'reference_type' => GoodsReceivedNote::class,
                    'reference_id' => $lockedGrn->id,
                    'created_by' => $request->user()->id,
                    'notes' => 'Accepted quantity from GRN '.$lockedGrn->grn_number,
                ]);
            }

            $lockedGrn->update([
                'status' => 'Confirmed',
                'confirmed_at' => now(),
            ]);

            $purchaseOrder = $lockedGrn->purchaseOrder()->lockForUpdate()->firstOrFail();
            $allItemsReceived = true;
            foreach ($purchaseOrder->items as $poItem) {
                $received = (float) $poItem->grnItems()
                    ->whereHas('grn', function ($query) {
                        $query->where('status', 'Confirmed');
                    })
                    ->sum('received_quantity');
                if ($received + 0.0001 < (float) $poItem->ordered_quantity) {
                    $allItemsReceived = false;
                }
            }
            $purchaseOrder->update(['status' => $allItemsReceived ? 'Completed' : 'Partially Received']);
        });

        return redirect()->route('grns.show', $grn)
            ->with('success', 'GRN confirmed and accepted quantities posted to inventory.');
    }

    public function storeWarehouse(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:warehouses,code',
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
        ]);
        Warehouse::create($validated + ['status' => 1]);

        return redirect()->back()->with('success', 'Warehouse/location created successfully.');
    }

    private function resolveWarehouse(string $name): Warehouse
    {
        $name = trim($name);
        $warehouse = Warehouse::whereRaw('LOWER(name) = ?', [Str::lower($name)])->first();

        if ($warehouse) {
            if ((int) $warehouse->status !== 1) {
                throw ValidationException::withMessages([
                    'warehouse_name' => 'The entered warehouse is inactive.',
                ]);
            }

            return $warehouse;
        }

        $slug = Str::upper(Str::slug($name)) ?: 'LOCATION';
        $baseCode = 'WH-'.substr($slug, 0, 47);
        $code = $baseCode;
        $suffix = 1;

        while (Warehouse::withTrashed()->where('code', $code)->exists()) {
            $tail = '-'.$suffix++;
            $code = substr($baseCode, 0, 50 - strlen($tail)).$tail;
        }

        return Warehouse::create([
            'code' => $code,
            'name' => $name,
            'status' => 1,
        ]);
    }
}
