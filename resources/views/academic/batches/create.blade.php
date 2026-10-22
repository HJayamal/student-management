@extends('layouts.app')

@section('title', 'Add Batch')

@section('content')

    <style>
        .batch-form-page {
            padding: 10px 5px 30px;
        }

        .batch-form-heading {
            margin-bottom: 24px;
        }

        .batch-form-heading h1 {
            color: #0f172a;
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 6px;
        }

        .batch-form-heading p {
            color: #64748b;
            margin: 0;
        }

        .batch-form-card {
            max-width: 850px;
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 8px 25px rgba(15, 23, 42, .05);
        }

        .batch-form-card-header {
            padding: 22px 26px;
            background: linear-gradient(135deg, #4f46e5, #6366f1);
            color: white;
        }

        .batch-form-card-header h2 {
            margin: 0 0 5px;
            font-size: 19px;
            font-weight: 700;
        }

        .batch-form-card-header p {
            margin: 0;
            font-size: 13px;
            opacity: .9;
        }

        .batch-form-body {
            padding: 26px;
        }

        .batch-field {
            margin-bottom: 22px;
        }

        .batch-field label {
            display: block;
            margin-bottom: 8px;
            color: #334155;
            font-size: 14px;
            font-weight: 600;
        }

        .batch-field input,
        .batch-field select {
            display: block;
            width: 100%;
            min-height: 46px;
            padding: 11px 13px;
            background: #fff;
            border: 1px solid #dbe3ef;
            border-radius: 9px;
            color: #334155;
            font-size: 14px;
        }

        .batch-field input:focus,
        .batch-field select:focus {
            outline: none;
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, .12);
        }

        .batch-field small {
            display: block;
            margin-top: 7px;
            color: #64748b;
            font-size: 12px;
        }

        .batch-form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            flex-wrap: wrap;
            padding-top: 8px;
        }

        .batch-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            padding: 11px 17px;
            border: 0;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
        }

        .batch-btn-primary {
            background: #4f46e5;
            color: white;
        }

        .batch-btn-primary:hover {
            background: #4338ca;
            color: white;
        }

        .batch-btn-secondary {
            background: #e2e8f0;
            color: #334155;
        }

        .batch-btn-secondary:hover {
            background: #cbd5e1;
            color: #0f172a;
        }

        .batch-alert {
            padding: 14px 18px;
            margin-bottom: 20px;
            border-radius: 9px;
        }

        @media (max-width: 576px) {
            .batch-form-heading h1 {
                font-size: 23px;
            }

            .batch-form-body {
                padding: 19px;
            }

            .batch-form-actions {
                flex-direction: column-reverse;
            }

            .batch-form-actions .batch-btn {
                width: 100%;
            }
        }
    </style>

    <div class="batch-form-page">

        <div class="batch-form-heading">
            <h1>
                <i class="bi bi-layers me-2" style="color:#4f46e5;"></i>
                Add New Batch
            </h1>

            <p>
                Register a batch under an existing degree.
            </p>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger batch-alert" role="alert">
                <strong>Please correct the following errors:</strong>

                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="batch-form-card">

            <div class="batch-form-card-header">
                <h2>
                    <i class="bi bi-plus-circle me-2"></i>
                    Batch Information
                </h2>

                <p>Complete the details below to register a campus batch.</p>
            </div>

            <div class="batch-form-body">

                @if ($degrees->isEmpty())

                    <div class="alert alert-warning batch-alert">
                        No degrees have been registered yet.
                        Please register a degree before creating a batch.
                    </div>

                    <div class="batch-form-actions">
                        <a href="{{ route('degrees.create') }}"
                           class="batch-btn batch-btn-primary">
                            <i class="bi bi-plus-lg"></i>
                            Add Degree
                        </a>
                    </div>

                @else

                    <form action="{{ route('batches.store') }}" method="POST">
                        @csrf

                        <div class="batch-field">
                            <label for="degree_id">Degree *</label>

                            <select id="degree_id" name="degree_id" required>
                                <option value="">Select a degree</option>

                                @foreach ($degrees as $degree)
                                    <option
                                        value="{{ $degree->id }}"
                                        @selected(old('degree_id') == $degree->id)
                                    >
                                        {{ $degree->degree_name }}
                                        — {{ $degree->course?->course_name ?? 'No course' }}
                                    </option>
                                @endforeach
                            </select>

                            <small>
                                Select the degree this batch belongs to.
                                The associated course is shown beside the degree name.
                            </small>
                        </div>

                        <div class="batch-field">
                            <label for="batch_name">Batch Name *</label>

                            <input
                                type="text"
                                id="batch_name"
                                name="batch_name"
                                value="{{ old('batch_name') }}"
                                placeholder="e.g. 24.1 or 2027 Intake"
                                maxlength="255"
                                required
                            >

                            <small>
                                Use the official batch or intake name.
                                The same batch name can be used under a different degree.
                            </small>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="batch-field">
                                    <label for="start_date">Start Date</label>

                                    <input
                                        type="date"
                                        id="start_date"
                                        name="start_date"
                                        value="{{ old('start_date') }}"
                                    >
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="batch-field">
                                    <label for="end_date">End Date</label>

                                    <input
                                        type="date"
                                        id="end_date"
                                        name="end_date"
                                        value="{{ old('end_date') }}"
                                    >

                                    <small>Must be on or after the start date.</small>
                                </div>
                            </div>
                        </div>

                        <div class="batch-form-actions">
                            <a
                                href="{{ route('batches.index') }}"
                                class="batch-btn batch-btn-secondary"
                            >
                                Cancel
                            </a>

                            <button type="submit"
                                    class="batch-btn batch-btn-primary">
                                <i class="bi bi-check-lg"></i>
                                Save Batch
                            </button>
                        </div>
                    </form>

                @endif

            </div>
        </div>
    </div>

@endsection
