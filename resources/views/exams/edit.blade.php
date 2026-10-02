@extends('layouts.app')

@section('title', 'Update Exam')

@section('content')

    <div class="container">

        <div class="col-md-6 mx-auto">

            <div class="card">

                <div class="card-header bg-warning">
                    <h3 class="card-title">Update Exam</h3>
                </div>

                <div class="card-body">

                    @if($errors->any())
                        <div class="alert alert-danger">
                            @foreach($errors->all() as $error)
                                <div>{{ $error }}</div>
                            @endforeach
                        </div>
                    @endif

                    <form
                        action="{{ route('exams.update', $exam) }}"
                        method="POST">

                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label class="form-label">Exam Name</label>

                            <input
                                type="text"
                                name="exam_name"
                                value="{{ $exam->exam_name }}"
                                class="form-control"
                                required
                            >
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Subject</label>

                            <input
                                type="text"
                                name="subject"
                                value="{{ $exam->subject }}"
                                class="form-control"
                                required
                            >
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Exam Date</label>

                            <input
                                type="date"
                                name="exam_date"
                                value="{{ $exam->exam_date }}"
                                class="form-control"
                                required
                            >
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Duration</label>

                            <input
                                type="text"
                                name="duration"
                                value="{{ $exam->duration }}"
                                class="form-control"
                                required
                            >
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Status</label>

                            <select
                                name="status"
                                class="form-select"
                                required>

                                <option value="Scheduled"
                                    {{ $exam->status == 'Scheduled' ? 'selected' : '' }}>
                                    Scheduled
                                </option>

                                <option value="Completed"
                                    {{ $exam->status == 'Completed' ? 'selected' : '' }}>
                                    Completed
                                </option>

                            </select>

                        </div>

                        <button
                            type="submit"
                            class="btn btn-success">
                            Update Exam
                        </button>

                        <a
                            href="{{ route('exams.index') }}"
                            class="btn btn-secondary">
                            Back
                        </a>

                    </form>

                </div>

            </div>

        </div>

    </div>

@endsection
