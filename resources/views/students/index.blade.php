@extends('layouts.app')

@section('title', 'Students')

@section('content')

    <style>


        .student-page {
            background: #f8fafc;
            min-height: calc(100vh - 120px);
            padding: 10px 5px 30px;
        }


        .page-title {
            color: #0f172a !important;
            font-size: 30px;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .page-subtitle {
            color: #64748b !important;
            font-size: 14px;
            margin-bottom: 28px;
        }

        /* =========================================
           CARDS
           ========================================= */

        .modern-card {
            background: #ffffff !important;
            border: 1px solid #e2e8f0 !important;
            border-radius: 18px !important;
            overflow: hidden;
            box-shadow: 0 8px 28px rgba(15, 23, 42, 0.07) !important;
        }

        .modern-card-header {
            min-height: 72px;
            padding: 16px 20px;
            color: #ffffff !important;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .register-header {
            background: linear-gradient(135deg, #10b981, #059669) !important;
        }

        .list-header {
            background: linear-gradient(135deg, #4f46e5, #6366f1) !important;
        }

        .modern-card-title {
            color: #ffffff !important;
            margin: 0;
            font-size: 18px;
            font-weight: 700;
        }

        .modern-card-header small {
            color: rgba(255, 255, 255, 0.85) !important;
        }

        .modern-card-body {
            background: #ffffff !important;
            padding: 24px;
        }

        /* =========================================
           FORM
           ========================================= */

        .student-page .form-label {
            color: #334155 !important;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .student-page .form-control {
            background-color: #ffffff !important;
            color: #1e293b !important;
            border: 1px solid #dbe3ef !important;
            border-radius: 10px !important;
            padding: 11px 13px !important;
            min-height: 44px;
            box-shadow: none !important;
            transition: all 0.2s ease;
        }

        .student-page .form-control:focus {
            background-color: #ffffff !important;
            color: #1e293b !important;
            border-color: #6366f1 !important;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.12) !important;
        }

        .student-page textarea.form-control {
            min-height: 95px;
            resize: vertical;
        }

        .student-page .form-control::placeholder {
            color: #94a3b8 !important;
            opacity: 1;
        }

        .student-page input[type="date"] {
            color: #1e293b !important;
        }

        .student-page input[type="file"] {
            padding: 9px 12px !important;
        }

        .student-page input[type="file"]::file-selector-button {
            border: none;
            background: #eef2ff;
            color: #4338ca;
            padding: 7px 12px;
            margin-right: 10px;
            border-radius: 7px;
            font-weight: 600;
            cursor: pointer;
        }

        .student-page .text-muted {
            color: #94a3b8 !important;
        }

        /* =========================================
           REGISTER BUTTON
           ========================================= */

        .register-btn {
            width: 100%;
            border: none !important;
            border-radius: 10px !important;
            background: linear-gradient(135deg, #10b981, #059669) !important;
            color: #ffffff !important;
            font-weight: 600;
            padding: 12px 16px;
            box-shadow: 0 5px 14px rgba(16, 185, 129, 0.18);
            transition: all 0.2s ease;
        }

        .register-btn:hover {
            color: #ffffff !important;
            transform: translateY(-1px);
            box-shadow: 0 7px 18px rgba(16, 185, 129, 0.22);
        }

        /* =========================================
           ALERTS
           ========================================= */

        .student-page .alert {
            border-radius: 10px !important;
            border: none !important;
        }

        .student-page .alert-success {
            background: #ecfdf5 !important;
            color: #047857 !important;
        }

        .student-page .alert-danger {
            background: #fef2f2 !important;
            color: #b91c1c !important;
        }

        /* =========================================
           PDF BUTTON
           ========================================= */

        .pdf-btn {
            background: #ffffff !important;
            color: #dc2626 !important;
            border: none !important;
            border-radius: 9px !important;
            padding: 8px 13px;
            font-size: 13px;
            font-weight: 600;
            white-space: nowrap;
        }

        .pdf-btn:hover {
            background: #fef2f2 !important;
            color: #b91c1c !important;
            transform: translateY(-1px);
        }

        /* =========================================
           SEARCH
           ========================================= */

        .search-box {
            background: #f8fafc !important;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 12px;
            margin-bottom: 20px;
        }

        .search-box .form-control {
            background: #ffffff !important;
            color: #1e293b !important;
        }

        .search-box .btn-primary {
            background: #4f46e5 !important;
            border-color: #4f46e5 !important;
            border-radius: 9px !important;
            font-weight: 600;
            padding-left: 16px;
            padding-right: 16px;
        }

        .search-box .btn-primary:hover {
            background: #4338ca !important;
            border-color: #4338ca !important;
        }

        /* =========================================
           TABLE
           ========================================= */

        .student-table {
            background: #ffffff !important;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            overflow: hidden;
        }

        .student-table table {
            width: 100%;
            margin-bottom: 0 !important;
            background: #ffffff !important;
        }

        .student-table thead th {
            background: #f8fafc !important;
            color: #334155 !important;
            border-bottom: 1px solid #e2e8f0 !important;
            border-top: none !important;
            font-size: 13px;
            font-weight: 700;
            padding: 14px 12px !important;
            white-space: nowrap;
        }

        .student-table tbody tr {
            background: #ffffff !important;
        }

        .student-table tbody td {
            background: #ffffff !important;
            color: #334155 !important;
            border-color: #e2e8f0 !important;
            padding: 12px !important;
            vertical-align: middle;
            font-size: 13px;
        }

        .student-table tbody tr:hover td {
            background: #f8faff !important;
        }

        /* =========================================
           STUDENT IMAGE
           ========================================= */

        .student-avatar {
            width: 46px;
            height: 46px;
            object-fit: cover;
            border-radius: 50%;
            border: 3px solid #eef2ff;
        }

        .no-image {
            width: 46px;
            height: 46px;
            border-radius: 50%;
            background: #f1f5f9 !important;
            color: #94a3b8 !important;
            font-size: 11px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: auto;
            border: 1px solid #e2e8f0;
        }

        /* =========================================
           ACTION BUTTONS
           ========================================= */

        .action-btn {
            border: none !important;
            border-radius: 8px !important;
            font-size: 12px;
            font-weight: 600;
            padding: 7px 10px;
            margin-right: 3px;
        }

        .action-btn.btn-warning {
            background: #f59e0b !important;
            color: #ffffff !important;
        }

        .action-btn.btn-warning:hover {
            background: #d97706 !important;
            color: #ffffff !important;
        }

        .action-btn.btn-danger {
            background: #ef4444 !important;
            color: #ffffff !important;
        }

        .action-btn.btn-danger:hover {
            background: #dc2626 !important;
            color: #ffffff !important;
        }

        /* =========================================
           NO DATA
           ========================================= */

        .empty-state {
            padding: 45px 20px !important;
            color: #64748b !important;
        }

        .empty-state i {
            color: #cbd5e1 !important;
        }

        .empty-state-title {
            color: #334155 !important;
            font-weight: 600;
        }

        /* =========================================
           RESPONSIVE
           ========================================= */

        @media (max-width: 992px) {

            .student-page {
                padding: 5px 0 25px;
            }

            .modern-card {
                margin-bottom: 20px;
            }

        }

        @media (max-width: 576px) {

            .page-title {
                font-size: 24px;
            }

            .modern-card-body {
                padding: 16px;
            }

            .modern-card-header {
                padding: 14px 16px;
            }

            .modern-card-title {
                font-size: 16px;
            }

            .pdf-btn {
                padding: 7px 9px;
                font-size: 12px;
            }

            .student-table tbody td,
            .student-table thead th {
                font-size: 12px;
                padding: 9px !important;
            }

        }
    </style>


    <div class="student-page">

        <!-- PAGE HEADER -->
        <div class="mb-4">

            <div class="page-title">
                Student Management
            </div>

            <div class="page-subtitle">
                Register and manage student information
            </div>

        </div>


        <div class="row g-4">

            <!-- =====================================
                 STUDENT REGISTER
            ====================================== -->
            <div class="col-lg-5">

                <div class="modern-card">

                    <div class="modern-card-header register-header">

                        <div>

                            <h3 class="modern-card-title">
                                <i class="bi bi-person-plus me-2"></i>
                                Student Register
                            </h3>

                            <small>
                                Add a new student
                            </small>

                        </div>

                    </div>


                    <div class="modern-card-body">

                        <!-- SUCCESS MESSAGE -->
                        @if(session('success'))

                            <div class="alert alert-success">

                                <i class="bi bi-check-circle me-2"></i>

                                {{ session('success') }}

                            </div>

                        @endif


                        <!-- VALIDATION ERRORS -->
                        @if($errors->any())

                            <div class="alert alert-danger">

                                <div class="fw-semibold mb-1">
                                    Please fix the following:
                                </div>

                                @foreach($errors->all() as $error)

                                    <div>
                                        <i class="bi bi-exclamation-circle me-1"></i>
                                        {{ $error }}
                                    </div>

                                @endforeach

                            </div>

                        @endif


                        <!-- REGISTER FORM -->
                        <form
                            action="{{ route('students.store') }}"
                            method="POST"
                            enctype="multipart/form-data">

                            @csrf


                            <!-- REGISTER NO -->
                            <div class="mb-3">

                                <label class="form-label">
                                    Register No
                                </label>

                                <input
                                    type="text"
                                    name="reg_no"
                                    class="form-control"
                                    placeholder="Enter register number"
                                    value="{{ old('reg_no') }}"
                                    required
                                >

                            </div>


                            <!-- FULL NAME -->
                            <div class="mb-3">

                                <label class="form-label">
                                    Full Name
                                </label>

                                <input
                                    type="text"
                                    name="name"
                                    class="form-control"
                                    placeholder="Enter student's full name"
                                    value="{{ old('name') }}"
                                    required
                                >

                            </div>


                            <!-- EMAIL -->
                            <div class="mb-3">

                                <label class="form-label">
                                    Email
                                </label>

                                <input
                                    type="email"
                                    name="email"
                                    class="form-control"
                                    placeholder="student@example.com"
                                    value="{{ old('email') }}"
                                    required
                                >

                            </div>


                            <!-- PHONE -->
                            <div class="mb-3">

                                <label class="form-label">
                                    Phone No
                                </label>

                                <input
                                    type="text"
                                    name="phone"
                                    class="form-control"
                                    placeholder="Enter phone number"
                                    value="{{ old('phone') }}"
                                    required
                                >

                            </div>


                            <!-- DATE OF BIRTH -->
                            <div class="mb-3">

                                <label class="form-label">
                                    Date of Birth
                                </label>

                                <input
                                    type="date"
                                    name="dob"
                                    class="form-control"
                                    value="{{ old('dob') }}"
                                    required
                                >

                            </div>


                            <!-- PASSWORD -->
                            <div class="mb-3">

                                <label class="form-label">
                                    Password
                                </label>

                                <input
                                    type="password"
                                    name="password"
                                    class="form-control"
                                    placeholder="Minimum 6 characters"
                                    required
                                >

                            </div>


                            <!-- STUDENT IMAGE -->
                            <div class="mb-3">

                                <label class="form-label">
                                    Student Image
                                </label>

                                <input
                                    type="file"
                                    name="image"
                                    class="form-control"
                                    accept=".jpg,.jpeg,.png"
                                >

                                <small class="text-muted">
                                    JPG, JPEG or PNG
                                </small>

                            </div>


                            <!-- ADDRESS -->
                            <div class="mb-4">

                                <label class="form-label">
                                    Address
                                </label>

                                <textarea
                                    name="address"
                                    class="form-control"
                                    rows="3"
                                    placeholder="Enter student address"
                                    required
                                >{{ old('address') }}</textarea>

                            </div>


                            <!-- REGISTER BUTTON -->
                            <button
                                type="submit"
                                class="register-btn">

                                <i class="bi bi-person-plus me-2"></i>

                                Register Student

                            </button>

                        </form>

                    </div>

                </div>

            </div>


            <!-- =====================================
                 STUDENT LIST
            ====================================== -->
            <div class="col-lg-7">

                <div class="modern-card">

                    <!-- STUDENT LIST HEADER -->
                    <div class="modern-card-header list-header">

                        <div>

                            <h3 class="modern-card-title">
                                <i class="bi bi-people me-2"></i>
                                Student List
                            </h3>

                            <small>
                                Registered students
                            </small>

                        </div>


                        <!-- EXPORT PDF -->
                        <a
                            href="{{ route('students.export-pdf') }}"
                            class="pdf-btn">

                            <i class="bi bi-file-earmark-pdf me-1"></i>

                            Export PDF

                        </a>

                    </div>


                    <div class="modern-card-body">

                        <!-- SEARCH -->
                        <div class="search-box">

                            <form
                                action="{{ route('students.index') }}"
                                method="GET">

                                <div class="input-group">

                                    <input
                                        type="text"
                                        name="search"
                                        value="{{ request('search') }}"
                                        class="form-control"
                                        placeholder="Search by Register No or Name"
                                    >

                                    <button
                                        type="submit"
                                        class="btn btn-primary">

                                        <i class="bi bi-search me-1"></i>

                                        Search

                                    </button>

                                </div>

                            </form>

                        </div>


                        <!-- STUDENT TABLE -->
                        <div class="table-responsive student-table">

                            <table class="table align-middle">

                                <thead>

                                <tr>

                                    <th>Image</th>
                                    <th>Reg No</th>
                                    <th>Name</th>
                                    <th>DOB</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Action</th>

                                </tr>

                                </thead>


                                <tbody>

                                @forelse($students as $student)

                                    <tr>

                                        <!-- IMAGE -->
                                        <td class="text-center">

                                            @if($student->image)

                                                <img
                                                    src="{{ asset('storage/' . $student->image) }}"
                                                    alt="Student Image"
                                                    class="student-avatar"
                                                >

                                            @else

                                                <div class="no-image">
                                                    No Image
                                                </div>

                                            @endif

                                        </td>


                                        <!-- REGISTER NO -->
                                        <td>

                                        <span class="fw-semibold">
                                            {{ $student->reg_no }}
                                        </span>

                                        </td>


                                        <!-- NAME -->
                                        <td>
                                            {{ $student->name }}
                                        </td>


                                        <!-- DOB -->
                                        <td>
                                            {{ $student->dob }}
                                        </td>


                                        <!-- EMAIL -->
                                        <td>
                                            {{ $student->email }}
                                        </td>


                                        <!-- PHONE -->
                                        <td>
                                            {{ $student->phone }}
                                        </td>


                                        <!-- ACTION -->
                                        <td>

                                            <!-- UPDATE -->
                                            <a
                                                href="{{ route('students.edit', $student) }}"
                                                class="btn btn-warning btn-sm action-btn">

                                                <i class="bi bi-pencil-square"></i>
                                                Update

                                            </a>


                                            <!-- DELETE -->
                                            <form
                                                action="{{ route('students.destroy', $student) }}"
                                                method="POST"
                                                style="display:inline;">

                                                @csrf

                                                @method('DELETE')

                                                <button
                                                    type="submit"
                                                    class="btn btn-danger btn-sm action-btn"
                                                    onclick="return confirm('Delete this student?')">

                                                    <i class="bi bi-trash"></i>
                                                    Delete

                                                </button>

                                            </form>

                                        </td>

                                    </tr>

                                @empty

                                    <tr>

                                        <td
                                            colspan="7"
                                            class="text-center empty-state">

                                            <i class="bi bi-people fs-1 d-block mb-2"></i>

                                            <div class="empty-state-title">
                                                No students found
                                            </div>

                                            <small>
                                                Register a student to see them here.
                                            </small>

                                        </td>

                                    </tr>

                                @endforelse

                                </tbody>

                            </table>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

@endsection
