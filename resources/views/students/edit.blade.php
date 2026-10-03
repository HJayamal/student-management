@extends('layouts.app')

@section('title', 'Update Student')

@section('content')

    <div class="container-fluid">

        <div class="row justify-content-center">

            <div class="col-md-6">

                <div class="card">

                    <div class="card-header bg-warning">
                        <h3 class="card-title">Update Student</h3>
                    </div>

                    <div class="card-body">

                        @if($errors->any())

                            <div class="alert alert-danger">

                                @foreach($errors->all() as $error)
                                    <div>{{ $error }}</div>
                                @endforeach

                            </div>

                        @endif


                        <form action="{{ route('students.update', $student) }}"
                              method="POST"
                              enctype="multipart/form-data">

                            @csrf
                            @method('PUT')


                            <!-- Register No -->
                            <div class="mb-3">

                                <label class="form-label">
                                    Register No
                                </label>

                                <input
                                    type="text"
                                    name="reg_no"
                                    value="{{ $student->reg_no }}"
                                    class="form-control"
                                    required
                                >

                            </div>


                            <!-- Full Name -->
                            <div class="mb-3">

                                <label class="form-label">
                                    Full Name
                                </label>

                                <input
                                    type="text"
                                    name="name"
                                    value="{{ $student->name }}"
                                    class="form-control"
                                    required
                                >

                            </div>


                            <!-- Email -->
                            <div class="mb-3">

                                <label class="form-label">
                                    Email
                                </label>

                                <input
                                    type="email"
                                    name="email"
                                    value="{{ $student->email }}"
                                    class="form-control"
                                    required
                                >

                            </div>


                            <!-- Phone -->
                            <div class="mb-3">

                                <label class="form-label">
                                    Phone No
                                </label>

                                <input
                                    type="text"
                                    name="phone"
                                    value="{{ $student->phone }}"
                                    class="form-control"
                                    required
                                >

                            </div>


                            <!-- Date of Birth -->
                            <div class="mb-3">

                                <label class="form-label">
                                    Date of Birth
                                </label>

                                <input
                                    type="date"
                                    name="dob"
                                    value="{{ $student->dob }}"
                                    class="form-control"
                                    required
                                >

                            </div>


                            <!-- Password -->
                            <div class="mb-3">

                                <label class="form-label">
                                    New Password
                                </label>

                                <input
                                    type="password"
                                    name="password"
                                    class="form-control"
                                    placeholder="Leave empty to keep old password"
                                >

                            </div>


                            <!-- Student Image -->
                            <div class="mb-3">

                                <label class="form-label">
                                    Student Image
                                </label>

                                @if($student->image)

                                    <div class="mb-2">

                                        <img
                                            src="{{ asset('storage/' . $student->image) }}"
                                            width="120"
                                            height="120"
                                            style="object-fit: cover; border-radius: 10px;"
                                            alt="Student Image">

                                    </div>

                                @else

                                    <p class="text-muted">
                                        No image uploaded
                                    </p>

                                @endif

                                <input
                                    type="file"
                                    name="image"
                                    class="form-control"
                                    accept=".jpg,.jpeg,.png"
                                >

                            </div>


                            <!-- Address -->
                            <div class="mb-3">

                                <label class="form-label">
                                    Address
                                </label>

                                <textarea
                                    name="address"
                                    class="form-control"
                                    rows="3"
                                    required>{{ $student->address }}</textarea>

                            </div>


                            <!-- Buttons -->
                            <button
                                type="submit"
                                class="btn btn-success">

                                Update Student

                            </button>

                            <a
                                href="{{ route('students.index') }}"
                                class="btn btn-secondary">

                                Back

                            </a>

                        </form>

                    </div>

                </div>

            </div>

        </div>

    </div>

@endsection
