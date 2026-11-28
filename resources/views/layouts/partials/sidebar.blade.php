<div id="right-sidebar" class="settings-panel">
  <div class="tab-content" id="setting-content" style="border: 1px solid rgba(75, 73, 172, 0.25);">
      <div class="tab-pane fade show active scroll-wrapper material-category-grid"
          id="todo-section"
          role="tabpanel"
          aria-labelledby="todo-section">
          @can('category-list')
              <a class="material-category-link"
                href="{{ route('material-categories.index') }}"
                data-tooltip="Material Category">
                  <i class="ti-layers"></i>
              </a>
          @endcan
          @can('supplier-list')
              <a class="material-category-link"
                href="{{ route('suppliers.index') }}"
                data-tooltip="Suppliers List">
                  <i class="ti-truck"></i>
              </a>
          @endcan
      </div>
  </div>
</div>
<nav class="sidebar sidebar-offcanvas" id="sidebar">
  @php
    $dashboardContext = session('dashboard_context', 'admin');

    $isAdminDashboard = $dashboardContext === 'admin';
    $isProjectManagerDashboard = $dashboardContext === 'project_manager';
    $isShareholderDashboard = $dashboardContext === 'shareholder';
    $isSystemDashboard = $dashboardContext === 'system';
  @endphp

  @php($isPurchaseOrderRoute = request()->routeIs('purchase-orders.*'))

  <ul class="nav">
    @if($isSystemDashboard)
      @can('users-list')
      <li class="nav-item">
        <a class="nav-link" data-toggle="collapse" href="#users-access-menu" aria-expanded="false" aria-controls="users-access-menu">
          <i class="icon-contract menu-icon"></i>
          <span class="menu-title">Users & Access</span>
          <i class="menu-arrow"></i>
        </a>
        <div class="collapse" id="users-access-menu">
          <ul class="nav flex-column sub-menu">
            <li class="nav-item"> <a class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}" href="{{ route('users-list.index') }}">Users List</a></li>
            <li class="nav-item"> <a class="nav-link" href="{{ route('roles.index') }}">Roles</a></li>
            <li class="nav-item"> <a class="nav-link" href="{{ route('permissions.index') }}">Permissions</a></li>
            <li class="nav-item"> <a class="nav-link" href="{{asset('assets/pages/ui-features/typography.html')}}">Activity Logs</a></li>
          </ul>
        </div>
      </li>
      @endcan
    @endif

    @if($isShareholderDashboard)
      @can('sites-list')
      <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('sites.*') ? 'active' : '' }}"
           href="{{ route('sites.index') }}">
          <i class="icon-map menu-icon"></i>
          <span class="menu-title">Sites List</span>
        </a>
      </li>
      @endcan
    @endif

    @if($isAdminDashboard || $isShareholderDashboard)
      @canany(['buildings-list', 'floors-list', 'amenities-list', 'floor-plans-list'])
      @php($isPropertyRoute = request()->routeIs('buildings.*', 'floors.*', 'amenities.*', 'floor-plans.*'))
      <li class="nav-item">
        <a class="nav-link {{ $isPropertyRoute ? 'active' : '' }}" data-toggle="collapse" href="#property-management-menu" aria-expanded="{{ $isPropertyRoute ? 'true' : 'false' }}" aria-controls="property-management-menu">
          <i class="ti-home menu-icon"></i>
          <span class="menu-title">Property Manage</span>
          <i class="menu-arrow"></i>
        </a>
        <div class="collapse {{ $isPropertyRoute ? 'show' : '' }}" id="property-management-menu">
          <ul class="nav flex-column sub-menu">
            @can('buildings-list')
            <li class="nav-item"><a class="nav-link {{ request()->routeIs('buildings.*') ? 'active' : '' }}" href="{{ route('buildings.index') }}">Building List</a></li>
            @endcan
            @can('floors-list')
            <li class="nav-item"><a class="nav-link {{ request()->routeIs('floors.*') ? 'active' : '' }}" href="{{ route('floors.index') }}">Floor Management</a></li>
            @endcan
            @can('amenities-list')
            <li class="nav-item"><a class="nav-link {{ request()->routeIs('amenities.*') ? 'active' : '' }}" href="{{ route('amenities.index') }}">Amenities &amp; Facilities</a></li>
            @endcan
            @can('floor-plans-list')
            <li class="nav-item"><a class="nav-link {{ request()->routeIs('floor-plans.*') ? 'active' : '' }}" href="{{ route('floor-plans.index') }}">Floor Plans</a></li>
            @endcan
          </ul>
        </div>
      </li>
      @endcanany
    @endif

    @if($isProjectManagerDashboard)
      @can('clients-list')
      <li class="nav-item">
        <a class="nav-link" data-toggle="collapse" href="#projects-menu" aria-expanded="false" aria-controls="projects-menu">
          <i class="icon-briefcase menu-icon"></i>
          <span class="menu-title">Projects Portfolio</span>
          <i class="menu-arrow"></i>
        </a>
        <div class="collapse" id="projects-menu">
          <ul class="nav flex-column sub-menu">
            <li class="nav-item"> <a class="nav-link {{ request()->routeIs('clients.*') ? 'active' : '' }}" href="{{ route('clients.index') }}">Clients List</a></li>
            <li class="nav-item"> <a class="nav-link" href="{{ route('projects.index') }}">Projects</a></li>
          </ul>
        </div>
      </li>
      @endcan
      @canany(['materials-list', 'category-list', 'supplier-list', 'purchase-request-list', 'purchase-order-list', 'grn-list', 'inspection-list', 'inventory-list', 'material-issue-list', 'material-return-list', 'stock-transfer-list', 'consumption-list', 'stock-adjustment-list', 'material-report-view'])
      <li class="nav-item">
        <a class="nav-link {{ $isPurchaseOrderRoute ? 'active' : '' }}" data-toggle="collapse" href="#material-menu" aria-expanded="{{ $isPurchaseOrderRoute ? 'true' : 'false' }}" aria-controls="material-menu">
          <i class="icon-box menu-icon"></i>
          <span class="menu-title">Material</span>
          <i class="menu-arrow"></i>
        </a>
        <div class="collapse {{ $isPurchaseOrderRoute ? 'show' : '' }}" id="material-menu">
          <ul class="nav flex-column sub-menu">
            @can('materials-list')
            <li class="nav-item"> <a class="nav-link {{ request()->routeIs('materials.*') ? 'active' : '' }}" href="{{ route('materials.index') }}">Material List</a></li>
            @endcan
            @can('purchase-request-list')
            <li class="nav-item"> <a class="nav-link {{ request()->routeIs('purchase-requests.*') ? 'active' : '' }}" href="{{ route('purchase-requests.index') }}">Purchase Requests</a></li>
            @endcan
            @can('purchase-order-list')
            <li class="nav-item"> <a class="nav-link {{ request()->routeIs('purchase-orders.*') ? 'active' : '' }}" href="{{ route('purchase-orders.index') }}">Purchase Orders</a></li>
            @endcan
            @can('grn-list')
            <li class="nav-item"> <a class="nav-link {{ request()->routeIs('grns.*') ? 'active' : '' }}" href="{{ route('grns.index') }}">Material Receiving</a></li>
            @endcan
            @can('inspection-list')
            <li class="nav-item"> <a class="nav-link {{ request()->routeIs('inspections.*') ? 'active' : '' }}" href="{{ route('inspections.index') }}">Material Inspection</a></li>
            @endcan
            @can('inventory-list')
            <li class="nav-item"> <a class="nav-link {{ request()->routeIs('inventory.*') ? 'active' : '' }}" href="{{ route('inventory.index') }}">Material Inventory</a></li>
            @endcan
            @can('material-issue-list')
            <li class="nav-item"> <a class="nav-link {{ request()->routeIs('material-issues.*') ? 'active' : '' }}" href="{{ route('material-issues.index') }}">Material Issue</a></li>
            @endcan
            @can('material-return-list')
            <li class="nav-item"> <a class="nav-link {{ request()->routeIs('material-returns.*') ? 'active' : '' }}" href="{{ route('material-returns.index') }}">Material Returns</a></li>
            @endcan
            @can('stock-transfer-list')
            <li class="nav-item"> <a class="nav-link {{ request()->routeIs('stock-transfers.*') ? 'active' : '' }}" href="{{ route('stock-transfers.index') }}">Stock Transfer</a></li>
            @endcan
            @can('consumption-list')
            <li class="nav-item"> <a class="nav-link {{ request()->routeIs('material-consumptions.*') ? 'active' : '' }}" href="{{ route('material-consumptions.index') }}">Consumption</a></li>
            @endcan
            @can('stock-adjustment-list')
            <li class="nav-item"> <a class="nav-link {{ request()->routeIs('stock-adjustments.*') ? 'active' : '' }}" href="{{ route('stock-adjustments.index') }}">Stock Adjustment</a></li>
            @endcan
            @can('material-report-view')
            <li class="nav-item"> <a class="nav-link {{ request()->routeIs('material-reports.*') ? 'active' : '' }}" href="{{ route('material-reports.index') }}">Material Reports</a></li>
            @endcan
          </ul>
        </div>
      </li>
      @endcanany
    @endif
  </ul>
</nav>