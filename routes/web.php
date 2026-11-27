<?php

use App\Http\Controllers\SitesController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\BuildingController;
use App\Http\Controllers\BuildingAmenityController;
use App\Http\Controllers\FloorController;
use App\Http\Controllers\AmenityController;
use App\Http\Controllers\FloorPlanController;
use App\Http\Controllers\GoodsReceivedNoteController;
use App\Http\Controllers\MaterialCategoryController;
use App\Http\Controllers\MaterialConsumptionController;
use App\Http\Controllers\MaterialController;
use App\Http\Controllers\MaterialInspectionController;
use App\Http\Controllers\MaterialInventoryController;
use App\Http\Controllers\MaterialIssueController;
use App\Http\Controllers\MaterialReportController;
use App\Http\Controllers\MaterialReturnController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\ProjectMilestoneController;
use App\Http\Controllers\ProjectProgressController;
use App\Http\Controllers\ProjectsController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\PurchaseRequestController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\StockAdjustmentController;
use App\Http\Controllers\StockTransferController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UserListController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Auth::routes();

Route::middleware(['auth'])->group(function () {

    // Dashboard
    Route::get('/', function () {
    if (auth()->user()->can('central-view')) {
        session(['dashboard_context' => 'admin']);
        return view('dashboards.admin');
    } elseif (auth()->user()->can('projects-list')) {
        session(['dashboard_context' => 'project_manager']);
        return view('dashboards.project');
    } elseif (auth()->user()->can('sites-list')) {
        session(['dashboard_context' => 'shareholder']);
        return view('dashboards.shareholder');
    } elseif (auth()->user()->can('users-list')) {
        session(['dashboard_context' => 'system']);
        return view('dashboards.system');
    } else {
        abort(403, 'Unauthorized Access');
    }
    })->middleware('auth')->name('home');

    Route::get('/cms', function () {
        session(['dashboard_context' => 'project_manager']);
        return view('dashboards.project');
    })->middleware('auth')->name('projects');

    Route::get('/shms', function () {
        session(['dashboard_context' => 'shareholder']);
        return view('dashboards.shareholder');
    })->middleware('auth')->name('shareholder');

    Route::get('/system', function () {
        session(['dashboard_context' => 'system']);
        return view('dashboards.system');
    })->middleware('auth')->name('system');

    Route::resource('sites', SitesController::class)
        ->middlewareFor('index', 'permission:sites-list')
        ->middlewareFor(['create', 'store'], 'permission:sites-create')
        ->middlewareFor('show', 'permission:sites-view')
        ->middlewareFor(['edit', 'update'], 'permission:sites-edit')
        ->middlewareFor('destroy', 'permission:sites-delete');

    Route::resource('buildings', BuildingController::class)
        ->middlewareFor('index', 'permission:buildings-list')
        ->middlewareFor(['create', 'store'], 'permission:buildings-create')
        ->middlewareFor('show', 'permission:buildings-view')
        ->middlewareFor(['edit', 'update'], 'permission:buildings-edit')
        ->middlewareFor('destroy', 'permission:buildings-delete');

    Route::get('floors', [FloorController::class, 'index'])
        ->middleware('permission:floors-list')
        ->name('floors.index');

    Route::resource('buildings.floors', FloorController::class)
        ->except(['index'])
        ->shallow()
        ->middlewareFor(['create', 'store'], 'permission:floors-create')
        ->middlewareFor('show', 'permission:floors-view')
        ->middlewareFor(['edit', 'update'], 'permission:floors-edit')
        ->middlewareFor('destroy', 'permission:floors-delete');

    Route::resource('amenities', AmenityController::class)
        ->middlewareFor('index', 'permission:amenities-list')
        ->middlewareFor(['create', 'store'], 'permission:amenities-create')
        ->middlewareFor('show', 'permission:amenities-view')
        ->middlewareFor(['edit', 'update'], 'permission:amenities-edit')
        ->middlewareFor('destroy', 'permission:amenities-delete');

    Route::post('buildings/{building}/amenities', [BuildingAmenityController::class, 'store'])
        ->middleware('permission:buildings-edit')
        ->name('buildings.amenities.store');
    Route::put('buildings/{building}/amenities/{amenity}', [BuildingAmenityController::class, 'update'])
        ->middleware('permission:buildings-edit')
        ->name('buildings.amenities.update');
    Route::delete('buildings/{building}/amenities/{amenity}', [BuildingAmenityController::class, 'destroy'])
        ->middleware('permission:buildings-edit')
        ->name('buildings.amenities.destroy');

    Route::get('floor-plans', [FloorPlanController::class, 'index'])
        ->middleware('permission:floor-plans-list')
        ->name('floor-plans.index');
    Route::get('floor-plans/create', [FloorPlanController::class, 'create'])
        ->middleware('permission:floor-plans-create')
        ->name('floor-plans.create');
    Route::post('floor-plans', [FloorPlanController::class, 'store'])
        ->middleware('permission:floor-plans-create')
        ->name('floor-plans.store');
    Route::get('floor-plans/{floorPlan}/preview', [FloorPlanController::class, 'preview'])
        ->middleware('permission:floor-plans-view')
        ->name('floor-plans.preview');
    Route::get('floor-plans/{floorPlan}/download', [FloorPlanController::class, 'download'])
        ->middleware('permission:floor-plans-view')
        ->name('floor-plans.download');
    Route::get('floor-plans/{floorPlan}/edit', [FloorPlanController::class, 'edit'])
        ->middleware('permission:floor-plans-edit')
        ->name('floor-plans.edit');
    Route::put('floor-plans/{floorPlan}', [FloorPlanController::class, 'update'])
        ->middleware('permission:floor-plans-edit')
        ->name('floor-plans.update');
    Route::delete('floor-plans/{floorPlan}', [FloorPlanController::class, 'destroy'])
        ->middleware('permission:floor-plans-delete')
        ->name('floor-plans.destroy');

    Route::resource('clients', ClientController::class)
        ->middlewareFor('index', 'permission:clients-list')
        ->middlewareFor(['create', 'store'], 'permission:clients-create')
        ->middlewareFor('show', 'permission:clients-view')
        ->middlewareFor(['edit', 'update'], 'permission:clients-edit')
        ->middlewareFor('destroy', 'permission:clients-delete');

    Route::resource('users-list', UserListController::class)
        ->middlewareFor('index', 'permission:users-list');
    Route::put('users-list/{user}/role', [UserListController::class, 'assignRole'])
        ->middleware('permission:users-edit')
        ->name('users-list.assignRole');

    Route::resource('roles', RoleController::class)
        ->middlewareFor('index', 'permission:roles-list')
        ->middlewareFor(['create', 'store'], 'permission:roles-create')
        ->middlewareFor('show', 'permission:roles-view')
        ->middlewareFor(['edit', 'update'], 'permission:roles-edit')
        ->middlewareFor('destroy', 'permission:roles-delete');

    Route::resource('permissions', PermissionController::class)
        ->except(['create', 'edit']);

    Route::resource('projects', ProjectsController::class)
        ->middlewareFor('index', 'permission:projects-list')
        ->middlewareFor(['create', 'store'], 'permission:projects-create')
        ->middlewareFor('show', 'permission:projects-view')
        ->middlewareFor(['edit', 'update'], 'permission:projects-edit')
        ->middlewareFor('destroy', 'permission:projects-delete');

    Route::post('/projects/{project}/progress', [ProjectProgressController::class, 'store'])
        ->name('projects.progress.store')
        ->middleware('permission:projects-edit');

    Route::resource('projects.milestones', ProjectMilestoneController::class)
        ->except(['index', 'show'])
        ->middlewareFor('create', 'permission:milestones-create')
        ->middlewareFor('store', 'permission:milestones-create')
        ->middlewareFor('edit', 'permission:milestones-edit')
        ->middlewareFor('update', 'permission:milestones-edit')
        ->middlewareFor('destroy', 'permission:milestones-delete');

    Route::resource('materials', MaterialController::class)
        ->except(['show'])
        ->middlewareFor('index', 'permission:materials-list')
        ->middlewareFor(['create', 'store'], 'permission:materials-create')
        ->middlewareFor(['edit', 'update'], 'permission:materials-edit')
        ->middlewareFor('destroy', 'permission:materials-delete');

    Route::resource('material-categories', MaterialCategoryController::class)
        ->except(['show'])
        ->middlewareFor('index', 'permission:category-list')
        ->middlewareFor(['create', 'store'], 'permission:category-create')
        ->middlewareFor(['edit', 'update'], 'permission:category-edit')
        ->middlewareFor('destroy', 'permission:category-delete');

    Route::resource('suppliers', SupplierController::class)
        ->except(['show'])
        ->middlewareFor('index', 'permission:supplier-list')
        ->middlewareFor(['create', 'store'], 'permission:supplier-create')
        ->middlewareFor(['edit', 'update'], 'permission:supplier-edit')
        ->middlewareFor('destroy', 'permission:supplier-delete');

    Route::resource('purchase-requests', PurchaseRequestController::class)
        ->middlewareFor('index', 'permission:purchase-request-list')
        ->middlewareFor(['create', 'store'], 'permission:purchase-request-create')
        ->middlewareFor('show', 'permission:purchase-request-list')
        ->middlewareFor(['edit', 'update'], 'permission:purchase-request-edit')
        ->middlewareFor('destroy', 'permission:purchase-request-delete');

    Route::post('purchase-requests/{purchase_request}/approve', [PurchaseRequestController::class, 'approve'])
        ->middleware('permission:purchase-request-approve')
        ->name('purchase-requests.approve');
    Route::post('purchase-requests/{purchase_request}/reject', [PurchaseRequestController::class, 'reject'])
        ->middleware('permission:purchase-request-reject')
        ->name('purchase-requests.reject');
    Route::post('purchase-requests/{purchase_request}/cancel', [PurchaseRequestController::class, 'cancel'])
        ->middleware('permission:purchase-request-create')
        ->name('purchase-requests.cancel');

    Route::resource('purchase-orders', PurchaseOrderController::class)
        ->middlewareFor('index', 'permission:purchase-order-list')
        ->middlewareFor(['create', 'store'], 'permission:purchase-order-create')
        ->middlewareFor('show', 'permission:purchase-order-list')
        ->middlewareFor(['edit', 'update'], 'permission:purchase-order-edit')
        ->middlewareFor('destroy', 'permission:purchase-order-delete');
    Route::post('purchase-orders/{purchase_order}/submit-approval', [PurchaseOrderController::class, 'submitApproval'])
        ->middleware('permission:purchase-order-create')
        ->name('purchase-orders.submit-approval');
    Route::post('purchase-orders/{purchase_order}/approve', [PurchaseOrderController::class, 'approve'])
        ->middleware('permission:purchase-order-approve')
        ->name('purchase-orders.approve');
    Route::post('purchase-orders/{purchase_order}/cancel', [PurchaseOrderController::class, 'cancel'])
        ->middleware('permission:purchase-order-edit')
        ->name('purchase-orders.cancel');
    Route::get('purchase-requests/{purchase_request}/purchase-orders/create', [PurchaseOrderController::class, 'createForRequest'])
        ->middleware('permission:purchase-order-create')
        ->name('purchase-requests.purchase-orders.create');
    Route::post('purchase-requests/{purchase_request}/purchase-orders', [PurchaseOrderController::class, 'storeForRequest'])
        ->middleware('permission:purchase-order-create')
        ->name('purchase-requests.purchase-orders.store');

    Route::resource('grns', GoodsReceivedNoteController::class)
        ->except(['edit', 'update'])
        ->middlewareFor('index', 'permission:grn-list')
        ->middlewareFor(['create', 'store'], 'permission:grn-create')
        ->middlewareFor('show', 'permission:grn-list')
        ->middlewareFor('destroy', 'permission:grn-delete');
    Route::post('grns/{grn}/confirm', [GoodsReceivedNoteController::class, 'confirm'])
        ->middleware('permission:grn-confirm')
        ->name('grns.confirm');
    Route::post('warehouses', [GoodsReceivedNoteController::class, 'storeWarehouse'])
        ->middleware('permission:inventory-list')
        ->name('warehouses.store');

    Route::resource('inspections', MaterialInspectionController::class)
        ->only(['index', 'create', 'store'])
        ->middlewareFor('index', 'permission:inspection-list')
        ->middlewareFor(['create', 'store'], 'permission:inspection-create');

    Route::get('inventory', [MaterialInventoryController::class, 'index'])
        ->middleware('permission:inventory-list')
        ->name('inventory.index');
    Route::get('inventory/low-stock', [MaterialInventoryController::class, 'lowStock'])
        ->middleware('permission:inventory-view')
        ->name('inventory.low-stock');
    Route::get('inventory/movements', [MaterialInventoryController::class, 'movements'])
        ->middleware('permission:inventory-view')
        ->name('inventory.movements');
    Route::get('inventory/{inventory}', [MaterialInventoryController::class, 'show'])
        ->middleware('permission:inventory-view')
        ->name('inventory.show');

    Route::resource('material-issues', MaterialIssueController::class)
        ->except(['edit', 'update'])
        ->middlewareFor('index', 'permission:material-issue-list')
        ->middlewareFor(['create', 'store'], 'permission:material-issue-create')
        ->middlewareFor('show', 'permission:material-issue-list')
        ->middlewareFor('destroy', 'permission:material-issue-create');
    Route::post('material-issues/{material_issue}/confirm', [MaterialIssueController::class, 'confirm'])
        ->middleware('permission:material-issue-approve')
        ->name('material-issues.confirm');

    Route::resource('material-returns', MaterialReturnController::class)
        ->except(['edit', 'update'])
        ->middlewareFor('index', 'permission:material-return-list')
        ->middlewareFor(['create', 'store'], 'permission:material-return-create')
        ->middlewareFor('show', 'permission:material-return-list')
        ->middlewareFor('destroy', 'permission:material-return-create');
    Route::post('material-returns/{material_return}/process', [MaterialReturnController::class, 'process'])
        ->middleware('permission:material-return-approve')
        ->name('material-returns.process');

    Route::resource('stock-transfers', StockTransferController::class)
        ->except(['edit', 'update'])
        ->middlewareFor('index', 'permission:stock-transfer-list')
        ->middlewareFor(['create', 'store'], 'permission:stock-transfer-create')
        ->middlewareFor('show', 'permission:stock-transfer-list')
        ->middlewareFor('destroy', 'permission:stock-transfer-create');
    Route::post('stock-transfers/{stock_transfer}/dispatch', [StockTransferController::class, 'dispatch'])
        ->middleware('permission:stock-transfer-approve')
        ->name('stock-transfers.dispatch');
    Route::post('stock-transfers/{stock_transfer}/receive', [StockTransferController::class, 'receive'])
        ->middleware('permission:stock-transfer-receive')
        ->name('stock-transfers.receive');
    Route::post('stock-transfers/{stock_transfer}/cancel', [StockTransferController::class, 'cancel'])
        ->middleware('permission:stock-transfer-approve')
        ->name('stock-transfers.cancel');

    Route::resource('material-consumptions', MaterialConsumptionController::class)
        ->except(['edit', 'update'])
        ->middlewareFor('index', 'permission:consumption-list')
        ->middlewareFor(['create', 'store'], 'permission:consumption-create')
        ->middlewareFor('show', 'permission:consumption-list')
        ->middlewareFor('destroy', 'permission:consumption-create');

    Route::resource('stock-adjustments', StockAdjustmentController::class)
        ->only(['index', 'create', 'store', 'show'])
        ->middlewareFor('index', 'permission:stock-adjustment-list')
        ->middlewareFor(['create', 'store'], 'permission:stock-adjustment-create')
        ->middlewareFor('show', 'permission:stock-adjustment-list');
    Route::post('stock-adjustments/{stock_adjustment}/approve', [StockAdjustmentController::class, 'approve'])
        ->middleware('permission:stock-adjustment-approve')
        ->name('stock-adjustments.approve');
    Route::post('stock-adjustments/{stock_adjustment}/reject', [StockAdjustmentController::class, 'reject'])
        ->middleware('permission:stock-adjustment-approve')
        ->name('stock-adjustments.reject');

    Route::get('material-reports', [MaterialReportController::class, 'index'])
        ->middleware('permission:material-report-view')
        ->name('material-reports.index');
});
