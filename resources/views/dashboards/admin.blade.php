@include('layouts.partials.header')
    <style>
        /* Page Layout Fix */
        .container-scroller {
            width: 100%;
            overflow-x: hidden;
        }

        .page-body-wrapper {
            width: 100%;
            min-width: 0;
        }

        .main-panel {
            width: 100%;
            min-width: 0;
            flex-grow: 1;
        }

        /* Central Dashboard */
        .central-dashboard {
            width: 100%;
            box-sizing: border-box;
            padding: 25px;
            background: #f5f7fb;
            min-height: calc(100vh - 70px);
        }

        /* Welcome Section */
        .welcome-section {
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: space-between;
            min-height: 230px;
            padding: 40px;
            margin-bottom: 30px;
            border-radius: 20px;
            background: linear-gradient(120deg, #e8f0ff, #d8e8ff);
        }

        .welcome-content {
            position: relative;
            z-index: 2;
        }

        .welcome-badge {
            display: inline-block;
            background: #fff;
            color: #2563eb;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 1.5px;
            padding: 8px 14px;
            border-radius: 30px;
            margin-bottom: 15px;
        }

        .welcome-section h1 {
            font-size: 36px;
            font-weight: 700;
            color: #172554;
            margin-bottom: 10px;
        }

        .welcome-section p {
            font-size: 17px;
            color: #475569;
            margin: 0;
        }

        .welcome-decoration {
            position: absolute;
            right: 60px;
            bottom: -35px;
            font-size: 200px;
            color: rgba(37, 99, 235, 0.10);
        }

        /* Management Section */
        .management-section {
            background: #fff;
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 5px 25px rgba(15, 23, 42, 0.04);
        }

        .section-heading {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 25px;
        }

        .heading-icon {
            width: 50px;
            height: 50px;
            background: #eef2ff;
            color: #4f46e5;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 14px;
            font-size: 22px;
        }

        .section-heading h3 {
            font-size: 22px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 5px;
        }

        .section-heading p {
            color: #64748b;
            font-size: 14px;
            margin: 0;
        }

        /* Management Cards */
        .management-card {
            position: relative;
            display: flex;
            align-items: center;
            min-height: 300px;
            height: 100%;
            padding: 30px;
            border-radius: 18px;
            text-decoration: none !important;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .management-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 15px 30px rgba(15, 23, 42, 0.10);
        }

        /* Shareholder Card */
        .shareholder-card {
            background: linear-gradient(135deg, #e1f8ec, #c7f0df);
            border: 1px solid #c2ead5;
        }

        .shareholder-card .management-icon {
            background: #c2f0d8;
            color: #15803d;
        }

        .shareholder-card h3 {
            color: #166534;
        }

        .shareholder-card .management-btn {
            background: #15803d;
        }

        .shareholder-card .card-decoration {
            color: #15803d;
        }

        /* Construction Card */
        .construction-card {
            background: linear-gradient(135deg, #fff2df, #ffe0b9);
            border: 1px solid #f5d6ad;
        }

        .construction-card .management-icon {
            background: #ffe0b3;
            color: #c77712;
        }

        .construction-card h3 {
            color: #92400e;
        }

        .construction-card .management-btn {
            background: #c77712;
        }

        .construction-card .card-decoration {
            color: #c77712;
        }

        /* System Management Card */
        .system-card {
            background: linear-gradient(135deg, #e8edf5, #d5dfed);
            border: 1px solid #cbd5e1;
        }

        .system-card .management-icon {
            background: #cbd5e1;
            color: #34495e;
        }

        .system-card h3 {
            color: #34495e;
        }

        .system-card .management-btn {
            background: #34495e;
        }

        .system-card .card-decoration {
            color: #34495e;
        }

        .system-card:hover {
            background: linear-gradient(135deg, #dbe5f1, #c5d4e6);
        }

        /* Management Card Content */
        .management-card-content {
            position: relative;
            z-index: 2;
            max-width: 420px;
        }

        /* Common Icon */
        .management-icon {
            width: 65px;
            height: 65px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 30px;
            border-radius: 18px;
            margin-bottom: 20px;
        }

        /* Common Heading */
        .management-card h3 {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 12px;
        }

        /* Common Description */
        .management-card p {
            font-size: 15px;
            line-height: 1.7;
            margin-bottom: 25px;
            color: #475569;
        }

        /* Management Button */
        .management-btn {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 12px 22px;
            border-radius: 50px;
            font-size: 14px;
            font-weight: 600;
            color: #fff;
            transition: all 0.3s ease;
        }

        .management-card:hover .management-btn {
            gap: 15px;
        }

        /* Card Decoration */
        .card-decoration {
            position: absolute;
            right: 15px;
            bottom: -25px;
            font-size: 180px;
            opacity: 0.07;
        }

        /* Footer */
        .main-panel .footer {
            width: 100%;
            box-sizing: border-box;
        }

        /* Responsive */
        @media (max-width: 768px) {

            .central-dashboard {
                padding: 15px;
            }

            .welcome-section {
                padding: 25px;
                min-height: 180px;
            }

            .welcome-section h1 {
                font-size: 27px;
            }

            .welcome-section p {
                font-size: 14px;
            }

            .welcome-decoration {
                right: 15px;
                font-size: 120px;
            }

            .management-section {
                padding: 20px;
            }

            .management-card {
                min-height: 250px;
                padding: 25px;
            }

            .management-card h3 {
                font-size: 20px;
            }

            .card-decoration {
                font-size: 130px;
            }
        }
    </style>
<body>
    <div class="container-scroller">
        <!-- Navbar -->
        <nav class="navbar col-lg-12 col-12 p-0 fixed-top d-flex flex-row">
            <div class="text-center navbar-brand-wrapper d-flex align-items-center justify-content-center">
                <a class="navbar-brand brand-logo mr-5" href="javascript:void(0);">
                    <img src="{{ asset('assets/images/logo.svg') }}" class="mr-2" alt="logo">
                </a>
                <a class="navbar-brand brand-logo-mini" href="javascript:void(0);">
                    <img src="{{ asset('assets/images/logo-mini.svg') }}" style="width: 100%;" alt="logo">
                </a>
            </div>
            <div class="navbar-menu-wrapper d-flex align-items-center justify-content-end">
                <!-- Minimize Button -->
                <button class="navbar-toggler align-self-center" type="button" data-toggle="minimize">
                    <span class="icon-menu"></span>
                </button>
            </div>
        </nav>
        <!-- Page Body Wrapper -->
        <div class="container-fluid page-body-wrapper">
            <!-- Theme Settings -->
            <div class="theme-setting-wrapper">

                <div id="settings-trigger">
                    <i class="ti-settings"></i>
                </div>

                <div id="theme-settings" class="settings-panel">

                    <i class="settings-close ti-close"></i>

                    <p class="settings-heading">SIDEBAR SKINS</p>

                    <div class="sidebar-bg-options selected" id="sidebar-light-theme">
                        <div class="img-ss rounded-circle bg-light border mr-3"></div>
                        Light
                    </div>

                    <div class="sidebar-bg-options" id="sidebar-dark-theme">
                        <div class="img-ss rounded-circle bg-dark border mr-3"></div>
                        Dark
                    </div>

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

            <!-- Main Panel Starts -->
            <div class="main-panel">

                <!-- Content Wrapper -->
                <div class="content-wrapper central-dashboard">

                    <!-- Welcome Section -->
                    <section class="welcome-section">

                        <div class="welcome-content">

                            <span class="welcome-badge">
                                NIRMANIC MANAGEMENT
                            </span>

                            <h1>
                                Welcome Back,
                                {{ Auth::check() ? Auth::user()->name : 'to Nirmanic' }}!
                            </h1>

                            <p>
                                Select a management to continue with your work.
                            </p>

                        </div>

                        <div class="welcome-decoration">
                            <i class="ti-home"></i>
                        </div>

                    </section>

                    <!-- Management Selection -->
                    <section class="management-section">

                        <div class="section-heading">

                            <div class="heading-icon">
                                <i class="ti-view-grid"></i>
                            </div>

                            <div>
                                <h3>Choose Your Management</h3>
                                <p>
                                    Access your workspace and manage your operations.
                                </p>
                            </div>

                        </div>

                        <div class="row">

                            <!-- Shareholder Management -->
                            <div class="col-lg-4 col-md-6 mb-4">

                                <a href="{{ route('shareholder') }}"
                                   class="management-card shareholder-card">

                                    <div class="management-card-content">

                                        <div class="management-icon">
                                            <i class="ti-user"></i>
                                        </div>

                                        <h3>Shareholder Management</h3>

                                        <p>
                                            Manage shareholders, flat booking,
                                            investments, installments and payments.
                                        </p>

                                        <span class="management-btn">
                                            Enter Dashboard
                                            <i class="ti-arrow-right"></i>
                                        </span>

                                    </div>

                                    <i class="ti-home card-decoration"></i>

                                </a>

                            </div>

                            <!-- Construction Management -->
                            <div class="col-lg-4 col-md-6 mb-4">

                                <a href="{{ route('projects') }}"
                                   class="management-card construction-card">

                                    <div class="management-card-content">

                                        <div class="management-icon">
                                            <i class="ti-harddrives"></i>
                                        </div>

                                        <h3>Construction Management</h3>

                                        <p>
                                            Manage construction projects, materials,
                                            workforce, site operations and expenses.
                                        </p>

                                        <span class="management-btn">
                                            Enter Dashboard
                                            <i class="ti-arrow-right"></i>
                                        </span>

                                    </div>

                                    <i class="ti-layout-grid2 card-decoration"></i>

                                </a>

                            </div>

                            <!-- System Management -->
                            <div class="col-lg-4 col-md-6 mb-4">

                                <a href="{{ route('system') }}"
                                   class="management-card system-card">

                                    <div class="management-card-content">

                                        <div class="management-icon">
                                            <i class="ti-settings"></i>
                                        </div>

                                        <h3>System Management</h3>

                                        <p>
                                            Manage system users, roles, permissions,
                                            access control and system settings.
                                        </p>

                                        <span class="management-btn">
                                            Enter Management
                                            <i class="ti-arrow-right"></i>
                                        </span>

                                    </div>

                                    <i class="ti-shield card-decoration"></i>

                                </a>

                            </div>

                        </div>

                    </section>

                </div>

            </div>
            <!-- Main Panel Ends -->

        </div>
        <!-- Page Body Wrapper Ends -->

    </div>
@include('layouts.partials.footer')
