@extends('layouts.app')

@section('title', 'Teachers')

@section('content')

    <div class="container-fluid">

        <div class="row">

            <!-- Teacher Form -->
            <div class="col-md-5">

                <div class="card">

                    <div class="card-header bg-success text-white">
                        <h3 class="card-title">Add Teacher</h3>
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

                        <form action="{{ route('teachers.store') }}" method="POST">

                            @csrf

                            <div class="mb-3">
                                <label class="form-label">Name</label>

                                <input
                                    type="text"
                                    name="name"
                                    class="form-control"
                                    required
                                >
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Email</label>

                                <input
                                    type="email"
                                    name="email"
                                    class="form-control"
                                    required
                                >
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Phone</label>

                                <input
                                    type="text"
                                    name="phone"
                                    class="form-control"
                                    required
                                >
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Subject</label>

                                <input
                                    type="text"
                                    name="subject"
                                    class="form-control"
                                    required
                                >
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Address</label>

                                <textarea
                                    name="address"
                                    class="form-control"
                                    rows="3"
                                    required
                                ></textarea>
                            </div>

                            <button
                                type="submit"
                                class="btn btn-success w-100">
                                Add Teacher
                            </button>

                        </form>

                    </div>

                </div>

            </div>


            <!-- Teacher List -->
            <div class="col-md-7">

                <div class="card">

                    <!-- Teacher List Header -->
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">

                        <h3 class="card-title mb-0">
                            Teacher List
                        </h3>

                        <!-- Export PDF Button -->
                        <a
                            href="{{ route('teachers.export-pdf') }}"
                            class="btn btn-danger btn-sm">

                            <i class="bi bi-file-earmark-pdf"></i>
                            Export PDF

                        </a>

                    </div>


                    <div class="card-body">

                        <div class="table-responsive">

                            <table class="table table-bordered table-striped">

                                <thead>

                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Subject</th>
                                    <th>Action</th>
                                </tr>

                                </thead>

                                <tbody>

                                @forelse($teachers as $teacher)

                                    <tr>

                                        <td>
                                            {{ $teacher->name }}
                                        </td>

                                        <td>
                                            {{ $teacher->email }}
                                        </td>

                                        <td>
                                            {{ $teacher->phone }}
                                        </td>

                                        <td>
                                            {{ $teacher->subject }}
                                        </td>

                                        <td>

                                            <a
                                                href="{{ route('teachers.edit', $teacher) }}"
                                                class="btn btn-warning btn-sm">

                                                <i class="bi bi-pencil-square"></i>
                                                Update

                                            </a>

                                            <form
                                                action="{{ route('teachers.destroy', $teacher) }}"
                                                method="POST"
                                                style="display:inline;">

                                                @csrf

                                                @method('DELETE')

                                                <button
                                                    type="submit"
                                                    class="btn btn-danger btn-sm"
                                                    onclick="return confirm('Delete this teacher?')">

                                                    <i class="bi bi-trash"></i>
                                                    Delete

                                                </button>

                                            </form>

                                        </td>

                                    </tr>

                                @empty

                                    <tr>

                                        <td
                                            colspan="5"
                                            class="text-center">

                                            No teachers found.

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
