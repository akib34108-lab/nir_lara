<!DOCTYPE html>
<html lang="en">
<head>
  <!-- Required meta tags -->
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>Nirmanic</title>
  <!-- plugins:css -->
  <link rel="stylesheet" href="{{asset('assets/vendors/feather/feather.css')}}">
  <link rel="stylesheet" href="{{asset('assets/vendors/ti-icons/css/themify-icons.css')}}">
  <link rel="stylesheet" href="{{asset('assets/vendors/css/vendor.bundle.base.css')}}">
  <!-- endinject -->
  <!-- Plugin css for this page -->
  <link rel="stylesheet" href="{{asset('assets/vendors/datatables.net-bs4/dataTables.bootstrap4.css')}}">
  <link rel="stylesheet" href="{{asset('assets/vendors/ti-icons/css/themify-icons.css')}}">
  <link rel="stylesheet" type="text/css" href="{{asset('assets/js/select.dataTables.min.css')}}">
  <!-- End plugin css for this page -->
  <!-- inject:css -->
  <link rel="stylesheet" href="{{asset('assets/css/vertical-layout-light/style.css')}}">
  <!-- endinject -->
  <link rel="shortcut icon" href="{{asset('assets/images/favicon.png')}}" />
</head>
<body>
  <div class="container-scroller">
    <!-- partial:partials/_navbar.html -->
    <nav class="navbar col-lg-12 col-12 p-0 fixed-top d-flex flex-row">
      <div class="text-center navbar-brand-wrapper d-flex align-items-center justify-content-center">
        <a class="navbar-brand brand-logo mr-5" href="{{ route('dashboard') }}"><img src="{{asset('assets/images/logo.svg')}}" class="mr-2" alt="logo"/></a>
        <a class="navbar-brand brand-logo-mini" href="{{ route('dashboard') }}"><img src="{{asset('assets/images/logo-mini.svg')}}" style="width: 100%;" alt="logo"/></a>
      </div>
      <div class="navbar-menu-wrapper d-flex align-items-center justify-content-end">
        <button class="navbar-toggler navbar-toggler align-self-center" type="button" data-toggle="minimize">
          <span class="icon-menu"></span>
        </button>
        <ul class="navbar-nav mr-lg-2">
          <li class="nav-item nav-search d-none d-lg-block">
            <div class="input-group">
              <div class="input-group-prepend hover-cursor" id="navbar-search-icon">
                <span class="input-group-text" id="search">
                  <i class="icon-search"></i>
                </span>
              </div>
              <input type="text" class="form-control" id="navbar-search-input" placeholder="Search now" aria-label="search" aria-describedby="search">
            </div>
          </li>
        </ul>
        <ul class="navbar-nav navbar-nav-right">
          <li class="nav-item dropdown">
            <a class="nav-link count-indicator dropdown-toggle" id="notificationDropdown" href="javascript:void(0)" data-toggle="dropdown">
              <i class="icon-bell mx-0"></i>
              <span class="count"></span>
            </a>
            <div class="dropdown-menu dropdown-menu-right navbar-dropdown preview-list" aria-labelledby="notificationDropdown">
              <p class="mb-0 font-weight-normal float-left dropdown-header">Notifications</p>
              <a class="dropdown-item preview-item">
                <div class="preview-thumbnail">
                  <div class="preview-icon bg-success">
                    <i class="ti-info-alt mx-0"></i>
                  </div>
                </div>
                <div class="preview-item-content">
                  <h6 class="preview-subject font-weight-normal">Application Error</h6>
                  <p class="font-weight-light small-text mb-0 text-muted">
                    Just now
                  </p>
                </div>
              </a>
              <a class="dropdown-item preview-item">
                <div class="preview-thumbnail">
                  <div class="preview-icon bg-warning">
                    <i class="ti-settings mx-0"></i>
                  </div>
                </div>
                <div class="preview-item-content">
                  <h6 class="preview-subject font-weight-normal">Settings</h6>
                  <p class="font-weight-light small-text mb-0 text-muted">
                    Private message
                  </p>
                </div>
              </a>
              <a class="dropdown-item preview-item">
                <div class="preview-thumbnail">
                  <div class="preview-icon bg-info">
                    <i class="ti-user mx-0"></i>
                  </div>
                </div>
                <div class="preview-item-content">
                  <h6 class="preview-subject font-weight-normal">New user registration</h6>
                  <p class="font-weight-light small-text mb-0 text-muted">
                    2 days ago
                  </p>
                </div>
              </a>
            </div>
          </li>
          <li class="nav-item nav-profile dropdown">
            <a class="nav-link dropdown-toggle" href="javascript:void(0)" data-toggle="dropdown" id="profileDropdown">
              <img src="{{asset('assets/images/faces/face28.jpg')}}" alt="profile"/>
            </a>
            <div class="dropdown-menu dropdown-menu-right navbar-dropdown" aria-labelledby="profileDropdown">
              @auth
              <a class="dropdown-item">
                <i class="ti-id-badge text-primary"></i>
                {{ Auth::user()->getRoleNames()->first() }}
              </a>
              <a class="dropdown-item">
                <i class="ti-settings text-primary"></i>
                Settings
              </a>
              <form method="POST" action="{{ route('logout') }}">
                  @csrf
                  <button type="submit" class="dropdown-item">
                      <i class="ti-power-off text-primary"></i>
                      Logout
                  </button>
              </form>
              @else
              <a class="dropdown-item" href="{{ route('login') }}">
                <i class="ti-user text-primary"></i>
                Login
              </a>
              @endauth
            </div>
          </li>
          <li class="nav-item nav-settings d-none d-lg-flex">
            <a class="nav-link" href="javascript:void(0)">
              <i class="icon-ellipsis"></i>
            </a>
          </li>
        </ul>
        <button class="navbar-toggler navbar-toggler-right d-lg-none align-self-center" type="button" data-toggle="offcanvas">
          <span class="icon-menu"></span>
        </button>
      </div>
    </nav>
    <!-- partial -->
    <div class="container-fluid page-body-wrapper">
      <!-- partial:partials/_settings-panel.html -->
      <div class="theme-setting-wrapper">
        <div id="settings-trigger"><i class="ti-settings"></i></div>
        <div id="theme-settings" class="settings-panel">
          <i class="settings-close ti-close"></i>
          <p class="settings-heading">SIDEBAR SKINS</p>
          <div class="sidebar-bg-options selected" id="sidebar-light-theme"><div class="img-ss rounded-circle bg-light border mr-3"></div>Light</div>
          <div class="sidebar-bg-options" id="sidebar-dark-theme"><div class="img-ss rounded-circle bg-dark border mr-3"></div>Dark</div>
          <p class="settings-heading mt-2">HEADER SKINS</p>
          <div class="color-tiles mx-0 px-4">
            <div class="tiles success"></div>
            <div class="tiles warning"></div>
            <div class="tiles danger"></div>
            <div class="tiles info"></div>
            <div class="tiles dark"></div>
            <div class="tiles default"></div>
          </div>
        </div>
      </div>
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
      <!-- partial -->
      <!-- partial:partials/_sidebar.html -->
      <nav class="sidebar sidebar-offcanvas" id="sidebar">
        @php($isPurchaseOrderRoute = request()->routeIs('purchase-orders.*'))
        <ul class="nav">
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
          @can('shareholder-list')
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
        </ul>
      </nav>
    <!-- partial -->
    <div class="main-panel">
        @yield('content')
      <!-- content-wrapper ends -->
        <!-- partial:partials/_footer.html -->
        <footer class="footer">
          <div class="d-sm-flex justify-content-center justify-content-sm-between">
            <span class="text-muted text-center text-sm-left d-block d-sm-inline-block">Copyright © 2021.  Premium <a href="https://www.bootstrapdash.com/" target="_blank">Bootstrap admin template</a> from BootstrapDash. All rights reserved.</span>
            <span class="float-none float-sm-right d-block mt-1 mt-sm-0 text-center">Hand-crafted & made with <i class="ti-heart text-danger ml-1"></i></span>
          </div>
          <div class="d-sm-flex justify-content-center justify-content-sm-between">
            <span class="text-muted text-center text-sm-left d-block d-sm-inline-block">Distributed by <a href="https://www.themewagon.com/" target="_blank">Themewagon</a></span> 
          </div>
        </footer> 
        <!-- partial -->
      </div>
      <!-- main-panel ends -->
    </div>   
    <!-- page-body-wrapper ends -->
  </div>
  <!-- container-scroller -->
  <!-- plugins:js -->
  <script src="{{asset('assets/vendors/js/vendor.bundle.base.js')}}"></script>
  <!-- endinject -->
  <!-- Plugin js for this page -->
  <script src="{{asset('assets/vendors/chart.js/Chart.min.js')}}"></script>
  <script src="{{asset('assets/vendors/datatables.net/jquery.dataTables.js')}}"></script>
  <script src="{{asset('assets/vendors/datatables.net-bs4/dataTables.bootstrap4.js')}}"></script>
  <script src="{{asset('assets/js/dataTables.select.min.js')}}"></script>

  <!-- End plugin js for this page -->
  <!-- inject:js -->
  <script src="{{asset('assets/js/off-canvas.js')}}"></script>
  <script src="{{asset('assets/js/hoverable-collapse.js')}}"></script>
  <script src="{{asset('assets/js/template.js')}}"></script>
  <script src="{{asset('assets/js/settings.js')}}"></script>
  <script src="{{asset('assets/js/todolist.js')}}"></script>
  <!-- endinject -->
  <!-- Custom js for this page-->
  <script src="{{asset('assets/js/dashboard.js')}}"></script>
  <script src="{{asset('assets/js/Chart.roundedBarCharts.js')}}"></script>
  <!-- End custom js for this page-->
   <style>
    #right-sidebar.settings-panel {
        height: fit-content !important;
        min-height: 0 !important;
        max-height: 80vh !important;
        bottom: auto !important;
        overflow-y: auto !important;
    }
    #right-sidebar .tab-content,
    #right-sidebar .tab-pane,
    #right-sidebar .scroll-wrapper {
        height: auto !important;
        min-height: 0 !important;
    }
    .material-category-link {
        width: 50px;
        height: 50px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 12px auto;
        background: #f1f3f9;
        color: #4B49AC;
        border-radius: 12px;
        text-decoration: none !important;
        transition: all 0.25s ease;
        box-shadow: 0 3px 8px rgba(0, 0, 0, 0.08);
    }
    .material-category-link i {
        font-size: 24px;
    }
    .material-category-link:hover,
    .material-category-link.active {
        background: #4B49AC;
        color: #ffffff;
        transform: translateY(-2px);
        box-shadow: 0 5px 12px rgba(75, 73, 172, 0.25);
    }
    .material-category-grid {
    display: grid !important;
    grid-template-columns: repeat(4, 1fr);
    gap: 8px;
    padding: 10px;
    }
    .material-category-link {
        width: 45px;
        height: 45px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto;
        background: #f1f3f9;
        color: #4B49AC;
        border-radius: 12px;
        text-decoration: none !important;
        transition: all 0.25s ease;
        box-shadow: 0 3px 8px rgba(0, 0, 0, 0.08);
    }
    .material-category-link i {
        font-size: 23px;
    }
    .material-category-link:hover,
    .material-category-link.active {
        background: #4B49AC;
        color: #ffffff;
        transform: translateY(-2px);
        box-shadow: 0 5px 12px rgba(75, 73, 172, 0.25);
    }
    .material-category-link {
      position: relative;
  }
  .material-category-link::after {
      content: attr(data-tooltip);
      position: absolute;
      left: 50px;
      top: 65px;
      transform: translateY(-50%);
      background: #343a40;
      color: #fff;
      padding: 7px 12px;
      border-radius: 5px;
      font-size: 12px;
      white-space: nowrap;
      opacity: 0;
      visibility: hidden;
      transition: all 0.2s ease;
      z-index: 9999;
  }
  .material-category-link:hover::after {
      opacity: 1;
      visibility: visible;
  }
  </style>
   @stack('scripts')
</body>
</html>

