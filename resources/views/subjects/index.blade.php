@extends('layouts.app')

@section('title', 'Subjects')

@section('content')

    <div class="container-fluid">

        <div class="row">

            <!-- Add Subject -->
            <div class="col-md-5">

                <div class="card">

                    <div class="card-header bg-success text-white">
                        <h3 class="card-title">Add Subject</h3>
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

                        <form action="{{ route('subjects.store') }}" method="POST">

                            @csrf

                            <div class="mb-3">
                                <label class="form-label">Subject Code</label>

                                <input
                                    type="text"
                                    name="subject_code"
                                    class="form-control"
                                    placeholder="SE101"
                                    required
                                >
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Subject Name</label>

                                <input
                                    type="text"
                                    name="subject_name"
                                    class="form-control"
                                    placeholder="Software Engineering"
                                    required
                                >
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Teacher</label>

                                <input
                                    type="text"
                                    name="teacher"
                                    class="form-control"
                                    placeholder="Teacher Name"
                                    required
                                >
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Description</label>

                                <textarea
                                    name="description"
                                    class="form-control"
                                    rows="3"
                                    placeholder="Subject description"
                                ></textarea>
                            </div>

                            <button
                                type="submit"
                                class="btn btn-success w-100">
                                Add Subject
                            </button>

                        </form>

                    </div>

                </div>

            </div>


            <!-- Subject List -->
            <div class="col-md-7">

                <div class="card">

                    <div class="card-header bg-primary text-white">
                        <h3 class="card-title">Subject List</h3>
                    </div>

                    <div class="card-body">

                        <div class="table-responsive">

                            <table class="table table-bordered table-striped">

                                <thead>

                                <tr>
                                    <th>Code</th>
                                    <th>Subject</th>
                                    <th>Teacher</th>
                                    <th>Description</th>
                                    <th>Action</th>
                                </tr>

                                </thead>

                                <tbody>

                                @forelse($subjects as $subject)

                                    <tr>

                                        <td>{{ $subject->subject_code }}</td>

                                        <td>{{ $subject->subject_name }}</td>

                                        <td>{{ $subject->teacher }}</td>

                                        <td>{{ $subject->description }}</td>

                                        <td>

                                            <a
                                                href="{{ route('subjects.edit', $subject) }}"
                                                class="btn btn-warning btn-sm">
                                                Update
                                            </a>

                                            <form
                                                action="{{ route('subjects.destroy', $subject) }}"
                                                method="POST"
                                                style="display:inline;">

                                                @csrf
                                                @method('DELETE')

                                                <button
                                                    type="submit"
                                                    class="btn btn-danger btn-sm"
                                                    onclick="return confirm('Delete this subject?')">
                                                    Delete
                                                </button>

                                            </form>

                                        </td>

                                    </tr>

                                @empty

                                    <tr>
                                        <td colspan="5" class="text-center">
                                            No subjects found.
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
