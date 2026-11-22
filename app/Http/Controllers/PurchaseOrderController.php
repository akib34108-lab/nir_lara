<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use App\Models\PurchaseRequest;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseOrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = PurchaseOrder::query()
            ->with(['supplier', 'purchaseRequest'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $query->where('po_number', 'like', '%'.$request->search.'%');
            })
            ->when($request->filled('supplier'), function ($query) use ($request) {
                $query->where('supplier_id', $request->supplier);
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->latest()
            ->paginate(10);

        $suppliers = Supplier::where('status', 1)->orderBy('company_name')->get();

        return view('purchase-orders.index', compact('orders', 'suppliers'));
    }

    public function create(Request $request)
    {
        $suppliers = Supplier::where('status', 1)->orderBy('company_name')->get();
        $purchaseRequests = PurchaseRequest::whereIn('status', ['Approved', 'Partially Approved'])
            ->with(['project', 'items.material'])
            ->latest()
            ->get();

        $selectedRequest = null;
        if ($request->filled('purchase_request')) {
            $selectedRequest = $purchaseRequests->firstWhere('id', (int) $request->purchase_request);
        }

        return view('purchase-orders.create', compact('suppliers', 'purchaseRequests', 'selectedRequest'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'purchase_request_id' => 'required|exists:purchase_requests,id',
            'supplier_id' => 'required|exists:suppliers,id',
            'order_date' => 'required|date',
            'expected_delivery_date' => 'nullable|date|after_or_equal:order_date',
            'payment_terms' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'discount' => 'required|numeric|min:0',
            'tax' => 'required|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.purchase_request_item_id' => 'required|exists:purchase_request_items,id',
            'items.*.material_id' => 'required|exists:materials,id',
            'items.*.ordered_quantity' => 'required|numeric|min:0.001',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        $purchaseOrder = DB::transaction(function () use ($validated, $request) {
            $purchaseRequest = PurchaseRequest::whereKey($validated['purchase_request_id'])
                ->lockForUpdate()
                ->firstOrFail();

            if (! in_array($purchaseRequest->status, ['Approved', 'Partially Approved'], true)) {
                abort(422, 'Purchase orders can only be created from approved purchase requests.');
            }

            $nextId = (PurchaseOrder::withTrashed()->max('id') ?? 0) + 1;
            $poNumber = 'PO-'.now()->format('Y').'-'.str_pad($nextId, 5, '0', STR_PAD_LEFT);

            $purchaseOrder = PurchaseOrder::create([
                'po_number' => $poNumber,
                'purchase_request_id' => $purchaseRequest->id,
                'supplier_id' => $validated['supplier_id'],
                'created_by' => $request->user()->id,
                'order_date' => $validated['order_date'],
                'expected_delivery_date' => $validated['expected_delivery_date'] ?? null,
                'payment_terms' => $validated['payment_terms'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'discount' => $validated['discount'],
                'tax' => $validated['tax'],
                'grand_total' => 0,
                'status' => 'Draft',
            ]);

            $subtotalTotal = 0;

            foreach ($validated['items'] as $item) {
                $requestItem = $purchaseRequest->items()
                    ->whereKey($item['purchase_request_item_id'])
                    ->lockForUpdate()
                    ->first();

                if (! $requestItem || (int) $requestItem->material_id !== (int) $item['material_id']) {
                    abort(422, 'An item does not belong to the selected purchase request.');
                }

                $alreadyOrdered = $requestItem->purchaseOrdersRelationQuery()
                    ->whereHas('purchaseOrder', function ($query) {
                        $query->where('status', '!=', 'Cancelled');
                    })
                    ->sum('ordered_quantity');

                $remaining = (float) $requestItem->approved_quantity - (float) $alreadyOrdered;
                if ((float) $item['ordered_quantity'] > $remaining + 0.0001) {
                    abort(422, 'Ordered quantity cannot exceed the approved remaining quantity.');
                }

                $subtotal = round((float) $item['ordered_quantity'] * (float) $item['unit_price'], 2);
                $subtotalTotal += $subtotal;

                $purchaseOrder->items()->create([
                    'purchase_request_item_id' => $requestItem->id,
                    'material_id' => $requestItem->material_id,
                    'ordered_quantity' => $item['ordered_quantity'],
                    'unit_price' => $item['unit_price'],
                    'subtotal' => $subtotal,
                ]);
            }

            $grandTotal = max(0, round($subtotalTotal - (float) $validated['discount'] + (float) $validated['tax'], 2));
            $purchaseOrder->update(['grand_total' => $grandTotal]);

            return $purchaseOrder;
        });

        return redirect()->route('purchase-orders.show', $purchaseOrder)
            ->with('success', 'Purchase order created successfully.');
    }

    public function show(PurchaseOrder $purchase_order)
    {
        $purchase_order->load([
            'supplier',
            'purchaseRequest.project',
            'creator',
            'approver',
            'items.material',
            'items.purchaseRequestItem',
        ]);

        return view('purchase-orders.show', compact('purchase_order'));
    }

    public function edit(PurchaseOrder $purchase_order)
    {
        if (! in_array($purchase_order->status, ['Draft', 'Pending Approval'], true)) {
            return redirect()->route('purchase-orders.show', $purchase_order)
                ->with('error', 'Only draft or pending approval purchase orders can be edited.');
        }

        $purchase_order->load(['items', 'purchaseRequest.items.material']);
        $suppliers = Supplier::where('status', 1)->orderBy('company_name')->get();

        return view('purchase-orders.edit', compact('purchase_order', 'suppliers'));
    }

    public function update(Request $request, PurchaseOrder $purchase_order)
    {
        if (! in_array($purchase_order->status, ['Draft', 'Pending Approval'], true)) {
            return redirect()->route('purchase-orders.show', $purchase_order)
                ->with('error', 'Only draft or pending approval purchase orders can be edited.');
        }

        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'order_date' => 'required|date',
            'expected_delivery_date' => 'nullable|date|after_or_equal:order_date',
            'payment_terms' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'discount' => 'required|numeric|min:0',
            'tax' => 'required|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|exists:purchase_order_items,id',
            'items.*.ordered_quantity' => 'required|numeric|min:0.001',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($validated, $purchase_order) {
            $lockedOrder = PurchaseOrder::whereKey($purchase_order->id)->lockForUpdate()->firstOrFail();
            if (! in_array($lockedOrder->status, ['Draft', 'Pending Approval'], true)) {
                abort(422, 'Only draft or pending approval purchase orders can be edited.');
            }

            $lockedOrder->update([
                'supplier_id' => $validated['supplier_id'],
                'order_date' => $validated['order_date'],
                'expected_delivery_date' => $validated['expected_delivery_date'] ?? null,
                'payment_terms' => $validated['payment_terms'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'discount' => $validated['discount'],
                'tax' => $validated['tax'],
            ]);

            $subtotalTotal = 0;
            foreach ($validated['items'] as $item) {
                $orderItem = $lockedOrder->items()->whereKey($item['id'])->lockForUpdate()->firstOrFail();
                $requestItem = $orderItem->purchaseRequestItem()->lockForUpdate()->first();

                $alreadyOrdered = 0;
                if ($requestItem) {
                    $alreadyOrdered = $requestItem->purchaseOrdersRelationQuery()
                        ->whereHas('purchaseOrder', function ($query) use ($lockedOrder) {
                            $query->where('status', '!=', 'Cancelled')
                                ->where('purchase_orders.id', '!=', $lockedOrder->id);
                        })
                        ->sum('ordered_quantity');

                    $remaining = (float) $requestItem->approved_quantity - (float) $alreadyOrdered;
                    if ((float) $item['ordered_quantity'] > $remaining + 0.0001) {
                        abort(422, 'Ordered quantity cannot exceed the approved remaining quantity.');
                    }
                }

                $subtotal = round((float) $item['ordered_quantity'] * (float) $item['unit_price'], 2);
                $subtotalTotal += $subtotal;
                $orderItem->update([
                    'ordered_quantity' => $item['ordered_quantity'],
                    'unit_price' => $item['unit_price'],
                    'subtotal' => $subtotal,
                ]);
            }

            $grandTotal = max(0, round($subtotalTotal - (float) $validated['discount'] + (float) $validated['tax'], 2));
            $lockedOrder->update(['grand_total' => $grandTotal]);
        });

        return redirect()->route('purchase-orders.show', $purchase_order)
            ->with('success', 'Purchase order updated successfully.');
    }

    public function destroy(PurchaseOrder $purchase_order)
    {
        if ($purchase_order->status !== 'Draft') {
            return redirect()->route('purchase-orders.index')
                ->with('error', 'Only draft purchase orders can be deleted.');
        }

        $purchase_order->delete();

        return redirect()->route('purchase-orders.index')
            ->with('success', 'Purchase order deleted successfully.');
    }

    public function submitApproval(PurchaseOrder $purchase_order)
    {
        DB::transaction(function () use ($purchase_order) {
            $lockedOrder = PurchaseOrder::whereKey($purchase_order->id)->lockForUpdate()->firstOrFail();
            if ($lockedOrder->status !== 'Draft') {
                abort(422, 'Only draft purchase orders can be submitted for approval.');
            }
            $lockedOrder->update(['status' => 'Pending Approval']);
        });

        return redirect()->route('purchase-orders.show', $purchase_order)
            ->with('success', 'Purchase order submitted for approval.');
    }

    public function approve(Request $request, PurchaseOrder $purchase_order)
    {
        DB::transaction(function () use ($request, $purchase_order) {
            $lockedOrder = PurchaseOrder::whereKey($purchase_order->id)->lockForUpdate()->firstOrFail();
            if ($lockedOrder->status !== 'Pending Approval') {
                abort(422, 'Only purchase orders awaiting approval can be approved.');
            }
            if ($lockedOrder->created_by === $request->user()->id) {
                abort(403, 'You cannot approve a purchase order you created.');
            }

            $lockedOrder->update([
                'status' => 'Approved',
                'approved_by' => $request->user()->id,
                'approved_at' => now(),
            ]);
        });

        return redirect()->route('purchase-orders.show', $purchase_order)
            ->with('success', 'Purchase order approved.');
    }

    public function cancel(PurchaseOrder $purchase_order)
    {
        DB::transaction(function () use ($purchase_order) {
            $lockedOrder = PurchaseOrder::whereKey($purchase_order->id)->lockForUpdate()->firstOrFail();
            if (in_array($lockedOrder->status, ['Completed', 'Cancelled', 'Partially Received'], true)) {
                abort(422, 'This purchase order cannot be cancelled.');
            }
            $lockedOrder->update(['status' => 'Cancelled']);
        });

        return redirect()->route('purchase-orders.show', $purchase_order)
            ->with('success', 'Purchase order cancelled.');
    }
}
