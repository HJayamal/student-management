@extends('layouts.app')

@section('title', 'Update Subject')

@section('content')

    <style>
        .subject-edit-page {
            background: #f8fafc;
            min-height: calc(100vh - 120px);
            padding: 20px 5px 35px;
        }

        .edit-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 8px 28px rgba(15, 23, 42, 0.07);
        }

        .edit-header {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: #ffffff;
            padding: 18px 22px;
        }

        .edit-header h3 {
            margin: 0;
            font-size: 20px;
            font-weight: 700;
        }

        .edit-header small {
            color: rgba(255, 255, 255, 0.85);
        }

        .edit-body {
            padding: 26px;
        }

        .subject-edit-page .form-label {
            color: #334155;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .subject-edit-page .form-control {
            min-height: 44px;
            border: 1px solid #dbe3ef;
            border-radius: 10px;
            background: #ffffff;
            color: #1e293b;
            box-shadow: none;
        }

        .subject-edit-page .form-control:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.12);
        }

        .subject-edit-page textarea.form-control {
            min-height: 100px;
            resize: vertical;
        }

        .edit-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 8px;
        }

        .update-subject-btn {
            border: none;
            border-radius: 10px;
            background: linear-gradient(135deg, #10b981, #059669);
            color: #ffffff;
            font-weight: 600;
            padding: 11px 18px;
        }

        .update-subject-btn:hover {
            background: linear-gradient(135deg, #059669, #047857);
            color: #ffffff;
        }

        .back-btn {
            border: none;
            border-radius: 10px;
            background: #e2e8f0;
            color: #334155;
            font-weight: 600;
            padding: 11px 18px;
            text-decoration: none;
        }

        .back-btn:hover {
            background: #cbd5e1;
            color: #1e293b;
        }

        .subject-edit-page .alert {
            border-radius: 10px;
            border: none;
        }

        .subject-edit-page .alert-danger {
            background: #fef2f2;
            color: #b91c1c;
        }

        @media (max-width: 576px) {
            .edit-body {
                padding: 18px;
            }

            .edit-header {
                padding: 15px 18px;
            }
        }
    </style>

    <div class="subject-edit-page">

        <div class="row justify-content-center">

            <div class="col-lg-7 col-md-9">

                <div class="edit-card">

                    <div class="edit-header">
                        <h3>
                            <i class="bi bi-pencil-square me-2"></i>
                            Update Subject
                        </h3>

                        <small>
                            Edit subject information
                        </small>
                    </div>

                    <div class="edit-body">

                        @if($errors->any())
                            <div class="alert alert-danger mb-4">
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
                            action="{{ route('subjects.update', $subject) }}"
                            method="POST">

                            @csrf
                            @method('PUT')

                            <!-- Subject Code -->
                            <div class="mb-3">
                                <label class="form-label">
                                    Subject Code
                                </label>

                                <input
                                    type="text"
                                    name="subject_code"
                                    value="{{ old('subject_code', $subject->subject_code) }}"
                                    class="form-control"
                                    required
                                >
                            </div>

                            <!-- Course Name -->
                            <div class="mb-3">
                                <label class="form-label">
                                    Course Name
                                </label>

                                <input
                                    type="text"
                                    name="course_name"
                                    value="{{ old('course_name', $subject->course_name) }}"
                                    class="form-control"
                                    placeholder="BSc Software Engineering"
                                    required
                                >
                            </div>

                            <!-- Subject Name -->
                            <div class="mb-3">
                                <label class="form-label">
                                    Subject Name
                                </label>

                                <input
                                    type="text"
                                    name="subject_name"
                                    value="{{ old('subject_name', $subject->subject_name) }}"
                                    class="form-control"
                                    required
                                >
                            </div>

                            <!-- Teacher -->
                            <div class="mb-3">
                                <label class="form-label">
                                    Teacher
                                </label>

                                <input
                                    type="text"
                                    name="teacher"
                                    value="{{ old('teacher', $subject->teacher) }}"
                                    class="form-control"
                                    required
                                >
                            </div>

                            <!-- Description -->
                            <div class="mb-4">
                                <label class="form-label">
                                    Description
                                </label>

                                <textarea
                                    name="description"
                                    class="form-control"
                                    rows="4"
                                >{{ old('description', $subject->description) }}</textarea>
                            </div>

                            <div class="edit-actions">

                                <button
                                    type="submit"
                                    class="update-subject-btn">

                                    <i class="bi bi-check-circle me-1"></i>
                                    Update Subject

                                </button>

                                <a
                                    href="{{ route('subjects.index') }}"
                                    class="back-btn">

                                    <i class="bi bi-arrow-left me-1"></i>
                                    Back

                                </a>

                            </div>

                        </form>

                    </div>

                </div>

            </div>

        </div>

    </div>

@endsection
