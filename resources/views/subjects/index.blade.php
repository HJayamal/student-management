@extends('layouts.app')

@section('title', 'Subjects')

@section('content')

    <style>
        .subject-page {
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

        .subject-form-header {
            background: linear-gradient(135deg, #10b981, #059669) !important;
        }

        .subject-list-header {
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

        .subject-page .form-label {
            color: #334155 !important;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .subject-page .form-control {
            background: #ffffff !important;
            color: #1e293b !important;
            border: 1px solid #dbe3ef !important;
            border-radius: 10px !important;
            padding: 11px 13px !important;
            min-height: 44px;
            box-shadow: none !important;
            transition: all 0.2s ease;
        }

        .subject-page .form-control:focus {
            background: #ffffff !important;
            color: #1e293b !important;
            border-color: #6366f1 !important;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.12) !important;
        }

        .subject-page .form-control::placeholder {
            color: #94a3b8 !important;
            opacity: 1;
        }

        .subject-page textarea.form-control {
            min-height: 110px;
            resize: vertical;
        }

        .add-subject-btn {
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

        .add-subject-btn:hover {
            color: #ffffff !important;
            transform: translateY(-1px);
            box-shadow: 0 7px 18px rgba(16, 185, 129, 0.22);
        }

        .subject-page .alert {
            border-radius: 10px !important;
            border: none !important;
        }

        .subject-page .alert-success {
            background: #ecfdf5 !important;
            color: #047857 !important;
        }

        .subject-page .alert-danger {
            background: #fef2f2 !important;
            color: #b91c1c !important;
        }

        .subject-count {
            background: rgba(255, 255, 255, 0.16);
            border: 1px solid rgba(255, 255, 255, 0.25);
            color: #ffffff;
            border-radius: 999px;
            padding: 8px 13px;
            font-size: 12px;
            font-weight: 600;
            white-space: nowrap;
        }

        .subject-table-wrap {
            overflow-x: auto;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
        }

        .subject-table {
            width: 100%;
            min-width: 860px;
            margin-bottom: 0 !important;
            background: #ffffff !important;
        }

        .subject-table thead th {
            background: #f8fafc !important;
            color: #334155 !important;
            border-bottom: 1px solid #e2e8f0 !important;
            border-top: none !important;
            font-size: 13px;
            font-weight: 700;
            padding: 14px 12px !important;
            white-space: nowrap;
        }

        .subject-table tbody tr {
            background: #ffffff !important;
        }

        .subject-table tbody td {
            background: #ffffff !important;
            color: #334155 !important;
            border-color: #e2e8f0 !important;
            padding: 13px 12px !important;
            vertical-align: middle;
            font-size: 13px;
        }

        .subject-table tbody tr:hover td {
            background: #f8faff !important;
        }

        .code-badge {
            display: inline-flex;
            align-items: center;
            background: #eef2ff;
            color: #4338ca !important;
            padding: 6px 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
        }

        .course-badge {
            display: inline-flex;
            align-items: center;
            background: #ecfdf5;
            color: #047857 !important;
            padding: 6px 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
            white-space: nowrap;
        }

        .subject-name {
            color: #0f172a !important;
            font-weight: 700;
        }

        .teacher-name {
            color: #475569 !important;
            font-weight: 600;
        }

        .description-text {
            color: #64748b !important;
            max-width: 260px;
            line-height: 1.45;
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
            .subject-page {
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

            .subject-count {
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

    <div class="subject-page">

        <div class="mb-4">
            <div class="page-title">
                Subject Management
            </div>

            <div class="page-subtitle">
                Add and manage subject information
            </div>
        </div>

        <div class="row g-4">

            <!-- ADD SUBJECT -->
            <div class="col-lg-5">

                <div class="modern-card">

                    <div class="modern-card-header subject-form-header">

                        <div>
                            <h3 class="modern-card-title">
                                <i class="bi bi-book me-2"></i>
                                Add Subject
                            </h3>

                            <small>
                                Add a new subject
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
                            action="{{ route('subjects.store') }}"
                            method="POST">

                            @csrf

                            <div class="mb-3">
                                <label class="form-label">
                                    Subject Code
                                </label>

                                <input
                                    type="text"
                                    name="subject_code"
                                    class="form-control"
                                    placeholder="SE101"
                                    value="{{ old('subject_code') }}"
                                    required
                                >
                            </div>

                            <div class="mb-3">
                                <label class="form-label">
                                    Course Name
                                </label>

                                <input
                                    type="text"
                                    name="course_name"
                                    class="form-control"
                                    placeholder="BSc Software Engineering"
                                    value="{{ old('course_name') }}"
                                    required
                                >
                            </div>

                            <div class="mb-3">
                                <label class="form-label">
                                    Subject Name
                                </label>

                                <input
                                    type="text"
                                    name="subject_name"
                                    class="form-control"
                                    placeholder="Software Engineering"
                                    value="{{ old('subject_name') }}"
                                    required
                                >
                            </div>

                            <div class="mb-3">
                                <label class="form-label">
                                    Teacher
                                </label>

                                <input
                                    type="text"
                                    name="teacher"
                                    class="form-control"
                                    placeholder="Teacher Name"
                                    value="{{ old('teacher') }}"
                                    required
                                >
                            </div>

                            <div class="mb-4">
                                <label class="form-label">
                                    Description
                                </label>

                                <textarea
                                    name="description"
                                    class="form-control"
                                    rows="4"
                                    placeholder="Subject description"
                                >{{ old('description') }}</textarea>
                            </div>

                            <button
                                type="submit"
                                class="add-subject-btn">

                                <i class="bi bi-plus-circle me-2"></i>
                                Add Subject

                            </button>

                        </form>

                    </div>

                </div>

            </div>

            <!-- SUBJECT LIST -->
            <div class="col-lg-7">

                <div class="modern-card">

                    <div class="modern-card-header subject-list-header">

                        <div>
                            <h3 class="modern-card-title">
                                <i class="bi bi-journal-bookmark-fill me-2"></i>
                                Subject List
                            </h3>

                            <small>
                                Registered subjects
                            </small>
                        </div>

                        <div class="subject-count">
                            {{ $subjects->count() }} Subjects
                        </div>

                    </div>

                    <div class="modern-card-body">

                        <div class="subject-table-wrap">

                            <table class="table align-middle subject-table">

                                <thead>
                                <tr>
                                    <th>Code</th>
                                    <th>Course</th>
                                    <th>Subject</th>
                                    <th>Teacher</th>
                                    <th>Description</th>
                                    <th class="action-column">Action</th>
                                </tr>
                                </thead>

                                <tbody>

                                @forelse($subjects as $subject)

                                    <tr>

                                        <td>
                                            <span class="code-badge">
                                                <i class="bi bi-hash me-1"></i>
                                                {{ $subject->subject_code }}
                                            </span>
                                        </td>

                                        <td>
                                            <span class="course-badge">
                                                <i class="bi bi-mortarboard-fill me-1"></i>
                                                {{ $subject->course_name ?: 'No course' }}
                                            </span>
                                        </td>

                                        <td>
                                            <span class="subject-name">
                                                {{ $subject->subject_name }}
                                            </span>
                                        </td>

                                        <td>
                                            <span class="teacher-name">
                                                {{ $subject->teacher }}
                                            </span>
                                        </td>

                                        <td>
                                            <span class="description-text">
                                                {{ $subject->description ?: 'No description' }}
                                            </span>
                                        </td>

                                        <td class="action-cell">

                                            <a
                                                href="{{ route('subjects.edit', $subject) }}"
                                                class="btn btn-warning btn-sm action-btn">

                                                <i class="bi bi-pencil-square me-1"></i>
                                                Update

                                            </a>

                                            <form
                                                action="{{ route('subjects.destroy', $subject) }}"
                                                method="POST">

                                                @csrf
                                                @method('DELETE')

                                                <button
                                                    type="submit"
                                                    class="btn btn-danger btn-sm action-btn"
                                                    onclick="return confirm('Delete this subject?')">

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

                                            <i class="bi bi-journal-x d-block mb-2"></i>

                                            <div class="empty-state-title">
                                                No subjects found
                                            </div>

                                            <small>
                                                Add a subject to see it here.
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
