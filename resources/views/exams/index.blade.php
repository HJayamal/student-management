@extends('layouts.app')

@section('title', 'Exams')

@section('content')

    <style>
        .exam-page {
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
            gap: 15px;
        }

        .exam-form-header {
            background: linear-gradient(135deg, #10b981, #059669) !important;
        }

        .exam-list-header {
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

        .exam-page .form-label {
            color: #334155 !important;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .exam-page .form-control,
        .exam-page .form-select {
            background: #ffffff !important;
            color: #1e293b !important;
            border: 1px solid #dbe3ef !important;
            border-radius: 10px !important;
            padding: 11px 13px !important;
            min-height: 44px;
            box-shadow: none !important;
            transition: all 0.2s ease;
        }

        .exam-page .form-control:focus,
        .exam-page .form-select:focus {
            background: #ffffff !important;
            color: #1e293b !important;
            border-color: #6366f1 !important;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.12) !important;
        }

        .exam-page .form-control::placeholder {
            color: #94a3b8 !important;
            opacity: 1;
        }

        .exam-page input[type="date"] {
            color: #1e293b !important;
        }

        .add-exam-btn {
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

        .add-exam-btn:hover {
            color: #ffffff !important;
            transform: translateY(-1px);
            box-shadow: 0 7px 18px rgba(16, 185, 129, 0.22);
        }

        .exam-page .alert {
            border-radius: 10px !important;
            border: none !important;
        }

        .exam-page .alert-success {
            background: #ecfdf5 !important;
            color: #047857 !important;
        }

        .exam-page .alert-danger {
            background: #fef2f2 !important;
            color: #b91c1c !important;
        }

        .exam-count {
            background: rgba(255, 255, 255, 0.16);
            border: 1px solid rgba(255, 255, 255, 0.25);
            color: #ffffff;
            border-radius: 999px;
            padding: 8px 13px;
            font-size: 12px;
            font-weight: 600;
            white-space: nowrap;
        }

        .exam-table-wrap {
            overflow-x: auto;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
        }

        .exam-table {
            width: 100%;
            min-width: 820px;
            margin-bottom: 0 !important;
            background: #ffffff !important;
        }

        .exam-table thead th {
            background: #f8fafc !important;
            color: #334155 !important;
            border-bottom: 1px solid #e2e8f0 !important;
            border-top: none !important;
            font-size: 13px;
            font-weight: 700;
            padding: 14px 12px !important;
            white-space: nowrap;
        }

        .exam-table tbody tr {
            background: #ffffff !important;
        }

        .exam-table tbody td {
            background: #ffffff !important;
            color: #334155 !important;
            border-color: #e2e8f0 !important;
            padding: 13px 12px !important;
            vertical-align: middle;
            font-size: 13px;
        }

        .exam-table tbody tr:hover td {
            background: #f8faff !important;
        }

        .exam-name {
            color: #0f172a !important;
            font-weight: 700;
            line-height: 1.35;
        }

        .subject-badge {
            display: inline-flex;
            align-items: center;
            background: #eef2ff;
            color: #4338ca !important;
            padding: 6px 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
        }

        .date-text {
            color: #475569 !important;
            font-weight: 600;
            white-space: nowrap;
        }

        .duration-badge {
            display: inline-flex;
            align-items: center;
            background: #f1f5f9;
            color: #475569 !important;
            padding: 6px 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 600;
            white-space: nowrap;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 6px 11px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
            white-space: nowrap;
        }

        .status-scheduled {
            background: #dbeafe;
            color: #1d4ed8 !important;
        }

        .status-completed {
            background: #dcfce7;
            color: #15803d !important;
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
            font-size: 48px;
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
            .exam-page {
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

            .exam-count {
                padding: 7px 10px;
                font-size: 11px;
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

    <div class="exam-page">

        <div class="mb-4">
            <div class="page-title">
                Exam Management
            </div>

            <div class="page-subtitle">
                Schedule and manage examination information
            </div>
        </div>

        <div class="row g-4">

            <!-- ADD EXAM -->
            <div class="col-lg-5">

                <div class="modern-card">

                    <div class="modern-card-header exam-form-header">

                        <div>
                            <h3 class="modern-card-title">
                                <i class="bi bi-calendar-plus me-2"></i>
                                Add Exam
                            </h3>

                            <small>
                                Create a new examination
                            </small>
                        </div>

                    </div>

                    <div class="modern-card-body">

                        @if(session('success'))
                            <div class="alert alert-success">
                                <i class="bi bi-check-circle me-2"></i>
                                {{ session('success') }}
                            </div>
                        @endif

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

                        <form
                            action="{{ route('exams.store') }}"
                            method="POST">

                            @csrf

                            <div class="mb-3">
                                <label class="form-label">
                                    Exam Name
                                </label>

                                <input
                                    type="text"
                                    name="exam_name"
                                    class="form-control"
                                    placeholder="Mid Term Exam"
                                    value="{{ old('exam_name') }}"
                                    required
                                >
                            </div>

                            <div class="mb-3">
                                <label class="form-label">
                                    Subject
                                </label>

                                <input
                                    type="text"
                                    name="subject"
                                    class="form-control"
                                    placeholder="Software Engineering"
                                    value="{{ old('subject') }}"
                                    required
                                >
                            </div>

                            <div class="mb-3">
                                <label class="form-label">
                                    Exam Date
                                </label>

                                <input
                                    type="date"
                                    name="exam_date"
                                    class="form-control"
                                    value="{{ old('exam_date') }}"
                                    required
                                >
                            </div>

                            <div class="mb-3">
                                <label class="form-label">
                                    Duration
                                </label>

                                <input
                                    type="text"
                                    name="duration"
                                    class="form-control"
                                    placeholder="2 Hours"
                                    value="{{ old('duration') }}"
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

                                    <option value="Scheduled"
                                        {{ old('status', 'Scheduled') == 'Scheduled' ? 'selected' : '' }}>
                                        Scheduled
                                    </option>

                                    <option value="Completed"
                                        {{ old('status') == 'Completed' ? 'selected' : '' }}>
                                        Completed
                                    </option>

                                </select>
                            </div>

                            <button
                                type="submit"
                                class="add-exam-btn">

                                <i class="bi bi-plus-circle me-2"></i>
                                Add Exam

                            </button>

                        </form>

                    </div>

                </div>

            </div>

            <!-- EXAM LIST -->
            <div class="col-lg-7">

                <div class="modern-card">

                    <div class="modern-card-header exam-list-header">

                        <div>
                            <h3 class="modern-card-title">
                                <i class="bi bi-calendar2-week me-2"></i>
                                Exam List
                            </h3>

                            <small>
                                Scheduled and completed examinations
                            </small>
                        </div>

                        <div class="exam-count">
                            {{ $exams->count() }} Exams
                        </div>

                    </div>

                    <div class="modern-card-body">

                        <div class="exam-table-wrap">

                            <table class="table align-middle exam-table">

                                <thead>
                                <tr>
                                    <th>Exam</th>
                                    <th>Subject</th>
                                    <th>Date</th>
                                    <th>Duration</th>
                                    <th>Status</th>
                                    <th class="action-column">Action</th>
                                </tr>
                                </thead>

                                <tbody>

                                @forelse($exams as $exam)

                                    <tr>

                                        <td>
                                            <span class="exam-name">
                                                {{ $exam->exam_name }}
                                            </span>
                                        </td>

                                        <td>
                                            <span class="subject-badge">
                                                <i class="bi bi-book me-1"></i>
                                                {{ $exam->subject }}
                                            </span>
                                        </td>

                                        <td>
                                            <span class="date-text">
                                                <i class="bi bi-calendar3 me-1"></i>
                                                {{ $exam->exam_date }}
                                            </span>
                                        </td>

                                        <td>
                                            <span class="duration-badge">
                                                <i class="bi bi-clock me-1"></i>
                                                {{ $exam->duration }}
                                            </span>
                                        </td>

                                        <td>
                                            @php
                                                $status = strtolower($exam->status);
                                                $statusClass = match($status) {
                                                    'completed' => 'status-completed',
                                                    'pending' => 'status-pending',
                                                    default => 'status-scheduled',
                                                };
                                            @endphp

                                            <span class="status-badge {{ $statusClass }}">
                                                <i class="bi bi-circle-fill me-1" style="font-size: 7px;"></i>
                                                {{ $exam->status }}
                                            </span>
                                        </td>

                                        <td class="action-cell">

                                            <a
                                                href="{{ route('exams.edit', $exam) }}"
                                                class="btn btn-warning btn-sm action-btn">

                                                <i class="bi bi-pencil-square me-1"></i>
                                                Update

                                            </a>

                                            <form
                                                action="{{ route('exams.destroy', $exam) }}"
                                                method="POST">

                                                @csrf
                                                @method('DELETE')

                                                <button
                                                    type="submit"
                                                    class="btn btn-danger btn-sm action-btn"
                                                    onclick="return confirm('Delete this exam?')">

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

                                            <i class="bi bi-calendar-x d-block mb-2"></i>

                                            <div class="empty-state-title">
                                                No exams found
                                            </div>

                                            <small>
                                                Add an exam to see it here.
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
