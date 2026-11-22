<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\MaterialCategory;
use App\Models\MaterialInventory;
use App\Models\StockTransaction;
use App\Models\Warehouse;
use Illuminate\Http\Request;

class MaterialInventoryController extends Controller
{
    public function index(Request $request)
    {
        $inventories = MaterialInventory::query()
            ->with(['material.category', 'warehouse'])
            ->when($request->filled('warehouse'), function ($query) use ($request) {
                $query->where('warehouse_id', $request->warehouse);
            })
            ->when($request->filled('material'), function ($query) use ($request) {
                $query->where('material_id', $request->material);
            })
            ->when($request->filled('category'), function ($query) use ($request) {
                $query->whereHas('material', function ($materialQuery) use ($request) {
                    $materialQuery->where('category_id', $request->category);
                });
            })
            ->when($request->filled('search'), function ($query) use ($request) {
                $query->whereHas('material', function ($materialQuery) use ($request) {
                    $materialQuery->where('name', 'like', '%'.$request->search.'%')
                        ->orWhere('material_code', 'like', '%'.$request->search.'%');
                });
            })
            ->latest()
            ->paginate(15);

        $warehouseOptions = Warehouse::where('status', 1)->orderBy('name')->get();
        $materialOptions = Material::where('status', 1)->orderBy('name')->get();
        $categoryOptions = MaterialCategory::where('status', 1)->orderBy('name')->get();

        $lowStockCount = MaterialInventory::with('material')
            ->get()
            ->filter(fn (MaterialInventory $inventory) => (float) $inventory->quantity - (float) $inventory->reserved_quantity <= (float) $inventory->material->minimum_stock_level)
            ->count();

        $totalStockValue = MaterialInventory::get()
            ->sum(fn (MaterialInventory $inventory) => (float) $inventory->quantity * (float) $inventory->average_cost);

        return view('inventory.index', compact(
            'inventories',
            'warehouseOptions',
            'materialOptions',
            'categoryOptions',
            'lowStockCount',
            'totalStockValue'
        ));
    }

    public function lowStock(Request $request)
    {
        $inventories = MaterialInventory::with(['material.category', 'warehouse'])
            ->get()
            ->filter(fn (MaterialInventory $inventory) => (float) $inventory->quantity - (float) $inventory->reserved_quantity <= (float) $inventory->material->minimum_stock_level)
            ->filter(function (MaterialInventory $inventory) use ($request) {
                if (! $request->filled('warehouse')) {
                    return true;
                }

                return (int) $inventory->warehouse_id === (int) $request->warehouse;
            })
            ->filter(function (MaterialInventory $inventory) use ($request) {
                if (! $request->filled('material')) {
                    return true;
                }

                return (int) $inventory->material_id === (int) $request->material;
            });

        $warehouseOptions = Warehouse::where('status', 1)->orderBy('name')->get();
        $materialOptions = Material::where('status', 1)->orderBy('name')->get();

        return view('inventory.low-stock', compact('inventories', 'warehouseOptions', 'materialOptions'));
    }

    public function movements(Request $request)
    {
        $transactions = StockTransaction::query()
            ->with(['warehouse', 'material', 'creator'])
            ->when($request->filled('warehouse'), function ($query) use ($request) {
                $query->where('warehouse_id', $request->warehouse);
            })
            ->when($request->filled('material'), function ($query) use ($request) {
                $query->where('material_id', $request->material);
            })
            ->when($request->filled('transaction_type'), function ($query) use ($request) {
                $query->where('transaction_type', $request->transaction_type);
            })
            ->when($request->filled('date_from'), function ($query) use ($request) {
                $query->whereDate('created_at', '>=', $request->date_from);
            })
            ->when($request->filled('date_to'), function ($query) use ($request) {
                $query->whereDate('created_at', '<=', $request->date_to);
            })
            ->latest()
            ->paginate(20);

        $warehouseOptions = Warehouse::where('status', 1)->orderBy('name')->get();
        $materialOptions = Material::where('status', 1)->orderBy('name')->get();
        $transactionTypes = StockTransaction::distinct()->orderBy('transaction_type')->pluck('transaction_type');

        return view('inventory.movements', compact('transactions', 'warehouseOptions', 'materialOptions', 'transactionTypes'));
    }

    public function show(MaterialInventory $inventory)
    {
        $inventory->load(['material.category', 'warehouse']);
        $transactions = StockTransaction::with('creator')
            ->where('warehouse_id', $inventory->warehouse_id)
            ->where('material_id', $inventory->material_id)
            ->latest()
            ->paginate(20);

        return view('inventory.show', compact('inventory', 'transactions'));
    }
}
