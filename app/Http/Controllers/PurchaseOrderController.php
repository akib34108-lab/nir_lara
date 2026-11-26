<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\PurchaseRequest;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

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
        $purchaseRequests = $this->approvedPurchaseRequests();
        $selectedRequest = $request->filled('purchase_request')
            ? $purchaseRequests->firstWhere('id', (int) $request->purchase_request)
            : null;

        if (! $selectedRequest) {
            return view('purchase-orders.create', compact('purchaseRequests', 'selectedRequest'));
        }

        return $this->purchaseOrderGenerationForm($selectedRequest, $purchaseRequests);
    }

    public function createForRequest(PurchaseRequest $purchase_request)
    {
        if (! in_array($purchase_request->status, ['Approved', 'Partially Approved'], true)) {
            abort(422, 'Purchase orders can only be created from approved purchase requests.');
        }

        return $this->purchaseOrderGenerationForm($purchase_request, $this->approvedPurchaseRequests());
    }

    private function purchaseOrderGenerationForm(PurchaseRequest $purchaseRequest, $purchaseRequests)
    {
        $purchaseRequest->load(['project', 'items.material']);
        $selectedRequest = $purchaseRequest;
        $orderedQuantities = $this->orderedQuantities($purchaseRequest->items);
        $remainingQuantities = [];
        foreach ($purchaseRequest->items as $item) {
            $remainingQuantities[$item->id] = max(
                0,
                (float) $item->approved_quantity - ($orderedQuantities[$item->id] ?? 0)
            );
        }
        $hasRemainingQuantities = collect($remainingQuantities)->contains(fn ($quantity) => $quantity > 0.0001);
        $suppliers = Supplier::where('status', 1)->orderBy('company_name')->get();

        return view('purchase-orders.create', compact(
            'purchaseRequest',
            'purchaseRequests',
            'selectedRequest',
            'remainingQuantities',
            'orderedQuantities',
            'hasRemainingQuantities',
            'suppliers'
        ));
    }

    private function approvedPurchaseRequests()
    {
        return PurchaseRequest::whereIn('status', ['Approved', 'Partially Approved'])
            ->whereHas('items', function ($query) {
                $query->where('approved_quantity', '>', 0)
                    ->whereRaw('
                        approved_quantity > (
                            SELECT COALESCE(SUM(poi.ordered_quantity), 0)
                            FROM purchase_order_items AS poi
                            INNER JOIN purchase_orders AS po
                                ON po.id = poi.purchase_order_id
                            WHERE poi.purchase_request_item_id = purchase_request_items.id
                            AND po.deleted_at IS NULL
                            AND po.status != "Cancelled"
                        )
                    ');
            })
            ->with('project')
            ->latest()
            ->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'purchase_request_id' => 'required|exists:purchase_requests,id',
            'supplier_id' => [
                'required',
                'integer',
                Rule::exists('suppliers', 'id')->where('status', 1)->whereNull('deleted_at'),
            ],
            'order_date' => 'required|date',
            'expected_delivery_date' => 'nullable|date|after_or_equal:order_date',
            'payment_terms' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'discount' => 'required|numeric|min:0|max:9999999999999.99|decimal:0,2',
            'tax' => 'required|numeric|min:0|max:9999999999999.99|decimal:0,2',
            'items' => 'required|array|min:1',
            'items.*.purchase_request_item_id' => 'required|exists:purchase_request_items,id',
            'items.*.material_id' => 'required|exists:materials,id',
            'items.*.ordered_quantity' => 'required|numeric|min:0.001|max:999999999999.999|decimal:0,3',
            'items.*.unit_price' => 'required|numeric|min:0|max:9999999999999.99|decimal:0,2',
        ]);

        $purchaseOrder = DB::transaction(function () use ($validated, $request) {
            $purchaseRequest = PurchaseRequest::whereKey($validated['purchase_request_id'])
                ->lockForUpdate()
                ->firstOrFail();

            if (! in_array($purchaseRequest->status, ['Approved', 'Partially Approved'], true)) {
                abort(422, 'Purchase orders can only be created from approved purchase requests.');
            }

            $supplier = Supplier::whereKey($validated['supplier_id'])
                ->where('status', 1)
                ->lockForUpdate()
                ->first();
            if (! $supplier) {
                throw ValidationException::withMessages([
                    'supplier_id' => 'The selected supplier is no longer active.',
                ]);
            }

            $purchaseOrder = $this->createPurchaseOrder([
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

            foreach ($validated['items'] as $index => $item) {
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
                if (! is_finite($subtotal) || $subtotal > 9999999999999.99) {
                    throw ValidationException::withMessages([
                        "items.$index.unit_price" => 'The item subtotal exceeds the maximum supported amount.',
                    ]);
                }
                $subtotalTotal += $subtotal;
                if ($subtotalTotal > 9999999999999.99) {
                    throw ValidationException::withMessages([
                        'items' => 'The purchase order total exceeds the maximum supported amount.',
                    ]);
                }

                $purchaseOrder->items()->create([
                    'purchase_request_item_id' => $requestItem->id,
                    'material_id' => $requestItem->material_id,
                    'ordered_quantity' => $item['ordered_quantity'],
                    'unit_price' => $item['unit_price'],
                    'subtotal' => $subtotal,
                ]);
            }

            $grandTotal = round($subtotalTotal - (float) $validated['discount'] + (float) $validated['tax'], 2);
            if (! is_finite($grandTotal) || $grandTotal > 9999999999999.99) {
                throw ValidationException::withMessages([
                    'tax' => 'The purchase order total exceeds the maximum supported amount.',
                ]);
            }
            $grandTotal = max(0, $grandTotal);
            $purchaseOrder->update(['grand_total' => $grandTotal]);

            return $purchaseOrder;
        });

        return redirect()->route('purchase-orders.show', $purchaseOrder)
            ->with('success', 'Purchase order created successfully.');
    }

    public function storeForRequest(Request $request, PurchaseRequest $purchase_request)
    {
        $validated = $request->validate([
            'order_date' => 'required|date',
            'expected_delivery_date' => 'nullable|date|after_or_equal:order_date',
            'payment_terms' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*' => 'array',
            'items.*.supplier_id' => [
                'nullable',
                'integer',
                Rule::exists('suppliers', 'id')->where('status', 1)->whereNull('deleted_at'),
            ],
            'items.*.ordered_quantity' => 'nullable|numeric|min:0|max:999999999999.999|decimal:0,3',
            'items.*.unit_price' => 'nullable|numeric|min:0|max:9999999999999.99|decimal:0,2',
            'items.*.discount' => 'nullable|numeric|min:0|max:9999999999999.99|decimal:0,2',
            'items.*.tax' => 'nullable|numeric|min:0|max:9999999999999.99|decimal:0,2',
        ]);

        $selectedItems = [];
        $selectionErrors = [];
        foreach ($validated['items'] as $itemId => $item) {
            $supplierId = $item['supplier_id'] ?? null;
            $quantity = (string) ($item['ordered_quantity'] ?? '0');
            $unitPrice = $item['unit_price'] ?? null;
            $discount = $item['discount'] ?? null;
            $tax = $item['tax'] ?? null;

            if ($this->quantityInMilliunits($quantity) <= 0) {
                continue;
            }
            if ($supplierId === null || $supplierId === '') {
                $selectionErrors["items.$itemId.supplier_id"] = 'Select a supplier for each quantity to order.';
            }
            if ($unitPrice === null || $unitPrice === '') {
                $selectionErrors["items.$itemId.unit_price"] = 'Enter a unit price for each selected item.';
            }
            if ($discount === null || $discount === '') {
                $selectionErrors["items.$itemId.discount"] = 'Enter a discount amount for this material (use 0 if none).';
            }
            if ($tax === null || $tax === '') {
                $selectionErrors["items.$itemId.tax"] = 'Enter a tax amount for this material (use 0 if none).';
            }

            if (
                $supplierId !== null && $supplierId !== ''
                && $unitPrice !== null && $unitPrice !== ''
                && $discount !== null && $discount !== ''
                && $tax !== null && $tax !== ''
            ) {
                $selectedItems[] = [
                    'purchase_request_item_id' => (int) $itemId,
                    'supplier_id' => (int) $supplierId,
                    'ordered_quantity' => $quantity,
                    'unit_price' => (string) $unitPrice,
                    'discount' => (string) $discount,
                    'tax' => (string) $tax,
                ];
            }
        }

        if ($selectionErrors) {
            throw ValidationException::withMessages($selectionErrors);
        }
        if (! $selectedItems) {
            throw ValidationException::withMessages([
                'items' => 'Select at least one material, supplier, quantity, and unit price.',
            ]);
        }

        $purchaseOrders = DB::transaction(function () use ($validated, $selectedItems, $request, $purchase_request) {
            $purchaseRequest = PurchaseRequest::whereKey($purchase_request->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (! in_array($purchaseRequest->status, ['Approved', 'Partially Approved'], true)) {
                abort(422, 'Purchase orders can only be created from approved purchase requests.');
            }

            $itemsBySupplier = [];
            foreach ($selectedItems as $item) {
                $requestItem = $purchaseRequest->items()
                    ->whereKey($item['purchase_request_item_id'])
                    ->lockForUpdate()
                    ->first();

                if (! $requestItem || (float) $requestItem->approved_quantity <= 0) {
                    throw ValidationException::withMessages([
                        'items' => 'A selected item is not an approved item in this purchase request.',
                    ]);
                }

                $alreadyOrdered = $requestItem->purchaseOrderItems()
                    ->whereHas('purchaseOrder', function ($query) {
                        $query->where('status', '!=', 'Cancelled');
                    })
                    ->sum('ordered_quantity');
                $remaining = $this->quantityInMilliunits($requestItem->approved_quantity)
                    - $this->quantityInMilliunits($alreadyOrdered);

                if ($this->quantityInMilliunits($item['ordered_quantity']) > $remaining) {
                    throw ValidationException::withMessages([
                        "items.{$requestItem->id}.ordered_quantity" => 'Ordered quantity cannot exceed the approved remaining quantity.',
                    ]);
                }

                $itemsBySupplier[$item['supplier_id']][] = [
                    'request_item' => $requestItem,
                    'ordered_quantity' => $item['ordered_quantity'],
                    'unit_price' => $item['unit_price'],
                    'discount' => (float) $item['discount'],
                    'tax' => (float) $item['tax'],
                ];
            }

            foreach (array_keys($itemsBySupplier) as $supplierId) {
                $supplier = Supplier::whereKey($supplierId)
                    ->where('status', 1)
                    ->lockForUpdate()
                    ->first();

                if (! $supplier) {
                    throw ValidationException::withMessages([
                        'items' => 'A selected supplier is no longer active.',
                    ]);
                }
            }

            $purchaseOrders = [];
            foreach ($itemsBySupplier as $supplierId => $items) {
                $discount = round(array_sum(array_column($items, 'discount')), 2);
                $tax = round(array_sum(array_column($items, 'tax')), 2);
                if ($discount > 9999999999999.99 || $tax > 9999999999999.99) {
                    throw ValidationException::withMessages([
                        'items' => 'The purchase order total exceeds the maximum supported amount.',
                    ]);
                }
                $purchaseOrder = $this->createPurchaseOrder([
                    'purchase_request_id' => $purchaseRequest->id,
                    'supplier_id' => $supplierId,
                    'created_by' => $request->user()->id,
                    'order_date' => $validated['order_date'],
                    'expected_delivery_date' => $validated['expected_delivery_date'] ?? null,
                    'payment_terms' => $validated['payment_terms'] ?? null,
                    'notes' => $validated['notes'] ?? null,
                    'discount' => $discount,
                    'tax' => $tax,
                    'grand_total' => 0,
                    'status' => 'Draft',
                ]);

                $subtotalTotal = 0;
                foreach ($items as $item) {
                    $subtotal = round($item['ordered_quantity'] * $item['unit_price'], 2);
                    if (! is_finite($subtotal) || $subtotal > 9999999999999.99) {
                        throw ValidationException::withMessages([
                            "items.{$item['request_item']->id}.unit_price" => 'The item subtotal exceeds the maximum supported amount.',
                        ]);
                    }
                    if ($item['discount'] > $subtotal) {
                        throw ValidationException::withMessages([
                            "items.{$item['request_item']->id}.discount" => 'Discount cannot exceed the material subtotal.',
                        ]);
                    }
                    $subtotalTotal += $subtotal;
                    if ($subtotalTotal > 9999999999999.99) {
                        throw ValidationException::withMessages([
                            'items' => 'The purchase order total exceeds the maximum supported amount.',
                        ]);
                    }

                    $purchaseOrder->items()->create([
                        'purchase_request_item_id' => $item['request_item']->id,
                        'material_id' => $item['request_item']->material_id,
                        'ordered_quantity' => $item['ordered_quantity'],
                        'unit_price' => $item['unit_price'],
                        'subtotal' => $subtotal,
                    ]);
                }

                $grandTotal = round($subtotalTotal - $discount + $tax, 2);
                if (! is_finite($grandTotal) || $grandTotal > 9999999999999.99) {
                    throw ValidationException::withMessages([
                        'items' => 'The purchase order total exceeds the maximum supported amount.',
                    ]);
                }
                $purchaseOrder->update(['grand_total' => $grandTotal]);
                $purchaseOrders[] = $purchaseOrder;
            }

            return $purchaseOrders;
        });

        $firstOrder = $purchaseOrders[0];
        $destination = $request->user()->can('purchase-order-list')
            ? route('purchase-orders.show', $firstOrder)
            : route('purchase-requests.purchase-orders.create', $purchase_request);

        return redirect($destination)
            ->with('success', count($purchaseOrders).' purchase order(s) generated successfully.');
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
            'supplier_id' => [
                'required',
                'integer',
                Rule::exists('suppliers', 'id')->where('status', 1)->whereNull('deleted_at'),
            ],
            'order_date' => 'required|date',
            'expected_delivery_date' => 'nullable|date|after_or_equal:order_date',
            'payment_terms' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'discount' => 'required|numeric|min:0|max:9999999999999.99|decimal:0,2',
            'tax' => 'required|numeric|min:0|max:9999999999999.99|decimal:0,2',
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|integer|distinct|exists:purchase_order_items,id',
            'items.*.ordered_quantity' => 'required|numeric|min:0.001|max:999999999999.999|decimal:0,3',
            'items.*.unit_price' => 'required|numeric|min:0|max:9999999999999.99|decimal:0,2',
        ]);

        DB::transaction(function () use ($validated, $purchase_order) {
            $lockedOrder = PurchaseOrder::whereKey($purchase_order->id)->lockForUpdate()->firstOrFail();
            if (! in_array($lockedOrder->status, ['Draft', 'Pending Approval'], true)) {
                abort(422, 'Only draft or pending approval purchase orders can be edited.');
            }

            $supplier = Supplier::whereKey($validated['supplier_id'])
                ->where('status', 1)
                ->lockForUpdate()
                ->first();
            if (! $supplier) {
                throw ValidationException::withMessages([
                    'supplier_id' => 'The selected supplier is no longer active.',
                ]);
            }

            $orderItems = $lockedOrder->items()->lockForUpdate()->get();
            $updatesById = collect($validated['items'])
                ->keyBy(fn ($item) => (int) $item['id']);

            foreach ($updatesById as $orderItemId => $item) {
                if (! $orderItems->firstWhere('id', (int) $orderItemId)) {
                    abort(422, 'An item does not belong to this purchase order.');
                }
            }

            $orderedByRequestItem = [];
            $requestItems = [];
            foreach ($orderItems as $orderItem) {
                $requestItem = $orderItem->purchaseRequestItem()->lockForUpdate()->first();
                if (! $requestItem) {
                    continue;
                }

                $quantity = $updatesById->has($orderItem->id)
                    ? $updatesById->get($orderItem->id)['ordered_quantity']
                    : $orderItem->ordered_quantity;
                $requestItemId = $requestItem->id;
                $orderedByRequestItem[$requestItemId] = ($orderedByRequestItem[$requestItemId] ?? 0)
                    + $this->quantityInMilliunits($quantity);
                $requestItems[$requestItemId] = $requestItem;
            }

            foreach ($orderedByRequestItem as $requestItemId => $orderedQuantity) {
                $requestItem = $requestItems[$requestItemId];
                $alreadyOrdered = $requestItem->purchaseOrderItems()
                    ->whereHas('purchaseOrder', function ($query) use ($lockedOrder) {
                        $query->where('status', '!=', 'Cancelled')
                            ->where('purchase_orders.id', '!=', $lockedOrder->id);
                    })
                    ->sum('ordered_quantity');
                $remaining = $this->quantityInMilliunits($requestItem->approved_quantity)
                    - $this->quantityInMilliunits($alreadyOrdered);

                if ($orderedQuantity > $remaining) {
                    abort(422, 'Ordered quantity cannot exceed the approved remaining quantity.');
                }
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

            foreach ($updatesById as $orderItemId => $item) {
                $orderItem = $orderItems->firstWhere('id', (int) $orderItemId);
                $subtotal = round((float) $item['ordered_quantity'] * (float) $item['unit_price'], 2);
                if (! is_finite($subtotal) || $subtotal > 9999999999999.99) {
                    throw ValidationException::withMessages([
                        "items.$orderItemId.unit_price" => 'The item subtotal exceeds the maximum supported amount.',
                    ]);
                }
                $orderItem->update([
                    'ordered_quantity' => $item['ordered_quantity'],
                    'unit_price' => $item['unit_price'],
                    'subtotal' => $subtotal,
                ]);
            }

            $subtotalTotal = (float) $lockedOrder->items()->sum('subtotal');
            $grandTotal = round($subtotalTotal - (float) $validated['discount'] + (float) $validated['tax'], 2);
            if (! is_finite($grandTotal) || $grandTotal > 9999999999999.99) {
                throw ValidationException::withMessages([
                    'tax' => 'The purchase order total exceeds the maximum supported amount.',
                ]);
            }
            $grandTotal = max(0, $grandTotal);
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
            if ($lockedOrder->goodsReceivedNotes()->where('status', '!=', 'Cancelled')->exists()) {
                abort(422, 'This purchase order cannot be cancelled while an active GRN exists.');
            }
            $lockedOrder->update(['status' => 'Cancelled']);
        });

        return redirect()->route('purchase-orders.show', $purchase_order)
            ->with('success', 'Purchase order cancelled.');
    }

    private function orderedQuantities($requestItems): array
    {
        $itemIds = collect($requestItems)->pluck('id');
        if ($itemIds->isEmpty()) {
            return [];
        }

        return PurchaseOrderItem::query()
            ->whereIn('purchase_request_item_id', $itemIds)
            ->whereHas('purchaseOrder', function ($query) {
                $query->where('status', '!=', 'Cancelled');
            })
            ->selectRaw('purchase_request_item_id, SUM(ordered_quantity) as ordered_quantity')
            ->groupBy('purchase_request_item_id')
            ->pluck('ordered_quantity', 'purchase_request_item_id')
            ->map(fn ($quantity) => (float) $quantity)
            ->all();
    }

    private function quantityInMilliunits($quantity): int
    {
        [$whole, $fraction] = array_pad(explode('.', (string) $quantity, 2), 2, '');

        return ((int) $whole * 1000) + (int) str_pad($fraction, 3, '0');
    }

    private function createPurchaseOrder(array $attributes): PurchaseOrder
    {
        $purchaseOrder = PurchaseOrder::create($attributes + [
            'po_number' => 'PO-TEMP-'.Str::uuid(),
        ]);

        $purchaseOrder->update([
            'po_number' => 'PO-'.now()->format('Y').'-'.str_pad((string) $purchaseOrder->id, 5, '0', STR_PAD_LEFT),
        ]);

        return $purchaseOrder;
    }
}
