<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\MaterialInventory;
use App\Models\StockTransaction;
use App\Models\StockTransfer;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockTransferController extends Controller
{
    public function index(Request $request)
    {
        $transfers = StockTransfer::with(['sourceWarehouse', 'destinationWarehouse', 'responsibleUser'])
            ->when($request->filled('search'), fn ($query) => $query->where('transfer_number', 'like', '%'.$request->search.'%'))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->status))
            ->latest()->paginate(10);

        return view('stock-transfers.index', compact('transfers'));
    }

    public function create()
    {
        $warehouses = Warehouse::where('status', 1)->orderBy('name')->get();
        $materials = Material::where('status', 1)->orderBy('name')->get();

        return view('stock-transfers.create', compact('warehouses', 'materials'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'source_warehouse_id' => 'required|exists:warehouses,id|different:destination_warehouse_id',
            'destination_warehouse_id' => 'required|exists:warehouses,id',
            'transfer_date' => 'required|date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.material_id' => 'required|exists:materials,id',
            'items.*.quantity' => 'required|numeric|min:0.001',
        ]);

        $transfer = DB::transaction(function () use ($validated, $request) {
            $nextId = (StockTransfer::withTrashed()->max('id') ?? 0) + 1;
            $transfer = StockTransfer::create([
                'transfer_number' => 'ST-'.now()->format('Y').'-'.str_pad($nextId, 5, '0', STR_PAD_LEFT),
                'source_warehouse_id' => $validated['source_warehouse_id'],
                'destination_warehouse_id' => $validated['destination_warehouse_id'],
                'responsible_user_id' => $request->user()->id,
                'transfer_date' => $validated['transfer_date'],
                'notes' => $validated['notes'] ?? null,
                'status' => 'Pending',
            ]);
            foreach ($validated['items'] as $item) {
                $transfer->items()->create([
                    'material_id' => $item['material_id'],
                    'quantity' => $item['quantity'],
                    'received_quantity' => 0,
                ]);
            }

            return $transfer;
        });

        return redirect()->route('stock-transfers.show', $transfer)->with('success', 'Stock transfer created successfully.');
    }

    public function show(StockTransfer $stock_transfer)
    {
        $stock_transfer->load(['sourceWarehouse', 'destinationWarehouse', 'responsibleUser', 'receiver', 'items.material']);

        return view('stock-transfers.show', compact('stock_transfer'));
    }

    public function destroy(StockTransfer $stock_transfer)
    {
        if ($stock_transfer->status !== 'Pending') {
            return redirect()->route('stock-transfers.index')->with('error', 'Only pending transfers can be deleted.');
        }
        $stock_transfer->delete();

        return redirect()->route('stock-transfers.index')->with('success', 'Stock transfer deleted successfully.');
    }

    public function dispatch(Request $request, StockTransfer $stock_transfer)
    {
        DB::transaction(function () use ($request, $stock_transfer) {
            $lockedTransfer = StockTransfer::whereKey($stock_transfer->id)->lockForUpdate()->firstOrFail();
            if ($lockedTransfer->status !== 'Pending') {
                abort(422, 'Only pending transfers can be dispatched.');
            }
            if (StockTransaction::where('reference_type', StockTransfer::class)
                ->where('reference_id', $lockedTransfer->id)
                ->where('transaction_type', 'STOCK_TRANSFER_OUT')
                ->exists()) {
                abort(422, 'This transfer has already been dispatched.');
            }

            foreach ($lockedTransfer->items()->with('material')->lockForUpdate()->get() as $item) {
                $inventory = MaterialInventory::where('warehouse_id', $lockedTransfer->source_warehouse_id)
                    ->where('material_id', $item->material_id)
                    ->lockForUpdate()->first();
                $available = $inventory ? (float) $inventory->quantity - (float) $inventory->reserved_quantity : 0;
                $quantity = (float) $item->quantity;
                if ($available + 0.0001 < $quantity) {
                    abort(422, 'Insufficient stock for '.$item->material->name.'.');
                }
                $inventory->quantity = (float) $inventory->quantity - $quantity;
                $inventory->save();
                StockTransaction::create([
                    'warehouse_id' => $lockedTransfer->source_warehouse_id,
                    'material_id' => $item->material_id,
                    'transaction_type' => 'STOCK_TRANSFER_OUT',
                    'quantity' => -$quantity,
                    'balance_quantity' => $inventory->quantity,
                    'reference_type' => StockTransfer::class,
                    'reference_id' => $lockedTransfer->id,
                    'created_by' => $request->user()->id,
                    'notes' => 'Stock transfer dispatched: '.$lockedTransfer->transfer_number,
                ]);
            }
            $lockedTransfer->update(['status' => 'In Transit']);
        });

        return redirect()->route('stock-transfers.show', $stock_transfer)->with('success', 'Transfer dispatched; source stock reduced.');
    }

    public function receive(Request $request, StockTransfer $stock_transfer)
    {
        DB::transaction(function () use ($request, $stock_transfer) {
            $lockedTransfer = StockTransfer::whereKey($stock_transfer->id)->lockForUpdate()->firstOrFail();
            if ($lockedTransfer->status !== 'In Transit') {
                abort(422, 'Only in-transit transfers can be received.');
            }
            if (StockTransaction::where('reference_type', StockTransfer::class)
                ->where('reference_id', $lockedTransfer->id)
                ->where('transaction_type', 'STOCK_TRANSFER_IN')
                ->exists()) {
                abort(422, 'This transfer has already been received.');
            }

            foreach ($lockedTransfer->items()->lockForUpdate()->get() as $item) {
                $quantity = (float) $item->quantity;
                $item->update(['received_quantity' => $quantity]);
                $inventory = MaterialInventory::where('warehouse_id', $lockedTransfer->destination_warehouse_id)
                    ->where('material_id', $item->material_id)
                    ->lockForUpdate()->first();
                if (! $inventory) {
                    $inventory = MaterialInventory::create([
                        'warehouse_id' => $lockedTransfer->destination_warehouse_id,
                        'material_id' => $item->material_id,
                        'quantity' => 0,
                        'reserved_quantity' => 0,
                        'average_cost' => 0,
                    ]);
                }
                $inventory->quantity = (float) $inventory->quantity + $quantity;
                $inventory->save();
                StockTransaction::create([
                    'warehouse_id' => $lockedTransfer->destination_warehouse_id,
                    'material_id' => $item->material_id,
                    'transaction_type' => 'STOCK_TRANSFER_IN',
                    'quantity' => $quantity,
                    'balance_quantity' => $inventory->quantity,
                    'reference_type' => StockTransfer::class,
                    'reference_id' => $lockedTransfer->id,
                    'created_by' => $request->user()->id,
                    'notes' => 'Stock transfer received: '.$lockedTransfer->transfer_number,
                ]);
            }
            $lockedTransfer->update([
                'status' => 'Received',
                'received_by' => $request->user()->id,
                'received_at' => now(),
            ]);
        });

        return redirect()->route('stock-transfers.show', $stock_transfer)->with('success', 'Transfer received; destination stock increased.');
    }

    public function cancel(StockTransfer $stock_transfer)
    {
        if ($stock_transfer->status !== 'Pending') {
            return redirect()->route('stock-transfers.show', $stock_transfer)->with('error', 'Only pending transfers can be cancelled.');
        }
        $stock_transfer->update(['status' => 'Cancelled']);

        return redirect()->route('stock-transfers.show', $stock_transfer)->with('success', 'Stock transfer cancelled.');
    }
}
