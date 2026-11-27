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