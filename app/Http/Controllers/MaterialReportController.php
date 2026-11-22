<?php

namespace App\Http\Controllers;

use App\Models\GoodsReceivedNote;
use App\Models\Material;
use App\Models\MaterialCategory;
use App\Models\MaterialConsumption;
use App\Models\MaterialInventory;
use App\Models\MaterialIssue;
use App\Models\Projects;
use App\Models\PurchaseOrder;
use App\Models\StockAdjustment;
use App\Models\StockTransfer;
use App\Models\Supplier;
use App\Models\Warehouse;
use Illuminate\Http\Request;

class MaterialReportController extends Controller
{
    public function index(Request $request)
    {
        $type = $request->input('type', 'current-stock');
        $rows = collect();

        $filters = [
            'from' => $request->input('date_from'),
            'to' => $request->input('date_to'),
            'project' => $request->input('project'),
            'material' => $request->input('material'),
            'category' => $request->input('category'),
            'supplier' => $request->input('supplier'),
            'warehouse' => $request->input('warehouse'),
        ];

        if ($type === 'current-stock') {
            $rows = MaterialInventory::with(['material.category', 'warehouse'])
                ->when($filters['warehouse'], fn ($q) => $q->where('warehouse_id', $filters['warehouse']))
                ->when($filters['material'], fn ($q) => $q->where('material_id', $filters['material']))
                ->when($filters['category'], fn ($q) => $q->whereHas('material', fn ($mq) => $mq->where('category_id', $filters['category'])))
                ->paginate(20)->withQueryString();
        } elseif ($type === 'low-stock') {
            $rows = MaterialInventory::with(['material.category', 'warehouse'])
                ->get()
                ->filter(fn ($inventory) => (float) $inventory->quantity - (float) $inventory->reserved_quantity <= (float) $inventory->material->minimum_stock_level);
        } elseif ($type === 'purchase-history') {
            $rows = PurchaseOrder::with(['supplier', 'purchaseRequest.project', 'items.material'])
                ->when($filters['supplier'], fn ($q) => $q->where('supplier_id', $filters['supplier']))
                ->when($filters['from'], fn ($q) => $q->whereDate('order_date', '>=', $filters['from']))
                ->when($filters['to'], fn ($q) => $q->whereDate('order_date', '<=', $filters['to']))
                ->latest()->paginate(20)->withQueryString();
        } elseif ($type === 'receiving') {
            $rows = GoodsReceivedNote::with(['purchaseOrder', 'warehouse', 'items.material'])
                ->when($filters['warehouse'], fn ($q) => $q->where('warehouse_id', $filters['warehouse']))
                ->when($filters['from'], fn ($q) => $q->whereDate('received_date', '>=', $filters['from']))
                ->when($filters['to'], fn ($q) => $q->whereDate('received_date', '<=', $filters['to']))
                ->latest()->paginate(20)->withQueryString();
        } elseif ($type === 'issues') {
            $rows = MaterialIssue::with(['project', 'warehouse', 'items.material'])
                ->when($filters['project'], fn ($q) => $q->where('project_id', $filters['project']))
                ->when($filters['warehouse'], fn ($q) => $q->where('warehouse_id', $filters['warehouse']))
                ->when($filters['from'], fn ($q) => $q->whereDate('issue_date', '>=', $filters['from']))
                ->when($filters['to'], fn ($q) => $q->whereDate('issue_date', '<=', $filters['to']))
                ->latest()->paginate(20)->withQueryString();
        } elseif ($type === 'consumption') {
            $rows = MaterialConsumption::with(['project', 'warehouse', 'material'])
                ->when($filters['project'], fn ($q) => $q->where('project_id', $filters['project']))
                ->when($filters['material'], fn ($q) => $q->where('material_id', $filters['material']))
                ->when($filters['warehouse'], fn ($q) => $q->where('warehouse_id', $filters['warehouse']))
                ->when($filters['from'], fn ($q) => $q->whereDate('consumption_date', '>=', $filters['from']))
                ->when($filters['to'], fn ($q) => $q->whereDate('consumption_date', '<=', $filters['to']))
                ->latest()->paginate(20)->withQueryString();
        } elseif ($type === 'transfers') {
            $rows = StockTransfer::with(['sourceWarehouse', 'destinationWarehouse', 'items.material'])
                ->when($filters['warehouse'], fn ($q) => $q->where(fn ($w) => $w->where('source_warehouse_id', $filters['warehouse'])->orWhere('destination_warehouse_id', $filters['warehouse'])))
                ->when($filters['from'], fn ($q) => $q->whereDate('transfer_date', '>=', $filters['from']))
                ->when($filters['to'], fn ($q) => $q->whereDate('transfer_date', '<=', $filters['to']))
                ->latest()->paginate(20)->withQueryString();
        } elseif ($type === 'adjustments') {
            $rows = StockAdjustment::with(['warehouse', 'material', 'creator'])
                ->when($filters['warehouse'], fn ($q) => $q->where('warehouse_id', $filters['warehouse']))
                ->when($filters['material'], fn ($q) => $q->where('material_id', $filters['material']))
                ->when($filters['from'], fn ($q) => $q->whereDate('created_at', '>=', $filters['from']))
                ->when($filters['to'], fn ($q) => $q->whereDate('created_at', '<=', $filters['to']))
                ->latest()->paginate(20)->withQueryString();
        } elseif ($type === 'project-cost') {
            $rows = Projects::with(['purchaseRequests.purchaseOrders.items.material'])
                ->when($filters['project'], fn ($q) => $q->whereKey($filters['project']))
                ->paginate(20)->withQueryString();
        }

        $options = [
            'projects' => Projects::orderBy('project_name')->get(),
            'materials' => Material::orderBy('name')->get(),
            'categories' => MaterialCategory::orderBy('name')->get(),
            'suppliers' => Supplier::orderBy('company_name')->get(),
            'warehouses' => Warehouse::where('status', 1)->orderBy('name')->get(),
        ];

        return view('material-reports.index', compact('type', 'rows', 'filters', 'options'));
    }
}
