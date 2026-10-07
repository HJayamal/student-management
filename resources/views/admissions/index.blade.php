@extends('layouts.app')

@section('title', 'Admissions')

@section('content')

    <style>
        .admission-page {
            background: #f8fafc;
            min-height: calc(100vh - 120px);
            padding: 10px 5px 35px;
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

        .admission-form-header {
            background: linear-gradient(135deg, #10b981, #059669) !important;
        }

        .admission-list-header {
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

        .admission-page .form-label {
            color: #334155 !important;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .admission-page .form-control,
        .admission-page .form-select {
            background: #ffffff !important;
            color: #1e293b !important;
            border: 1px solid #dbe3ef !important;
            border-radius: 10px !important;
            padding: 11px 13px !important;
            min-height: 44px;
            box-shadow: none !important;
            transition: all 0.2s ease;
        }

        .admission-page .form-control:focus,
        .admission-page .form-select:focus {
            background: #ffffff !important;
            color: #1e293b !important;
            border-color: #6366f1 !important;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.12) !important;
        }

        .admission-page .form-control::placeholder {
            color: #94a3b8 !important;
            opacity: 1;
        }

        .admission-page input[type="date"] {
            color: #1e293b !important;
        }

        .add-admission-btn {
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

        .add-admission-btn:hover {
            color: #ffffff !important;
            transform: translateY(-1px);
            box-shadow: 0 7px 18px rgba(16, 185, 129, 0.22);
        }

        .admission-page .alert {
            border-radius: 10px !important;
            border: none !important;
        }

        .admission-page .alert-success {
            background: #ecfdf5 !important;
            color: #047857 !important;
        }

        .admission-page .alert-danger {
            background: #fef2f2 !important;
            color: #b91c1c !important;
        }

        .admission-table-wrap {
            overflow-x: auto;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
        }

        .admission-table {
            width: 100%;
            min-width: 780px;
            margin-bottom: 0 !important;
            background: #ffffff !important;
        }

        .admission-table thead th {
            background: #f8fafc !important;
            color: #334155 !important;
            border-bottom: 1px solid #e2e8f0 !important;
            border-top: none !important;
            font-size: 13px;
            font-weight: 700;
            padding: 14px 12px !important;
            white-space: nowrap;
        }

        .admission-table tbody tr {
            background: #ffffff !important;
        }

        .admission-table tbody td {
            background: #ffffff !important;
            color: #334155 !important;
            border-color: #e2e8f0 !important;
            padding: 13px 12px !important;
            vertical-align: middle;
            font-size: 13px;
        }

        .admission-table tbody tr:hover td {
            background: #f8faff !important;
        }

        .admission-number {
            font-weight: 700;
            color: #1e293b !important;
        }

        .reg-badge {
            display: inline-flex;
            align-items: center;
            background: #eef2ff;
            color: #4338ca !important;
            padding: 5px 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 6px 11px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
        }

        .status-active {
            background: #dcfce7;
            color: #15803d !important;
        }

        .status-inactive {
            background: #fee2e2;
            color: #b91c1c !important;
        }

        .status-pending {
            background: #fef3c7;
            color: #b45309 !important;
        }

        .action-column {
            min-width: 145px;
            width: 145px;
            text-align: center;
        }

        .action-cell {
            min-width: 145px;
            width: 145px;
            text-align: center;
            white-space: normal;
        }

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
            min-width: 100px;
            white-space: nowrap;
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

        .action-cell form {
            display: block !important;
            margin: 0 !important;
        }

        .empty-state {
            padding: 55px 20px !important;
            color: #64748b !important;
        }

        .empty-state i {
            color: #cbd5e1 !important;
        }

        .empty-state-title {
            color: #334155 !important;
            font-weight: 700;
            font-size: 17px;
            margin-bottom: 4px;
        }

        @media (max-width: 992px) {
            .modern-card {
                margin-bottom: 20px;
            }
        }

        @media (max-width: 576px) {
            .admission-page {
                padding: 5px 0 25px;
            }

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

            .action-column,
            .action-cell {
                min-width: 120px;
                width: 120px;
            }

            .action-btn {
                min-width: 92px;
                font-size: 11px;
                padding: 7px 8px;
            }
        }
    </style>

    <div class="admission-page">

        <div class="mb-4">
            <div class="page-title">
                Admission Management
            </div>

            <div class="page-subtitle">
                Register and manage student admissions
            </div>
        </div>

        <div class="row g-4">

            <div class="col-lg-5">

                <div class="modern-card">

                    <div class="modern-card-header admission-form-header">

                        <div>
                            <h3 class="modern-card-title">
                                <i class="bi bi-person-plus me-2"></i>
                                Student Admission
                            </h3>

                            <small>
                                Add a new admission
                            </small>
                        </div>

                    </div>

                    <div class="modern-card-body">

                        @if(session('success'))
                            <div class="alert alert-success mb-3">
                                <i class="bi bi-check-circle me-2"></i>
                                {{ session('success') }}
                            </div>
                        @endif

                        @if($errors->any())
                            <div class="alert alert-danger mb-3">
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

                        <form
                            action="{{ route('admissions.store') }}"
                            method="POST">

                            @csrf

                            <div class="mb-3">
                                <label class="form-label">
                                    Admission No
                                </label>

                                <input
                                    type="text"
                                    name="admission_no"
                                    class="form-control"
                                    value="{{ old('admission_no') }}"
                                    required
                                >
                            </div>

                            <div class="mb-3">
                                <label class="form-label">
                                    Student Reg No
                                </label>

                                <input
                                    type="text"
                                    name="student_reg_no"
                                    class="form-control"
                                    value="{{ old('student_reg_no') }}"
                                    required
                                >
                            </div>

                            <div class="mb-3">
                                <label class="form-label">
                                    Course
                                </label>

                                <input
                                    type="text"
                                    name="course"
                                    class="form-control"
                                    value="{{ old('course') }}"
                                    placeholder="Example: Software Engineering"
                                    required
                                >
                            </div>

                            <div class="mb-3">
                                <label class="form-label">
                                    Admission Date
                                </label>

                                <input
                                    type="date"
                                    name="admission_date"
                                    class="form-control"
                                    value="{{ old('admission_date') }}"
                                    required
                                >
                            </div>

                            <div class="mb-4">
                                <label class="form-label">
                                    Status
                                </label>

                                <select
                                    name="status"
                                    class="form-select"
                                    required>

                                    <option value="Active"
                                        {{ old('status', 'Active') == 'Active' ? 'selected' : '' }}>
                                        Active
                                    </option>

                                    <option value="Inactive"
                                        {{ old('status') == 'Inactive' ? 'selected' : '' }}>
                                        Inactive
                                    </option>

                                    <option value="Pending"
                                        {{ old('status') == 'Pending' ? 'selected' : '' }}>
                                        Pending
                                    </option>

                                </select>
                            </div>

                            <button
                                type="submit"
                                class="add-admission-btn">

                                <i class="bi bi-plus-circle me-2"></i>
                                Add Admission

                            </button>

                        </form>

                    </div>

                </div>

            </div>

            <div class="col-lg-7">

                <div class="modern-card">

                    <div class="modern-card-header admission-list-header">

                        <div>
                            <h3 class="modern-card-title">
                                <i class="bi bi-clipboard2-data me-2"></i>
                                Admission List
                            </h3>

                            <small>
                                Registered admissions
                            </small>
                        </div>

                        <div class="student-count" style="
                            background: rgba(255,255,255,0.16);
                            border: 1px solid rgba(255,255,255,0.25);
                            color: #ffffff;
                            border-radius: 999px;
                            padding: 8px 13px;
                            font-size: 12px;
                            font-weight: 600;
                            white-space: nowrap;">
                            {{ $admissions->count() }} Admissions
                        </div>

                    </div>

                    <div class="modern-card-body">

                        <div class="admission-table-wrap">

                            <table class="table align-middle admission-table">

                                <thead>
                                <tr>
                                    <th>Admission No</th>
                                    <th>Reg No</th>
                                    <th>Course</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th class="action-column">Action</th>
                                </tr>
                                </thead>

                                <tbody>

                                @forelse($admissions as $admission)

                                    <tr>

                                        <td>
                                            <span class="admission-number">
                                                {{ $admission->admission_no }}
                                            </span>
                                        </td>

                                        <td>
                                            <span class="reg-badge">
                                                {{ $admission->student_reg_no }}
                                            </span>
                                        </td>

                                        <td>
                                            {{ $admission->course }}
                                        </td>

                                        <td>
                                            {{ $admission->admission_date }}
                                        </td>

                                        <td>
                                            @php
                                                $status = strtolower($admission->status);
                                            @endphp

                                            <span class="status-badge
                                                {{ $status === 'active' ? 'status-active' : ($status === 'pending' ? 'status-pending' : 'status-inactive') }}">

                                                <i class="bi bi-circle-fill me-1" style="font-size: 7px;"></i>
                                                {{ $admission->status }}

                                            </span>
                                        </td>

                                        <td class="action-cell">

                                            <a
                                                href="{{ route('admissions.edit', $admission) }}"
                                                class="btn btn-warning btn-sm action-btn">

                                                <i class="bi bi-pencil-square me-1"></i>
                                                Update

                                            </a>

                                            <form
                                                action="{{ route('admissions.destroy', $admission) }}"
                                                method="POST">

                                                @csrf
                                                @method('DELETE')

                                                <button
                                                    type="submit"
                                                    class="btn btn-danger btn-sm action-btn"
                                                    onclick="return confirm('Delete this admission?')">

                                                    <i class="bi bi-trash me-1"></i>
                                                    Delete

                                                </button>

                                            </form>

                                        </td>

                                    </tr>

                                @empty

                                    <tr>
                                        <td
                                            colspan="6"
                                            class="text-center empty-state">

                                            <i class="bi bi-clipboard2-x d-block"></i>

                                            <div class="empty-state-title">
                                                No admissions found
                                            </div>

                                            <small>
                                                Add an admission to see it here.
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
