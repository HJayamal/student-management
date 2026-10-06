<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>
        @yield('title', 'Student Management')
    </title>


    <!-- Bootstrap Icons -->
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">


    <!-- OverlayScrollbars -->
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/styles/overlayscrollbars.min.css">


    <!-- AdminLTE -->
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/admin-lte@4.10.0/dist/css/adminlte.min.css">


    <style>

        /* =====================================================
           GLOBAL
        ===================================================== */

        :root {

            --sidebar-bg: #0f172a;
            --sidebar-dark: #0b1120;
            --sidebar-hover: #1e293b;

            --primary: #4f46e5;
            --primary-dark: #4338ca;

            --white: #ffffff;

            --border: #e2e8f0;

            --text: #334155;
            --muted: #64748b;

            --danger: #dc2626;

        }


        * {
            box-sizing: border-box;
        }


        body {

            margin: 0;

            font-family:
                "Segoe UI",
                Arial,
                sans-serif;

            background: #f8fafc !important;

            color: var(--text);

        }


        /* =====================================================
           APP WRAPPER
        ===================================================== */

        .app-wrapper {
            min-height: 100vh;
        }


        /* =====================================================
           TOP NAVBAR
        ===================================================== */

        .app-header {

            height: 64px !important;

            background:
                var(--white) !important;

            border-bottom:
                1px solid var(--border) !important;

            box-shadow:
                0 2px 12px rgba(15, 23, 42, 0.05);

            z-index: 1030;

        }


        .app-header .container-fluid {

            height: 100%;

            padding-left: 15px;
            padding-right: 15px;

        }


        /* -----------------------------------------------------
           Sidebar Toggle
        ----------------------------------------------------- */

        .navbar-menu-btn {

            width: 42px;
            height: 42px;

            display: flex !important;

            align-items: center;
            justify-content: center;

            margin-top: 3px;

            border-radius: 10px;

            color: #475569 !important;

            transition:
                background 0.2s ease,
                color 0.2s ease;

        }


        .navbar-menu-btn i {

            font-size: 20px;

        }


        .navbar-menu-btn:hover {

            background: #f1f5f9;

            color:
                var(--primary) !important;

        }


        /* -----------------------------------------------------
           About / Contact
        ----------------------------------------------------- */

        .navbar-link {

            display: flex !important;

            align-items: center;

            color: #475569 !important;

            font-size: 14px;

            font-weight: 500;

            padding:
                9px 12px !important;

            border-radius: 8px;

            transition: 0.2s ease;

        }


        .navbar-link:hover {

            background: #f8fafc;

            color:
                var(--primary) !important;

        }


        /* -----------------------------------------------------
           Divider
        ----------------------------------------------------- */

        .navbar-divider {

            width: 1px;

            height: 30px;

            margin:
                7px 8px;

            background:
                var(--border);

        }


        /* -----------------------------------------------------
           Student Profile
        ----------------------------------------------------- */

        .student-profile {

            display: flex !important;

            align-items: center;

            gap: 8px;

            color: #334155 !important;

            font-size: 14px;

            font-weight: 600;

            padding:
                7px 10px !important;

        }


        .profile-icon {

            width: 32px;
            height: 32px;

            display: flex;

            align-items: center;
            justify-content: center;

            border-radius: 50%;

            background: #eef2ff;

            color:
                var(--primary);

            font-size: 15px;

        }


        /* -----------------------------------------------------
           Logout
        ----------------------------------------------------- */

        .logout-link {

            display: flex !important;

            align-items: center;

            color:
                var(--danger) !important;

            font-size: 14px;

            font-weight: 600;

            padding:
                9px 12px !important;

            border-radius: 8px;

            transition: 0.2s ease;

        }


        .logout-link:hover {

            background: #fef2f2;

            color: #b91c1c !important;

        }


        /* =====================================================
           SIDEBAR
        ===================================================== */

        .app-sidebar {

            width: 245px !important;

            background:
                var(--sidebar-bg) !important;

            border-right:
                1px solid rgba(255, 255, 255, 0.05);

            box-shadow:
                5px 0 20px rgba(15, 23, 42, 0.12);

        }


        /* -----------------------------------------------------
           Brand
        ----------------------------------------------------- */

        .sidebar-brand {

            height: 64px;

            display: flex;

            align-items: center;

            padding:
                0 18px;

            background:
                var(--sidebar-dark) !important;

            border-bottom:
                1px solid rgba(255, 255, 255, 0.08);

        }


        .brand-link {

            width: 100%;

            display: flex;

            align-items: center;

            gap: 10px;

            text-decoration: none !important;

        }


        .brand-icon {

            width: 36px;
            height: 36px;

            display: flex;

            align-items: center;
            justify-content: center;

            border-radius: 10px;

            background:
                linear-gradient(
                    135deg,
                    #4f46e5,
                    #6366f1
                );

            color: #ffffff;

            font-size: 18px;

            box-shadow:
                0 4px 10px
                rgba(79, 70, 229, 0.25);

        }


        .brand-text {

            color: #ffffff !important;

            font-size: 18px;

            font-weight: 700 !important;

            letter-spacing: 0.2px;

        }


        /* =====================================================
           SIDEBAR MENU
        ===================================================== */

        .sidebar-wrapper {

            padding-top: 15px;

        }


        .sidebar-menu {

            padding:
                0 10px;

        }


        .sidebar-menu .nav-item {

            margin-bottom: 4px;

        }


        .sidebar-menu .nav-link {

            min-height: 45px;

            display: flex;

            align-items: center;

            padding:
                10px 13px !important;

            border-radius: 10px;

            color:
                #cbd5e1 !important;

            font-size: 14px;

            transition:
                all 0.2s ease;

        }


        .sidebar-menu .nav-icon {

            width: 22px;

            margin-right: 9px;

            color:
                #94a3b8;

            font-size: 16px;

        }


        .sidebar-menu .nav-link p {

            margin: 0;

            color: inherit;

            font-size: 14px;

            font-weight: 500;

        }


        /* -----------------------------------------------------
           Sidebar Hover
        ----------------------------------------------------- */

        .sidebar-menu .nav-link:hover {

            background:
                var(--sidebar-hover) !important;

            color:
                #ffffff !important;

            transform:
                translateX(2px);

        }


        .sidebar-menu .nav-link:hover .nav-icon {

            color:
                #ffffff;

        }


        /* -----------------------------------------------------
           Sidebar Active
        ----------------------------------------------------- */

        .sidebar-menu .nav-link.active {

            background:
                linear-gradient(
                    135deg,
                    #4f46e5,
                    #6366f1
                ) !important;

            color:
                #ffffff !important;

            box-shadow:
                0 5px 14px
                rgba(79, 70, 229, 0.25);

        }


        .sidebar-menu .nav-link.active .nav-icon {

            color:
                #ffffff !important;

        }


        /* =====================================================
           MAIN CONTENT
        ===================================================== */

        .app-main {

            background:
                #f8fafc !important;

        }


        .app-content {

            min-height:
                calc(100vh - 120px);

            background:
                #f8fafc;

        }


        .app-content .container-fluid {

            width: 100%;

        }


        /* =====================================================
           FOOTER
        ===================================================== */

        .app-footer {

            min-height: 54px;

            display: flex;

            align-items: center;

            justify-content: space-between;

            background:
                #ffffff !important;

            border-top:
                1px solid var(--border) !important;

            color:
                var(--muted) !important;

            font-size: 13px;

            padding:
                0 18px;

        }


        .app-footer strong {

            color:
                #334155;

            font-weight: 700;

        }


        /* =====================================================
           RESPONSIVE
        ===================================================== */

        @media (max-width: 991px) {

            .app-sidebar {

                width: 245px !important;

            }


            .student-name {

                display: none;

            }


            .student-profile {

                padding:
                    7px !important;

            }

        }


        @media (max-width: 768px) {

            .navbar-link {

                font-size: 13px;

                padding:
                    8px !important;

            }


            .logout-link {

                padding:
                    8px !important;

            }

        }


        @media (max-width: 576px) {

            .navbar-link {

                display: none !important;

            }


            .navbar-divider {

                display: none;

            }


            .app-footer {

                font-size: 11px;

            }

        }

    </style>

