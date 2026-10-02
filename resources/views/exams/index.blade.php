@extends('layouts.app')

@section('title', 'Exams')

@section('content')

    <div class="container-fluid">

        <div class="row">

            <!-- Add Exam -->
            <div class="col-md-5">

                <div class="card">

                    <div class="card-header bg-success text-white">
                        <h3 class="card-title">Add Exam</h3>
                    </div>

                    <div class="card-body">

                        @if(session('success'))
                            <div class="alert alert-success">
                                {{ session('success') }}
                            </div>
                        @endif

                        @if($errors->any())
                            <div class="alert alert-danger">
                                @foreach($errors->all() as $error)
                                    <div>{{ $error }}</div>
                                @endforeach
                            </div>
                        @endif

                        <form action="{{ route('exams.store') }}" method="POST">

                            @csrf

                            <div class="mb-3">
                                <label class="form-label">Exam Name</label>

                                <input
                                    type="text"
                                    name="exam_name"
                                    class="form-control"
                                    placeholder="Mid Term Exam"
                                    required
                                >
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Subject</label>

                                <input
                                    type="text"
                                    name="subject"
                                    class="form-control"
                                    placeholder="Software Engineering"
                                    required
                                >
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Exam Date</label>

                                <input
                                    type="date"
                                    name="exam_date"
                                    class="form-control"
                                    required
                                >
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Duration</label>

                                <input
                                    type="text"
                                    name="duration"
                                    class="form-control"
                                    placeholder="2 Hours"
                                    required
                                >
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Status</label>

                                <select
                                    name="status"
                                    class="form-select"
                                    required>

                                    <option value="Scheduled">
                                        Scheduled
                                    </option>

                                    <option value="Completed">
                                        Completed
                                    </option>

                                </select>

                            </div>

                            <button
                                type="submit"
                                class="btn btn-success w-100">

                                Add Exam

                            </button>

                        </form>

                    </div>

                </div>

            </div>


            <!-- Exam List -->
            <div class="col-md-7">

                <div class="card">

                    <div class="card-header bg-primary text-white">
                        <h3 class="card-title">Exam List</h3>
                    </div>

                    <div class="card-body">

                        <div class="table-responsive">

                            <table class="table table-bordered table-striped">

                                <thead>

                                <tr>
                                    <th>Exam</th>
                                    <th>Subject</th>
                                    <th>Date</th>
                                    <th>Duration</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>

                                </thead>

                                <tbody>

                                @forelse($exams as $exam)

                                    <tr>

                                        <td>
                                            {{ $exam->exam_name }}
                                        </td>

                                        <td>
                                            {{ $exam->subject }}
                                        </td>

                                        <td>
                                            {{ $exam->exam_date }}
                                        </td>

                                        <td>
                                            {{ $exam->duration }}
                                        </td>

                                        <td>
                                            {{ $exam->status }}
                                        </td>

                                        <td>

                                            <a
                                                href="{{ route('exams.edit', $exam) }}"
                                                class="btn btn-warning btn-sm">
                                                Update
                                            </a>

                                            <form
                                                action="{{ route('exams.destroy', $exam) }}"
                                                method="POST"
                                                style="display:inline;">

                                                @csrf
                                                @method('DELETE')

                                                <button
                                                    type="submit"
                                                    class="btn btn-danger btn-sm"
                                                    onclick="return confirm('Delete this exam?')">

                                                    Delete

                                                </button>

                                            </form>

                                        </td>

                                    </tr>

                                @empty

                                    <tr>

                                        <td colspan="6"
                                            class="text-center">

                                            No exams found.

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
