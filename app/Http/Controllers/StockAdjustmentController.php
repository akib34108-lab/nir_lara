<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\MaterialInventory;
use App\Models\StockAdjustment;
use App\Models\StockTransaction;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockAdjustmentController extends Controller
{
    public function index(Request $request)
    {
        $adjustments = StockAdjustment::with(['warehouse', 'material', 'creator', 'approver'])
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->status))
            ->when($request->filled('warehouse'), fn ($query) => $query->where('warehouse_id', $request->warehouse))
            ->latest()->paginate(10);
        $warehouses = Warehouse::where('status', 1)->orderBy('name')->get();

        return view('stock-adjustments.index', compact('adjustments', 'warehouses'));
    }

    public function create()
    {
        $warehouses = Warehouse::where('status', 1)->orderBy('name')->get();
        $materials = Material::where('status', 1)->orderBy('name')->get();

        return view('stock-adjustments.create', compact('warehouses', 'materials'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'material_id' => 'required|exists:materials,id',
            'adjusted_quantity' => 'required|numeric|min:0',
            'reason' => 'required|in:Damage,Loss,Physical Count Difference,Correction',
            'remarks' => 'nullable|string',
        ]);

        $adjustment = DB::transaction(function () use ($validated, $request) {
            $inventory = MaterialInventory::where('warehouse_id', $validated['warehouse_id'])
                ->where('material_id', $validated['material_id'])
                ->lockForUpdate()->first();
            $previous = $inventory ? (float) $inventory->quantity : 0;
            $adjusted = (float) $validated['adjusted_quantity'];

            $nextId = (StockAdjustment::max('id') ?? 0) + 1;

            return StockAdjustment::create([
                'adjustment_number' => 'SA-'.now()->format('Y').'-'.str_pad($nextId, 5, '0', STR_PAD_LEFT),
                'warehouse_id' => $validated['warehouse_id'],
                'material_id' => $validated['material_id'],
                'previous_quantity' => $previous,
                'adjusted_quantity' => $adjusted,
                'difference' => $adjusted - $previous,
                'reason' => $validated['reason'],
                'remarks' => $validated['remarks'] ?? null,
                'created_by' => $request->user()->id,
                'status' => 'Pending',
            ]);
        });

        return redirect()->route('stock-adjustments.show', $adjustment)
            ->with('success', 'Stock adjustment submitted for approval.');
    }

    public function show(StockAdjustment $stock_adjustment)
    {
        $stock_adjustment->load(['warehouse', 'material', 'creator', 'approver']);

        return view('stock-adjustments.show', compact('stock_adjustment'));
    }

    public function approve(Request $request, StockAdjustment $stock_adjustment)
    {
        DB::transaction(function () use ($request, $stock_adjustment) {
            $lockedAdjustment = StockAdjustment::whereKey($stock_adjustment->id)->lockForUpdate()->firstOrFail();
            if ($lockedAdjustment->status !== 'Pending') {
                abort(422, 'Only pending stock adjustments can be approved.');
            }
            if ($lockedAdjustment->created_by === $request->user()->id) {
                abort(403, 'You cannot approve a stock adjustment you created.');
            }
            if (StockTransaction::where('reference_type', StockAdjustment::class)
                ->where('reference_id', $lockedAdjustment->id)
                ->exists()) {
                abort(422, 'This stock adjustment has already been posted.');
            }

            $inventory = MaterialInventory::where('warehouse_id', $lockedAdjustment->warehouse_id)
                ->where('material_id', $lockedAdjustment->material_id)
                ->lockForUpdate()->first();
            if (! $inventory) {
                $inventory = MaterialInventory::create([
                    'warehouse_id' => $lockedAdjustment->warehouse_id,
                    'material_id' => $lockedAdjustment->material_id,
                    'quantity' => 0,
                    'reserved_quantity' => 0,
                    'average_cost' => 0,
                ]);
            }

            $inventory->quantity = (float) $lockedAdjustment->adjusted_quantity;
            $inventory->save();
            $difference = (float) $lockedAdjustment->adjusted_quantity - (float) $lockedAdjustment->previous_quantity;

            StockTransaction::create([
                'warehouse_id' => $lockedAdjustment->warehouse_id,
                'material_id' => $lockedAdjustment->material_id,
                'transaction_type' => 'STOCK_ADJUSTMENT',
                'quantity' => $difference,
                'balance_quantity' => $inventory->quantity,
                'reference_type' => StockAdjustment::class,
                'reference_id' => $lockedAdjustment->id,
                'created_by' => $request->user()->id,
                'notes' => 'Stock adjustment '.$lockedAdjustment->adjustment_number.': '.$lockedAdjustment->reason,
            ]);

            $lockedAdjustment->update([
                'status' => 'Approved',
                'approved_by' => $request->user()->id,
                'approved_at' => now(),
            ]);
        });

        return redirect()->route('stock-adjustments.show', $stock_adjustment)
            ->with('success', 'Stock adjustment approved and inventory updated.');
    }

    public function reject(Request $request, StockAdjustment $stock_adjustment)
    {
        DB::transaction(function () use ($request, $stock_adjustment) {
            $lockedAdjustment = StockAdjustment::whereKey($stock_adjustment->id)->lockForUpdate()->firstOrFail();
            if ($lockedAdjustment->status !== 'Pending') {
                abort(422, 'Only pending stock adjustments can be rejected.');
            }
            if ($lockedAdjustment->created_by === $request->user()->id) {
                abort(403, 'You cannot reject a stock adjustment you created.');
            }

            $lockedAdjustment->update([
                'status' => 'Rejected',
                'approved_by' => $request->user()->id,
                'approved_at' => now(),
                'remarks' => $request->input('remarks', $lockedAdjustment->remarks),
            ]);
        });

        return redirect()->route('stock-adjustments.show', $stock_adjustment)
            ->with('success', 'Stock adjustment rejected.');
    }
}
