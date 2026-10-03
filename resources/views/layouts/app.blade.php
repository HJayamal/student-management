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
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

    <!-- OverlayScrollbars -->
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/styles/overlayscrollbars.min.css">

    <!-- AdminLTE -->
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/admin-lte@4.10.0/dist/css/adminlte.min.css">

</head>

<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">

<div class="app-wrapper">

    <!-- NAVBAR -->
    <nav class="app-header navbar navbar-expand bg-body">

        <div class="container-fluid">

            <!-- Sidebar button -->
            <ul class="navbar-nav">

                <li class="nav-item">

                    <a class="nav-link"
                       data-lte-toggle="sidebar"
                       href="#"
                       role="button">

                        <i class="bi bi-list"></i>

                    </a>

                </li>

            </ul>


            <!-- Right navbar -->
            <ul class="navbar-nav ms-auto">

                <li class="nav-item">

                    <a href="{{ route('about') }}"
                       class="nav-link">

                        About Us

                    </a>

                </li>

                <li class="nav-item">

                    <a href="{{ route('contact') }}"
                       class="nav-link">

                        Contact Us

                    </a>

                </li>

            </ul>

        </div>

    </nav>


    <!-- SIDEBAR -->
    <aside class="app-sidebar bg-dark shadow"
           data-bs-theme="dark">

        <!-- Brand -->
        <div class="sidebar-brand">

            <a href="{{ route('home') }}"
               class="brand-link">

                <span class="brand-text fw-light">
                    StudentManage
                </span>

            </a>

        </div>


        <!-- Sidebar menu -->
        <div class="sidebar-wrapper">

            <nav>

                <ul class="nav sidebar-menu flex-column"
                    data-lte-toggle="treeview"
                    role="menu">

                    <!-- Dashboard -->
                    <li class="nav-item">

                        <a href="{{ route('home') }}"
                           class="nav-link">

                            <i class="nav-icon bi bi-speedometer"></i>

                            <p>
                                Dashboard
                            </p>

                        </a>

                    </li>


                    <!-- Students -->
                    <li class="nav-item">

                        <a href="{{ route('students.index') }}"
                           class="nav-link">

                            <i class="nav-icon bi bi-people"></i>

                            <p>
                                Students
                            </p>

                        </a>

                    </li>

                    <li class="nav-item">

                        <a href="{{ route('students.image-list') }}"
                           class="nav-link">

                            <i class="nav-icon bi bi-images"></i>

                            <p>
                                Student Images
                            </p>

                        </a>

                    </li>


                    <!-- Admissions -->
                    <li class="nav-item">

                        <a href="{{ route('admissions.index') }}"
                           class="nav-link">

                            <i class="nav-icon bi bi-person-plus"></i>

                            <p>
                                Admissions
                            </p>

                        </a>

                    </li>


                    <!-- Teachers -->
                    <li class="nav-item">

                        <a href="{{ route('teachers.index') }}"
                           class="nav-link">

                            <i class="nav-icon bi bi-person-badge"></i>

                            <p>
                                Teachers
                            </p>

                        </a>

                    </li>


                    <!-- Subjects -->
                    <li class="nav-item">

                        <a href="{{ route('subjects.index') }}"
                           class="nav-link">

                            <i class="nav-icon bi bi-book"></i>

                            <p>
                                Subjects
                            </p>

                        </a>

                    </li>


                    <!-- Exams -->
                    <li class="nav-item">

                        <a href="{{ route('exams.index') }}"
                           class="nav-link">

                            <i class="nav-icon bi bi-pencil-square"></i>

                            <p>
                                Exams
                            </p>

                        </a>

                    </li>

                </ul>

            </nav>

        </div>

    </aside>


    <!-- MAIN CONTENT -->
    <main class="app-main">

        <div class="app-content">

            <div class="container-fluid p-4">

                @yield('content')

            </div>

        </div>

    </main>


    <!-- FOOTER -->
    <footer class="app-footer">

        <strong>
            StudentManage
        </strong>

        <span class="float-end">
            Student Management System
        </span>

    </footer>

</div>


<!-- Popper -->
<script
    src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js">
</script>

<!-- Bootstrap -->
<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.min.js">
</script>

<!-- OverlayScrollbars -->
<script
    src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/browser/overlayscrollbars.browser.es6.min.js">
</script>

<!-- AdminLTE -->
<script
    src="https://cdn.jsdelivr.net/npm/admin-lte@4.10.0/dist/js/adminlte.min.js">
</script>

</body>
</html>