</head>


<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">


<div class="app-wrapper">


    <!-- =====================================================
         TOP NAVBAR
    ====================================================== -->

    <nav class="app-header navbar navbar-expand">

        <div class="container-fluid">


            <!-- LEFT -->
            <ul class="navbar-nav">

                <li class="nav-item">

                    <a
                        class="nav-link navbar-menu-btn"
                        data-lte-toggle="sidebar"
                        href="#"
                        role="button">

                        <i class="bi bi-list"></i>

                    </a>

                </li>

            </ul>


            <!-- RIGHT -->
            <ul class="navbar-nav ms-auto">


                <!-- ABOUT US -->

                <li class="nav-item">

                    <a
                        href="{{ route('about') }}"
                        class="nav-link navbar-link">

                        <i class="bi bi-info-circle me-1"></i>

                        About Us

                    </a>

                </li>


                <!-- CONTACT US -->

                <li class="nav-item">

                    <a
                        href="{{ route('contact') }}"
                        class="nav-link navbar-link">

                        <i class="bi bi-envelope me-1"></i>

                        Contact Us

                    </a>

                </li>


                @if(session('student_id'))


                    <!-- DIVIDER -->

                    <li class="nav-item">

                        <div class="navbar-divider"></div>

                    </li>


                    <!-- STUDENT PROFILE -->

                    <li class="nav-item">

                        <span
                            class="nav-link student-profile">

                            <span class="profile-icon">

                                <i class="bi bi-person-fill"></i>

                            </span>


                            <span class="student-name">

                                Welcome,
                                {{ session('student_name') }}

                            </span>

                        </span>

                    </li>


                    <!-- LOGOUT -->

                    <li class="nav-item">

                        <a
                            href="{{ route('logout') }}"
                            class="nav-link logout-link">

                            <i class="bi bi-box-arrow-right me-1"></i>

                            Logout

                        </a>

                    </li>

                @endif

            </ul>

        </div>

    </nav>


    <!-- =====================================================
         SIDEBAR
    ====================================================== -->

    <aside
        class="app-sidebar"
        data-bs-theme="dark">


        <!-- BRAND -->

        <div class="sidebar-brand">

            <a
                href="{{ route('home') }}"
                class="brand-link">


                <span class="brand-icon">

                    <i class="bi bi-mortarboard-fill"></i>

                </span>


                <span class="brand-text">

                    StudentManage

                </span>

            </a>

        </div>


        <!-- SIDEBAR WRAPPER -->

        <div class="sidebar-wrapper">

            <nav>

                <ul
                    class="nav sidebar-menu flex-column"
                    role="menu">


                    <!-- ===================================
                         DASHBOARD
                    ==================================== -->

                    <li class="nav-item">

                        <a
                            href="{{ route('home') }}"
                            class="nav-link">

                            <i
                                class="nav-icon bi bi-grid-1x2-fill">
                            </i>

                            <p>
                                Dashboard
                            </p>

                        </a>

                    </li>


                    <!-- ===================================
                         STUDENTS
                    ==================================== -->

                    <li class="nav-item">

                        <a
                            href="{{ route('students.index') }}"
                            class="nav-link">

                            <i
                                class="nav-icon bi bi-people-fill">
                            </i>

                            <p>
                                Students
                            </p>

                        </a>

                    </li>


                    <!-- ===================================
                         STUDENT IMAGES
                    ==================================== -->

                    <li class="nav-item">

                        <a
                            href="{{ route('students.image-list') }}"
                            class="nav-link">

                            <i
                                class="nav-icon bi bi-images">
                            </i>

                            <p>
                                Student Images
                            </p>

                        </a>

                    </li>


                    <!-- ===================================
                         ADMISSIONS
                    ==================================== -->

                    <li class="nav-item">

                        <a
                            href="{{ route('admissions.index') }}"
                            class="nav-link">

                            <i
                                class="nav-icon bi bi-person-plus-fill">
                            </i>

                            <p>
                                Admissions
                            </p>

                        </a>

                    </li>


                    <!-- ===================================
                         TEACHERS
                    ==================================== -->

                    <li class="nav-item">

                        <a
                            href="{{ route('teachers.index') }}"
                            class="nav-link">

                            <i
                                class="nav-icon bi bi-person-badge-fill">
                            </i>

                            <p>
                                Teachers
                            </p>

                        </a>

                    </li>


                    <!-- ===================================
                         SUBJECTS
                    ==================================== -->

                    <li class="nav-item">

                        <a
                            href="{{ route('subjects.index') }}"
                            class="nav-link">

                            <i
                                class="nav-icon bi bi-book-fill">
                            </i>

                            <p>
                                Subjects
                            </p>

                        </a>

                    </li>


                    <!-- ===================================
                         EXAMS
                    ==================================== -->

                    <li class="nav-item">

                        <a
                            href="{{ route('exams.index') }}"
                            class="nav-link">

                            <i
                                class="nav-icon bi bi-journal-check">
                            </i>

                            <p>
                                Exams
                            </p>

                        </a>

                    </li>

                </ul>

            </nav>

        </div>

    </aside>


    <!-- =====================================================
         MAIN CONTENT
    ====================================================== -->

    <main class="app-main">

        <div class="app-content">

            <div class="container-fluid p-4">

                @yield('content')

            </div>

        </div>

    </main>


    <!-- =====================================================
         FOOTER
    ====================================================== -->

    <footer class="app-footer">

        <strong>
            StudentManage
        </strong>

        <span>
            Student Management System
        </span>

    </footer>


</div>



<script
    src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js">
</script>



<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.min.js">
</script>



<script
    src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/browser/overlayscrollbars.browser.es6.min.js">
</script>



<script
    src="https://cdn.jsdelivr.net/npm/admin-lte@4.10.0/dist/js/adminlte.min.js">
</script>


</body>

</html>
