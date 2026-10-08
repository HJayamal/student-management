@extends('layouts.app')

@section('title', 'Update Exam')

@section('content')

    <style>
        .exam-edit-page {
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

        .exam-edit-page .form-label {
            color: #334155 !important;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .exam-edit-page .form-control,
        .exam-edit-page .form-select {
            min-height: 44px;
            border: 1px solid #dbe3ef !important;
            border-radius: 10px !important;
            background: #ffffff !important;
            color: #1e293b !important;
            box-shadow: none !important;
        }

        .exam-edit-page .form-control:focus,
        .exam-edit-page .form-select:focus {
            border-color: #6366f1 !important;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.12) !important;
        }

        .edit-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 8px;
        }

        .update-exam-btn {
            border: none;
            border-radius: 10px;
            background: linear-gradient(135deg, #10b981, #059669);
            color: #ffffff;
            font-weight: 600;
            padding: 11px 18px;
        }

        .update-exam-btn:hover {
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

        .exam-edit-page .alert {
            border-radius: 10px !important;
            border: none !important;
        }

        .exam-edit-page .alert-danger {
            background: #fef2f2 !important;
            color: #b91c1c !important;
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

    @php
        $subjects = \App\Models\Subject::all();
        $courses = $subjects
            ->pluck('course_name')
            ->filter()
            ->unique()
            ->sort()
            ->values();
    @endphp

    <div class="exam-edit-page">

        <div class="row justify-content-center">

            <div class="col-lg-7 col-md-9">

                <div class="edit-card">

                    <div class="edit-header">
                        <h3>
                            <i class="bi bi-pencil-square me-2"></i>
                            Update Exam
                        </h3>

                        <small>
                            Edit examination information
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
                            action="{{ route('exams.update', $exam) }}"
                            method="POST">

                            @csrf
                            @method('PUT')

                            <!-- Exam Name -->
                            <div class="mb-3">
                                <label class="form-label">
                                    Exam Name
                                </label>

                                <select
                                    name="exam_name"
                                    class="form-select"
                                    required>

                                    <option value="">Select Exam</option>

                                    <option value="Quiz"
                                        {{ old('exam_name', $exam->exam_name) == 'Quiz' ? 'selected' : '' }}>
                                        Quiz
                                    </option>

                                    <option value="Assignment"
                                        {{ old('exam_name', $exam->exam_name) == 'Assignment' ? 'selected' : '' }}>
                                        Assignment
                                    </option>

                                    <option value="Mid Term Exam"
                                        {{ old('exam_name', $exam->exam_name) == 'Mid Term Exam' ? 'selected' : '' }}>
                                        Mid Term Exam
                                    </option>

                                    <option value="Final Exam"
                                        {{ old('exam_name', $exam->exam_name) == 'Final Exam' ? 'selected' : '' }}>
                                        Final Exam
                                    </option>

                                    @if(
                                        $exam->exam_name &&
                                        !in_array($exam->exam_name, ['Quiz', 'Assignment', 'Mid Term Exam', 'Final Exam'])
                                    )
                                        <option value="{{ $exam->exam_name }}" selected>
                                            {{ $exam->exam_name }}
                                        </option>
                                    @endif

                                </select>
                            </div>

                            <!-- Course Name -->
                            <div class="mb-3">
                                <label class="form-label">
                                    Course Name
                                </label>

                                <select
                                    name="course_name"
                                    id="courseName"
                                    class="form-select"
                                    required>

                                    <option value="">Select Course</option>

                                    @foreach($courses as $course)
                                        <option
                                            value="{{ $course }}"
                                            {{ old('course_name', $exam->course_name) == $course ? 'selected' : '' }}>
                                            {{ $course }}
                                        </option>
                                    @endforeach

                                    @if(
                                        $exam->course_name &&
                                        !$courses->contains($exam->course_name)
                                    )
                                        <option value="{{ $exam->course_name }}" selected>
                                            {{ $exam->course_name }}
                                        </option>
                                    @endif

                                </select>
                            </div>

                            <!-- Subject -->
                            <div class="mb-3">
                                <label class="form-label">
                                    Subject
                                </label>

                                <select
                                    name="subject"
                                    id="subjectName"
                                    class="form-select"
                                    required>

                                    <option value="">Select Subject</option>

                                    @foreach($subjects as $subject)
                                        @if($subject->course_name)
                                            <option
                                                value="{{ $subject->subject_name }}"
                                                data-course="{{ $subject->course_name }}"
                                                {{ old('subject', $exam->subject) == $subject->subject_name ? 'selected' : '' }}>
                                                {{ $subject->subject_name }}
                                            </option>
                                        @endif
                                    @endforeach

                                    @if($exam->subject)
                                        @php
                                            $currentSubjectExists = $subjects
                                                ->where('subject_name', $exam->subject)
                                                ->where('course_name', $exam->course_name)
                                                ->isNotEmpty();
                                        @endphp

                                        @if(!$currentSubjectExists)
                                            <option value="{{ $exam->subject }}" selected>
                                                {{ $exam->subject }}
                                            </option>
                                        @endif
                                    @endif

                                </select>
                            </div>

                            <!-- Marks -->
                            <div class="mb-3">
                                <label class="form-label">
                                    Marks
                                </label>

                                <input
                                    type="number"
                                    name="marks"
                                    class="form-control"
                                    placeholder="100"
                                    value="{{ old('marks', $exam->marks) }}"
                                    min="0"
                                    max="100"
                                    required
                                >
                            </div>

                            <!-- Exam Date -->
                            <div class="mb-3">
                                <label class="form-label">
                                    Exam Date
                                </label>

                                <input
                                    type="date"
                                    name="exam_date"
                                    value="{{ old('exam_date', $exam->exam_date) }}"
                                    class="form-control"
                                    required
                                >
                            </div>

                            <!-- Duration -->
                            <div class="mb-3">
                                <label class="form-label">
                                    Duration
                                </label>

                                <input
                                    type="text"
                                    name="duration"
                                    value="{{ old('duration', $exam->duration) }}"
                                    class="form-control"
                                    placeholder="2 Hours"
                                    required
                                >
                            </div>

                            <!-- Status -->
                            <div class="mb-4">
                                <label class="form-label">
                                    Status
                                </label>

                                <select
                                    name="status"
                                    class="form-select"
                                    required>

                                    <option value="Scheduled"
                                        {{ old('status', $exam->status) == 'Scheduled' ? 'selected' : '' }}>
                                        Scheduled
                                    </option>

                                    <option value="Completed"
                                        {{ old('status', $exam->status) == 'Completed' ? 'selected' : '' }}>
                                        Completed
                                    </option>

                                    @if(
                                        $exam->status &&
                                        !in_array($exam->status, ['Scheduled', 'Completed'])
                                    )
                                        <option value="{{ $exam->status }}" selected>
                                            {{ $exam->status }}
                                        </option>
                                    @endif

                                </select>
                            </div>

                            <div class="edit-actions">

                                <button
                                    type="submit"
                                    class="update-exam-btn">

                                    <i class="bi bi-check-circle me-1"></i>
                                    Update Exam

                                </button>

                                <a
                                    href="{{ route('exams.index') }}"
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

    <script>
        document.addEventListener('DOMContentLoaded', function () {

            const courseSelect = document.getElementById('courseName');
            const subjectSelect = document.getElementById('subjectName');

            if (!courseSelect || !subjectSelect) {
                return;
            }

            const allSubjectOptions = Array.from(subjectSelect.options)
                .slice(1)
                .map(function (option) {
                    return option.cloneNode(true);
                });

            const currentSubject = @json(old('subject', $exam->subject));

            function filterSubjects() {

                const selectedCourse = courseSelect.value;

                subjectSelect.innerHTML =
                    '<option value="">Select Subject</option>';

                let matchedCurrentSubject = false;

                allSubjectOptions.forEach(function (option) {

                    if (option.dataset.course === selectedCourse) {

                        const newOption = option.cloneNode(true);

                        if (newOption.value === currentSubject) {
                            newOption.selected = true;
                            matchedCurrentSubject = true;
                        }

                        subjectSelect.appendChild(newOption);
                    }
                });

                // Keep the existing subject visible when it is not linked
                // to the selected course yet.
                if (
                    selectedCourse &&
                    currentSubject &&
                    !matchedCurrentSubject &&
                    selectedCourse === @json($exam->course_name)
                ) {
                    const oldOption = document.createElement('option');
                    oldOption.value = currentSubject;
                    oldOption.textContent = currentSubject;
                    oldOption.selected = true;
                    subjectSelect.appendChild(oldOption);
                }
            }

            courseSelect.addEventListener('change', function () {

                const selectedCourse = this.value;

                subjectSelect.innerHTML =
                    '<option value="">Select Subject</option>';

                allSubjectOptions.forEach(function (option) {

                    if (option.dataset.course === selectedCourse) {
                        subjectSelect.appendChild(option.cloneNode(true));
                    }

                });
            });

            filterSubjects();

        });
    </script>

@endsection
