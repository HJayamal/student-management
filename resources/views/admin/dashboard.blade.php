@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')

    <style>



        .dashboard-page {
            background: #f8fafc;
            min-height: calc(100vh - 120px);
            padding: 10px 5px 30px;
        }



        .dashboard-title {
            color: #0f172a !important;
            font-size: 30px;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .dashboard-subtitle {
            color: #64748b !important;
            font-size: 14px;
            margin-bottom: 30px;
        }


        .stat-card {

            position: relative;

            min-height: 190px;

            border-radius: 18px;

            overflow: hidden;

            border: none;

            box-shadow:
                0 8px 25px rgba(15, 23, 42, 0.08);

            transition:
                transform 0.2s ease,
                box-shadow 0.2s ease;

        }


        .stat-card:hover {

            transform: translateY(-4px);

            box-shadow:
                0 14px 30px rgba(15, 23, 42, 0.12);

        }


        /* =====================================================
           CARD CONTENT
        ===================================================== */

        .stat-card-body {

            position: relative;

            padding: 24px;

            min-height: 142px;

            color: #ffffff;

        }


        .stat-icon {

            position: absolute;

            right: 20px;

            top: 22px;

            width: 58px;
            height: 58px;

            display: flex;

            align-items: center;

            justify-content: center;

            border-radius: 16px;

            background:
                rgba(255,255,255,0.18);

            font-size: 25px;

        }


        .stat-number {

            font-size: 38px;

            line-height: 1;

            font-weight: 750;

            margin-bottom: 12px;

        }


        .stat-label {

            font-size: 15px;

            font-weight: 600;

            opacity: 0.95;

        }


        .stat-description {

            font-size: 12px;

            margin-top: 6px;

            opacity: 0.82;

        }


        /* =====================================================
           COLORS
        ===================================================== */

        .students-card {

            background:
                linear-gradient(
                    135deg,
                    #4f46e5,
                    #6366f1
                );

        }


        .admissions-card {

            background:
                linear-gradient(
                    135deg,
                    #10b981,
                    #059669
                );

        }


        .teachers-card {

            background:
                linear-gradient(
                    135deg,
                    #f59e0b,
                    #d97706
                );

        }


        .subjects-card {

            background:
                linear-gradient(
                    135deg,
                    #06b6d4,
                    #0891b2
                );

        }


        .exams-card {

            background:
                linear-gradient(
                    135deg,
                    #ef4444,
                    #dc2626
                );

        }


        /* =====================================================
           CARD FOOTER
        ===================================================== */

        .stat-card-footer {

            min-height: 48px;

            display: flex;

            align-items: center;

            justify-content: space-between;

            padding: 0 20px;

            background:
                rgba(0,0,0,0.10);

            color: #ffffff;

        }


        .stat-card-footer a {

            color: #ffffff !important;

            text-decoration: none;

            font-size: 13px;

            font-weight: 600;

        }


        .stat-card-footer a:hover {

            text-decoration: underline;

        }


        .footer-arrow {

            font-size: 16px;

        }


        /* =====================================================
           QUICK ACTIONS
        ===================================================== */

        .quick-card {

            background: #ffffff;

            border: 1px solid #e2e8f0;

            border-radius: 18px;

            box-shadow:
                0 8px 25px rgba(15, 23, 42, 0.06);

            overflow: hidden;

            margin-top: 26px;

        }


        .quick-card-header {

            padding: 18px 20px;

            border-bottom:
                1px solid #e2e8f0;

        }


        .quick-card-title {

            color: #0f172a;

            font-size: 18px;

            font-weight: 700;

            margin: 0;

        }


        .quick-card-subtitle {

            color: #64748b;

            font-size: 13px;

            margin-top: 4px;

        }


        .quick-card-body {

            padding: 20px;

        }


        .quick-action {

            display: flex;

            align-items: center;

            gap: 12px;

            padding: 14px;

            border-radius: 12px;

            background: #f8fafc;

            border: 1px solid #e2e8f0;

            text-decoration: none !important;

            transition: all 0.2s ease;

        }


        .quick-action:hover {

            background: #f1f5f9;

            transform: translateY(-2px);

        }


        .quick-action-icon {

            width: 40px;
            height: 40px;

            display: flex;

            align-items: center;

            justify-content: center;

            border-radius: 10px;

            background: #eef2ff;

            color: #4f46e5;

            font-size: 17px;

        }


        .quick-action-title {

            color: #334155;

            font-size: 14px;

            font-weight: 600;

        }


        .quick-action-text {

            color: #64748b;

            font-size: 12px;

            margin-top: 2px;

        }


        /* =====================================================
           RESPONSIVE
        ===================================================== */

        @media (max-width: 992px) {

            .stat-card {

                margin-bottom: 4px;

            }

        }


        @media (max-width: 576px) {

            .dashboard-title {

                font-size: 24px;

            }

            .stat-card {

                min-height: 175px;

            }

            .stat-number {

                font-size: 32px;

            }

            .stat-icon {

                width: 50px;
                height: 50px;

                font-size: 21px;

            }

        }

    </style>


    <div class="dashboard-page">


        <!-- =====================================================
             HEADER
        ====================================================== -->

        <div class="mb-4">

            <div class="dashboard-title">

                Dashboard

            </div>

            <div class="dashboard-subtitle">

                Welcome to your Student Management System

            </div>

        </div>



        <!-- =====================================================
             STAT CARDS
        ====================================================== -->

        <div class="row g-4">


            <!-- STUDENTS -->

            <div class="col-xl col-lg-4 col-md-6">

                <div class="stat-card students-card">


                    <div class="stat-card-body">

                        <div class="stat-number">

                            {{ $students }}

                        </div>

                        <div class="stat-label">

                            Total Students

                        </div>

                        <div class="stat-description">

                            Registered students

                        </div>


                        <div class="stat-icon">

                            <i class="bi bi-people-fill"></i>

                        </div>

                    </div>


                    <div class="stat-card-footer">

                        <a href="{{ route('students.index') }}">

                            View Students

                        </a>

                        <span class="footer-arrow">
                        →
                    </span>

                    </div>

                </div>

            </div>


            <!-- ADMISSIONS -->

            <div class="col-xl col-lg-4 col-md-6">

                <div class="stat-card admissions-card">


                    <div class="stat-card-body">

                        <div class="stat-number">

                            {{ $admissions }}

                        </div>

                        <div class="stat-label">

                            Total Admissions

                        </div>

                        <div class="stat-description">

                            Student admissions

                        </div>


                        <div class="stat-icon">

                            <i class="bi bi-person-plus-fill"></i>

                        </div>

                    </div>


                    <div class="stat-card-footer">

                        <a href="{{ route('admissions.index') }}">

                            View Admissions

                        </a>

                        <span class="footer-arrow">
                        →
                    </span>

                    </div>

                </div>

            </div>


            <!-- TEACHERS -->

            <div class="col-xl col-lg-4 col-md-6">

                <div class="stat-card teachers-card">


                    <div class="stat-card-body">

                        <div class="stat-number">

                            {{ $teachers }}

                        </div>

                        <div class="stat-label">

                            Total Teachers

                        </div>

                        <div class="stat-description">

                            Registered teachers

                        </div>


                        <div class="stat-icon">

                            <i class="bi bi-person-badge-fill"></i>

                        </div>

                    </div>


                    <div class="stat-card-footer">

                        <a href="{{ route('teachers.index') }}">

                            View Teachers

                        </a>

                        <span class="footer-arrow">
                        →
                    </span>

                    </div>

                </div>

            </div>


            <!-- SUBJECTS -->

            <div class="col-xl col-lg-4 col-md-6">

                <div class="stat-card subjects-card">


                    <div class="stat-card-body">

                        <div class="stat-number">

                            {{ $subjects }}

                        </div>

                        <div class="stat-label">

                            Total Subjects

                        </div>

                        <div class="stat-description">

                            Available subjects

                        </div>


                        <div class="stat-icon">

                            <i class="bi bi-book-fill"></i>

                        </div>

                    </div>


                    <div class="stat-card-footer">

                        <a href="{{ route('subjects.index') }}">

                            View Subjects

                        </a>

                        <span class="footer-arrow">
                        →
                    </span>

                    </div>

                </div>

            </div>


            <!-- EXAMS -->

            <div class="col-xl col-lg-4 col-md-6">

                <div class="stat-card exams-card">


                    <div class="stat-card-body">

                        <div class="stat-number">

                            {{ $exams }}

                        </div>

                        <div class="stat-label">

                            Total Exams

                        </div>

                        <div class="stat-description">

                            Scheduled exams

                        </div>


                        <div class="stat-icon">

                            <i class="bi bi-journal-check"></i>

                        </div>

                    </div>


                    <div class="stat-card-footer">

                        <a href="{{ route('exams.index') }}">

                            View Exams

                        </a>

                        <span class="footer-arrow">
                        →
                    </span>

                    </div>

                </div>

            </div>

        </div>



        <!-- =====================================================
             QUICK ACTIONS
        ====================================================== -->

        <div class="quick-card">


            <div class="quick-card-header">

                <h3 class="quick-card-title">

                    Quick Actions

                </h3>

                <div class="quick-card-subtitle">

                    Quickly access the main sections

                </div>

            </div>


            <div class="quick-card-body">

                <div class="row g-3">


                    <!-- STUDENTS -->

                    <div class="col-lg-3 col-md-6">

                        <a
                            href="{{ route('students.index') }}"
                            class="quick-action">

                        <span class="quick-action-icon">

                            <i class="bi bi-person-plus-fill"></i>

                        </span>

                            <span>

                            <div class="quick-action-title">

                                Manage Students

                            </div>

                            <div class="quick-action-text">

                                Register and update

                            </div>

                        </span>

                        </a>

                    </div>


                    <!-- ADMISSIONS -->

                    <div class="col-lg-3 col-md-6">

                        <a
                            href="{{ route('admissions.index') }}"
                            class="quick-action">

                        <span class="quick-action-icon">

                            <i class="bi bi-person-plus-fill"></i>

                        </span>

                            <span>

                            <div class="quick-action-title">

                                Manage Admissions

                            </div>

                            <div class="quick-action-text">

                                View and update admissions

                            </div>

                        </span>

                        </a>

                    </div>


                    <!-- TEACHERS -->

                    <div class="col-lg-3 col-md-6">

                        <a
                            href="{{ route('teachers.index') }}"
                            class="quick-action">

                        <span class="quick-action-icon">

                            <i class="bi bi-person-badge-fill"></i>

                        </span>

                            <span>

                            <div class="quick-action-title">

                                Manage Teachers

                            </div>

                            <div class="quick-action-text">

                                Add and update teachers

                            </div>

                        </span>

                        </a>

                    </div>


                    <!-- SUBJECTS -->

                    <div class="col-lg-3 col-md-6">

                        <a
                            href="{{ route('subjects.index') }}"
                            class="quick-action">

                        <span class="quick-action-icon">

                            <i class="bi bi-book-fill"></i>

                        </span>

                            <span>

                            <div class="quick-action-title">

                                Manage Subjects

                            </div>

                            <div class="quick-action-text">

                                View all subjects

                            </div>

                        </span>

                        </a>

                    </div>


                    <!-- EXAMS -->

                    <div class="col-lg-3 col-md-6">

                        <a
                            href="{{ route('exams.index') }}"
                            class="quick-action">

                        <span class="quick-action-icon">

                            <i class="bi bi-journal-check"></i>

                        </span>

                            <span>

                            <div class="quick-action-title">

                                Manage Exams

                            </div>

                            <div class="quick-action-text">

                                View exam details

                            </div>

                        </span>

                        </a>

                    </div>


                </div>

            </div>

        </div>


    </div>

@endsection
