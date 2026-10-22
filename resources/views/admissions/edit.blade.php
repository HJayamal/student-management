@extends('layouts.app')

@section('title', 'Edit Admission')

@section('content')

    @php
        $matchingCourse = $courses->firstWhere('course_name', $admission->course);

        $matchingDegree = $degrees->first(function ($degree) use ($admission) {
            return $degree->degree_name === $admission->degree
                && $degree->course?->course_name === $admission->course;
        });

        $matchingBatch = $batches->first(function ($batch) use ($admission) {
            return $batch->batch_name === $admission->batch
                && $batch->degree?->degree_name === $admission->degree
                && $batch->degree?->course?->course_name === $admission->course;
        });

        $selectedCourseId = old('course_id', $matchingCourse?->id);
        $selectedDegreeId = old('degree_id', $matchingDegree?->id);
        $selectedBatchId = old('batch_id', $matchingBatch?->id);
    @endphp

    <style>
        .admission-edit-page {
            max-width: 900px;
            margin: 0 auto;
            padding: 10px 5px 35px;
        }

        .admission-edit-title {
            color: #0f172a;
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .admission-edit-subtitle {
            color: #64748b;
            font-size: 14px;
            margin-bottom: 24px;
        }

        .admission-edit-card {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 8px 25px rgba(15, 23, 42, .06);
        }

        .admission-edit-card-header {
            padding: 20px 24px;
            color: #fff;
            background: linear-gradient(135deg, #4f46e5, #6366f1);
        }

        .admission-edit-card-header h2 {
            margin: 0 0 5px;
            font-size: 19px;
            font-weight: 700;
        }

        .admission-edit-card-header p {
            margin: 0;
            color: rgba(255,255,255,.86);
            font-size: 13px;
        }

        .admission-edit-card-body {
            padding: 24px;
        }

        .admission-edit-page .form-label {
            display: block;
            margin-bottom: 8px;
            color: #334155;
            font-size: 14px;
            font-weight: 600;
        }

        .admission-edit-page .form-control,
        .admission-edit-page .form-select {
            min-height: 45px;
            padding: 10px 13px;
            border: 1px solid #dbe3ef;
            border-radius: 9px;
            background-color: #fff;
            color: #1e293b;
            box-shadow: none;
        }

        .admission-edit-page .form-control:focus,
        .admission-edit-page .form-select:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, .12);
        }

        .field-hint {
            display: block;
            margin-top: 6px;
            color: #64748b;
            font-size: 12px;
        }

        .admission-edit-page .alert {
            border: 0;
            border-radius: 10px;
        }

        .admission-edit-actions {
            display: flex;
            justify-content: flex-end;
            flex-wrap: wrap;
            gap: 10px;
            padding-top: 8px;
        }

        .admission-edit-actions .btn {
            min-height: 42px;
            padding: 10px 17px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
        }

        @media (max-width: 576px) {
            .admission-edit-card-body { padding: 17px; }
            .admission-edit-title { font-size: 24px; }
            .admission-edit-actions { flex-direction: column-reverse; }
            .admission-edit-actions .btn { width: 100%; }
        }
    </style>

    <div class="admission-edit-page">
        <div class="mb-4">
            <h1 class="admission-edit-title">
                <i class="bi bi-pencil-square me-2" style="color:#4f46e5;"></i>
                Edit Student Admission
            </h1>
            <p class="admission-edit-subtitle">
                Update the student admission and academic details.
            </p>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger mb-3" role="alert">
                <strong>Please fix the following errors:</strong>
                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="admission-edit-card">
            <div class="admission-edit-card-header">
                <h2><i class="bi bi-person-lines-fill me-2"></i>Admission Information</h2>
                <p>Choose a registered Course, Degree and Batch.</p>
            </div>

            <div class="admission-edit-card-body">
                <form action="{{ route('admissions.update', $admission) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="admission_no" class="form-label">Admission Number *</label>
                            <input
                                type="text"
                                id="admission_no"
                                name="admission_no"
                                class="form-control"
                                value="{{ old('admission_no', $admission->admission_no) }}"
                                maxlength="255"
                                required
                            >
                        </div>

                        <div class="col-md-6">
                            <label for="reg_no" class="form-label">Student Registration Number *</label>
                            <input
                                type="text"
                                id="reg_no"
                                name="reg_no"
                                class="form-control"
                                value="{{ old('reg_no', $admission->reg_no) }}"
                                maxlength="255"
                                required
                            >
                        </div>

                        <div class="col-md-4">
                            <label for="course_id" class="form-label">Course *</label>
                            <select id="course_id" name="course_id" class="form-select" required>
                                <option value="" @selected(!$selectedCourseId)>Select a course</option>
                                @foreach ($courses as $course)
                                    <option
                                        value="{{ $course->id }}"
                                        @selected((string) $selectedCourseId === (string) $course->id)
                                    >
                                        {{ $course->course_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label for="degree_id" class="form-label">Degree *</label>
                            <select id="degree_id" name="degree_id" class="form-select" required>
                                <option value="" @selected(!$selectedDegreeId)>Select a degree</option>
                                @foreach ($degrees as $degree)
                                    <option
                                        value="{{ $degree->id }}"
                                        data-course-id="{{ $degree->course_id }}"
                                        @selected((string) $selectedDegreeId === (string) $degree->id)
                                    >
                                        {{ $degree->degree_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label for="batch_id" class="form-label">Batch *</label>
                            <select id="batch_id" name="batch_id" class="form-select" required>
                                <option value="" @selected(!$selectedBatchId)>Select a batch</option>
                                @foreach ($batches as $batch)
                                    <option
                                        value="{{ $batch->id }}"
                                        data-degree-id="{{ $batch->degree_id }}"
                                        @selected((string) $selectedBatchId === (string) $batch->id)
                                    >
                                        {{ $batch->batch_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="admission_date" class="form-label">Admission Date *</label>
                            <input
                                type="date"
                                id="admission_date"
                                name="admission_date"
                                class="form-control"
                                value="{{ old('admission_date', $admission->admission_date ? \Illuminate\Support\Carbon::parse($admission->admission_date)->format('Y-m-d') : '') }}"
                                required
                            >
                        </div>

                        <div class="col-md-6">
                            <label for="status" class="form-label">Status *</label>
                            <select id="status" name="status" class="form-select" required>
                                @foreach (['Active', 'Inactive', 'Pending'] as $statusOption)
                                    <option
                                        value="{{ $statusOption }}"
                                        @selected(old('status', $admission->status) === $statusOption)
                                    >
                                        {{ $statusOption }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="admission-edit-actions mt-4">
                        <a href="{{ route('admissions.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-1"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i> Update Admission
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const courseSelect = document.getElementById('course_id');
            const degreeSelect = document.getElementById('degree_id');
            const batchSelect = document.getElementById('batch_id');

            function filterDegrees(clearSelection = false) {
                if (clearSelection) {
                    degreeSelect.value = '';
                    batchSelect.value = '';
                }

                const courseId = courseSelect.value;

                Array.from(degreeSelect.options).forEach(function (option) {
                    if (!option.value) {
                        option.hidden = false;
                        option.disabled = false;
                        return;
                    }

                    const matching = courseId !== '' && option.dataset.courseId === courseId;
                    option.hidden = !matching;
                    option.disabled = !matching;
                });

                if (degreeSelect.value && degreeSelect.selectedOptions[0]?.hidden) {
                    degreeSelect.value = '';
                }

                filterBatches(false);
            }

            function filterBatches(clearSelection = false) {
                if (clearSelection) {
                    batchSelect.value = '';
                }

                const degreeId = degreeSelect.value;

                Array.from(batchSelect.options).forEach(function (option) {
                    if (!option.value) {
                        option.hidden = false;
                        option.disabled = false;
                        return;
                    }

                    const matching = degreeId !== '' && option.dataset.degreeId === degreeId;
                    option.hidden = !matching;
                    option.disabled = !matching;
                });

                if (batchSelect.value && batchSelect.selectedOptions[0]?.hidden) {
                    batchSelect.value = '';
                }
            }

            courseSelect.addEventListener('change', function () {
                filterDegrees(true);
            });

            degreeSelect.addEventListener('change', function () {
                filterBatches(true);
            });

            // Keep the existing admission's academic values selected on initial load.
            filterDegrees(false);
        });
    </script>

@endsection
