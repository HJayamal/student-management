@extends('layouts.app')

@section('title', 'Students')

@section('content')

    <div class="container-fluid">

        <div class="row">

            <!-- Student Register -->
            <div class="col-md-5">

                <div class="card">

                    <div class="card-header bg-success text-white">
                        <h3 class="card-title">Student Register</h3>
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

                        <form action="{{ route('students.store') }}"
                              method="POST"
                              enctype="multipart/form-data">

                            @csrf

                            <div class="mb-3">
                                <label class="form-label">Register No</label>
                                <input type="text"
                                       name="reg_no"
                                       class="form-control"
                                       required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Full Name</label>
                                <input type="text"
                                       name="name"
                                       class="form-control"
                                       required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email"
                                       name="email"
                                       class="form-control"
                                       required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Phone No</label>
                                <input type="text"
                                       name="phone"
                                       class="form-control"
                                       required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Date of Birth</label>
                                <input type="date"
                                       name="dob"
                                       class="form-control"
                                       required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Password</label>
                                <input type="password"
                                       name="password"
                                       class="form-control"
                                       required>
                            </div>

                            <!-- Student Image -->
                            <div class="mb-3">
                                <label class="form-label">Student Image</label>

                                <input type="file"
                                       name="image"
                                       class="form-control"
                                       accept=".jpg,.jpeg,.png">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Address</label>

                                <textarea name="address"
                                          class="form-control"
                                          rows="3"
                                          required></textarea>
                            </div>

                            <button type="submit"
                                    class="btn btn-success w-100">
                                Register
                            </button>

                        </form>

                    </div>
                </div>

            </div>


            <!-- Student List -->
            <div class="col-md-7">

                <div class="card">

                    <div class="card-header bg-primary text-white">
                        <h3 class="card-title">Student List</h3>
                    </div>

                    <div class="card-body">

                        <!-- Search -->
                        <form action="{{ route('students.index') }}"
                              method="GET"
                              class="mb-3">

                            <div class="input-group">

                                <input type="text"
                                       name="search"
                                       value="{{ request('search') }}"
                                       class="form-control"
                                       placeholder="Search by Register No or Name">

                                <button type="submit"
                                        class="btn btn-primary">
                                    Search
                                </button>

                            </div>

                        </form>


                        <!-- Student Table -->
                        <div class="table-responsive">

                            <table class="table table-bordered table-striped">

                                <thead class="table-light">

                                <tr>
                                    <th>Image</th>
                                    <th>Reg No</th>
                                    <th>Name</th>
                                    <th>DOB</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Action</th>
                                </tr>

                                </thead>

                                <tbody>

                                @forelse($students as $student)

                                    <tr>

                                        <!-- Image -->
                                        <td class="text-center">

                                            @if($student->image)

                                                <img
                                                    src="{{ asset('storage/' . $student->image) }}"
                                                    alt="Student Image"
                                                    width="50"
                                                    height="50"
                                                    style="object-fit: cover; border-radius: 50%;">

                                            @else

                                                <span class="text-muted">
                                                No Image
                                            </span>

                                            @endif

                                        </td>


                                        <td>
                                            {{ $student->reg_no }}
                                        </td>

                                        <td>
                                            {{ $student->name }}
                                        </td>

                                        <td>
                                            {{ $student->dob }}
                                        </td>

                                        <td>
                                            {{ $student->email }}
                                        </td>

                                        <td>
                                            {{ $student->phone }}
                                        </td>

                                        <td>

                                            <a href="{{ route('students.edit', $student) }}"
                                               class="btn btn-warning btn-sm">
                                                Update
                                            </a>

                                            <form action="{{ route('students.destroy', $student) }}"
                                                  method="POST"
                                                  style="display:inline;">

                                                @csrf
                                                @method('DELETE')

                                                <button type="submit"
                                                        class="btn btn-danger btn-sm"
                                                        onclick="return confirm('Delete this student?')">
                                                    Delete
                                                </button>

                                            </form>

                                        </td>

                                    </tr>

                                @empty

                                    <tr>
                                        <td colspan="7"
                                            class="text-center">
                                            No students found.
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
