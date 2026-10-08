````
```
@extends('layouts.app')

@section('title', 'Teachers')

@section('content')

    <style>


        .teacher-page {
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

        .teacher-form-header {
            background: linear-gradient(135deg, #10b981, #059669) !important;
        }

        .teacher-list-header {
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

        /* FORM */

        .teacher-page .form-label {
            color: #334155 !important;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .teacher-page .form-control {
            background: #ffffff !important;
            color: #1e293b !important;
            border: 1px solid #dbe3ef !important;
            border-radius: 10px !important;
            padding: 11px 13px !important;
            min-height: 44px;
            box-shadow: none !important;
            transition: all 0.2s ease;
        }

        .teacher-page .form-control:focus {
            background: #ffffff !important;
            color: #1e293b !important;
            border-color: #6366f1 !important;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.12) !important;
        }

        .teacher-page textarea.form-control {
            min-height: 95px;
            resize: vertical;
        }

        .teacher-page .form-control::placeholder {
            color: #94a3b8 !important;
            opacity: 1;
        }

        /* ADD BUTTON */

        .add-teacher-btn {
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

        .add-teacher-btn:hover {
            color: #ffffff !important;
            transform: translateY(-1px);
            box-shadow: 0 7px 18px rgba(16, 185, 129, 0.22);
        }

        /* ALERTS */

        .teacher-page .alert {
            border-radius: 10px !important;
            border: none !important;
        }

        .teacher-page .alert-success {
            background: #ecfdf5 !important;
            color: #047857 !important;
        }

        .teacher-page .alert-danger {
            background: #fef2f2 !important;
            color: #b91c1c !important;
        }

        /* PDF BUTTON */

        .header-actions {
            display: flex !important;
            flex-direction: row !important;
            align-items: center !important;
            justify-content: flex-end !important;
            gap: 8px !important;
            flex-wrap: nowrap !important;
            white-space: nowrap !important;
            width: auto !important;
            min-width: max-content !important;
        }

        .import-form {
            display: inline-flex !important;
            align-items: center !important;
            margin: 0 !important;
            padding: 0 !important;
            flex: 0 0 auto !important;
        }

        .pdf-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #ffffff !important;
            color: #dc2626 !important;
            border: none !important;
            border-radius: 9px !important;
            padding: 9px 14px;
            font-size: 13px;
            font-weight: 600;
            white-space: nowrap;
            text-decoration: none !important;
            transition: all 0.2s ease;
            flex: 0 0 auto !important;
        }

        .import-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #ffffff !important;
            color: #2563eb !important;
            border: none !important;
            border-radius: 9px !important;
            padding: 9px 14px;
            font-size: 13px;
            font-weight: 600;
            white-space: nowrap;
            cursor: pointer;
            margin-right: 0;
            text-decoration: none !important;
            transition: all 0.2s ease;
            flex: 0 0 auto !important;
        }

        .import-btn:hover {
            background: #eff6ff !important;
            color: #1d4ed8 !important;
            transform: translateY(-1px);
        }


        .pdf-btn:hover {
            background: #fef2f2 !important;
            color: #b91c1c !important;
            transform: translateY(-1px);
        }

        /* TABLE */

        .teacher-table {
            background: #ffffff !important;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            overflow-x: auto;
            overflow-y: hidden;
            position: relative;
        }

        .teacher-table table {
            width: 100%;
            min-width: 900px;
            margin-bottom: 0 !important;
            background: #ffffff !important;
        }

        .teacher-table thead th {
            background: #f8fafc !important;
            color: #334155 !important;
            border-bottom: 1px solid #e2e8f0 !important;
            border-top: none !important;
            font-size: 13px;
            font-weight: 700;
            padding: 14px 12px !important;
            white-space: nowrap;
        }

        .teacher-table tbody tr {
            background: #ffffff !important;
        }

        .teacher-table tbody td {
            background: #ffffff !important;
            color: #334155 !important;
            border-color: #e2e8f0 !important;
            padding: 13px 12px !important;
            vertical-align: middle;
            font-size: 13px;
        }

        .teacher-table tbody tr:hover td {
            background: #f8faff !important;
        }

        /* TEACHER NAME */

        .teacher-name {
            font-weight: 600;
            color: #1e293b !important;
        }

        /* TEACHER IMAGE */

        .teacher-avatar {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 50%;
            border: 2px solid #e2e8f0;
        }

        .teacher-no-image {
            width: 50px;
            height: 50px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: #f1f5f9;
            color: #94a3b8;
            font-size: 10px;
            font-weight: 600;
            text-align: center;
        }

        /* SUBJECT BADGE */

        .subject-badge {
            display: inline-block;
            background: #eef2ff;
            color: #4338ca;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        /* ACTION BUTTONS */

        .action-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: none !important;
            border-radius: 8px !important;
            font-size: 12px;
            font-weight: 600;
            padding: 8px 11px;
            margin: 2px auto !important;
            white-space: nowrap;
            min-width: 100px;
            text-decoration: none !important;
        }

        .action-btn.btn-warning {
            background: #2563eb !important;
            color: #ffffff !important;
        }

        .action-btn.btn-warning:hover {
            background: #1d4ed8 !important;
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

        .action-column {
            min-width: 130px;
            width: 130px;
            text-align: center;
        }

        .action-cell {
            min-width: 130px;
            width: 130px;
            text-align: center;
            white-space: normal;
        }

        .action-cell form {
            display: block !important;
            margin: 0 !important;
        }

        /* EMPTY STATE */

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

        /* RESPONSIVE */

        @media (max-width: 992px) {

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

            .pdf-btn,
            .import-btn {
                padding: 7px 9px;
                font-size: 12px;
            }

            .header-actions {
                display: flex !important;
                flex-direction: row !important;
                flex-wrap: nowrap !important;
                align-items: center !important;
                gap: 4px !important;
            }

            .action-column,
            .action-cell {
                min-width: 115px;
                width: 115px;
            }

            .action-btn {
                min-width: 90px;
                font-size: 11px;
                padding: 7px 8px;
            }

            .teacher-table tbody td,
            .teacher-table thead th {
                font-size: 12px;
                padding: 9px !important;
            }
        }
    </style>


    <div class="teacher-page">

        <!-- PAGE HEADER -->

        <div class="mb-4">

            <div class="page-title">
                Teacher Management
            </div>

            <div class="page-subtitle">
                Add and manage teacher information
            </div>

        </div>


        <div class="row g-4">


            <!-- =====================================
                 ADD TEACHER
            ====================================== -->

            <div class="col-lg-5">

                <div class="modern-card">

                    <div class="modern-card-header teacher-form-header">

                        <div>

                            <h3 class="modern-card-title">

                                <i class="bi bi-person-badge-fill me-2"></i>

                                Add Teacher

                            </h3>

                            <small>
                                Add a new teacher
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


                        <!-- FORM -->

                        <form
                            action="{{ route('teachers.store') }}"
                            method="POST"
                            enctype="multipart/form-data">

                            @csrf


                            <!-- NAME -->

                            <div class="mb-3">

                                <label class="form-label">
                                    Name
                                </label>

                                <input
                                    type="text"
                                    name="name"
                                    class="form-control"
                                    placeholder="Enter teacher name"
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
                                    placeholder="teacher@example.com"
                                    value="{{ old('email') }}"
                                    required
                                >

                            </div>


                            <!-- PHONE -->

                            <div class="mb-3">

                                <label class="form-label">
                                    Phone
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


                            <!-- GENDER -->

                            <div class="mb-3">

                                <label class="form-label">
                                    Gender
                                </label>

                                <select
                                    name="gender"
                                    class="form-control"
                                    required
                                >
                                    <option value="">Select Gender</option>
                                    <option value="Male" {{ old('gender') == 'Male' ? 'selected' : '' }}>Male</option>
                                    <option value="Female" {{ old('gender') == 'Female' ? 'selected' : '' }}>Female</option>
                                </select>

                            </div>


                            <!-- IMAGE -->

                            <div class="mb-3">

                                <label class="form-label">
                                    Teacher Image
                                </label>

                                <input
                                    type="file"
                                    name="image"
                                    class="form-control"
                                    accept=".jpg,.jpeg,.png"
                                >

                                <small class="text-muted">
                                    JPG, JPEG or PNG (Max 2MB)
                                </small>

                            </div>


                            <!-- SUBJECT -->

                            <div class="mb-3">

                                <label class="form-label">
                                    Subject
                                </label>

                                <input
                                    type="text"
                                    name="subject"
                                    class="form-control"
                                    placeholder="Enter subject"
                                    value="{{ old('subject') }}"
                                    required
                                >

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
                                    placeholder="Enter teacher address"
                                    required
                                >{{ old('address') }}</textarea>

                            </div>


                            <!-- BUTTON -->

                            <button
                                type="submit"
                                class="add-teacher-btn">

                                <i class="bi bi-person-plus me-2"></i>

                                Add Teacher

                            </button>

                        </form>

                    </div>

                </div>

            </div>


            <!-- =====================================
                 TEACHER LIST
            ====================================== -->

            <div class="col-lg-7">

                <div class="modern-card">


                    <!-- HEADER -->

                    <div class="modern-card-header teacher-list-header">

                        <div>

                            <h3 class="modern-card-title">

                                <i class="bi bi-people-fill me-2"></i>

                                Teacher List

                            </h3>

                            <small>
                                Registered teachers
                            </small>

                        </div>


                        <!-- IMPORT CSV + EXPORT PDF -->

                        <div class="header-actions">

                            <form
                                action="{{ route('teachers.import') }}"
                                method="POST"
                                enctype="multipart/form-data"
                                class="d-inline import-form">

                                @csrf

                                <input
                                    type="file"
                                    name="file"
                                    id="teacherImport"
                                    accept=".csv,.txt"
                                    class="d-none">

                                <label
                                    for="teacherImport"
                                    class="import-btn mb-0">

                                    <i class="bi bi-upload me-1"></i>

                                    Import CSV

                                </label>

                            </form>

                            <a
                                href="{{ route('teachers.export-pdf') }}"
                                class="pdf-btn"
                                style="text-decoration: none !important;">

                                <i class="bi bi-file-earmark-pdf me-1"></i>

                                Export PDF

                            </a>

                        </div>

                    </div>


                    <div class="modern-card-body">


                        <!-- TEACHER TABLE -->

                        <div class="table-responsive teacher-table">

                            <table id="teacherTable" class="table align-middle">

                                <thead>

                                <tr>

                                    <th>Image</th>

                                    <th>Name</th>

                                    <th>Email</th>

                                    <th>Phone</th>

                                    <th>Gender</th>

                                    <th>Subject</th>

                                    <th class="action-column">Action</th>

                                </tr>

                                </thead>


                                <tbody>


                                @forelse($teachers as $teacher)


                                    <tr>


                                        <!-- IMAGE -->

                                        <td class="text-center">

                                            @if($teacher->image)

                                                <img
                                                    src="{{ asset('storage/' . $teacher->image) }}"
                                                    alt="Teacher Image"
                                                    class="teacher-avatar"
                                                >

                                            @else

                                                <div class="teacher-no-image">
                                                    No Image
                                                </div>

                                            @endif

                                        </td>


                                        <!-- NAME -->

                                        <td>

                                        <span class="teacher-name">

                                            {{ $teacher->name }}

                                        </span>

                                        </td>


                                        <!-- EMAIL -->

                                        <td>

                                            {{ $teacher->email }}

                                        </td>


                                        <!-- PHONE -->

                                        <td>

                                            {{ $teacher->phone }}

                                        </td>


                                        <!-- GENDER -->

                                        <td>
                                            {{ $teacher->gender }}
                                        </td>


                                        <!-- SUBJECT -->

                                        <td>

                                        <span class="subject-badge">

                                            {{ $teacher->subject }}

                                        </span>

                                        </td>


                                        <!-- ACTION -->

                                        <td class="action-cell">


                                            <!-- UPDATE -->

                                            <a
                                                href="{{ route('teachers.edit', $teacher) }}"
                                                class="btn btn-warning btn-sm action-btn">

                                                <i class="bi bi-pencil-square"></i>

                                                Update

                                            </a>


                                            <!-- DELETE -->

                                            <form
                                                action="{{ route('teachers.destroy', $teacher) }}"
                                                method="POST"
                                                style="display:inline;">

                                                @csrf

                                                @method('DELETE')


                                                <button
                                                    type="submit"
                                                    class="btn btn-danger btn-sm action-btn"
                                                    onclick="return confirm('Delete this teacher?')">

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

                                            <i
                                                class="bi bi-person-badge fs-1 d-block mb-2">
                                            </i>

                                            <div class="empty-state-title">

                                                No teachers found

                                            </div>

                                            <small>

                                                Add a teacher to see them here.

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

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const importInput = document.getElementById('teacherImport');

            if (importInput) {
                importInput.addEventListener('change', function () {
                    if (this.files.length > 0) {
                        this.form.submit();
                    }
                });
            }
        });
    </script>

    <!-- DataTables -->

    <link rel="stylesheet"
          href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>

    <script>
        $(document).ready(function () {

            $('#teacherTable').DataTable({

                paging: true,

                ordering: true,

                searching: false,

                pageLength: 10,

                lengthMenu: [5, 10, 25, 50, 100],

                columnDefs: [
                    {
                        targets: -1,
                        orderable: false
                    }
                ]

            });

        });
    </script>

@endsection

```

````
