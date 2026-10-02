@extends('layouts.app')

@section('title', 'Update Teacher')

@section('content')

    <div class="container">

        <div class="row">

            <div class="col-md-6 mx-auto">

                <div class="card">

                    <div class="card-header bg-warning">
                        <h3 class="card-title">Update Teacher</h3>
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
                            action="{{ route('teachers.update', $teacher) }}"
                            method="POST">

                            @csrf
                            @method('PUT')

                            <div class="mb-3">
                                <label class="form-label">Name</label>

                                <input
                                    type="text"
                                    name="name"
                                    value="{{ $teacher->name }}"
                                    class="form-control"
                                    required
                                >
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Email</label>

                                <input
                                    type="email"
                                    name="email"
                                    value="{{ $teacher->email }}"
                                    class="form-control"
                                    required
                                >
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Phone</label>

                                <input
                                    type="text"
                                    name="phone"
                                    value="{{ $teacher->phone }}"
                                    class="form-control"
                                    required
                                >
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Subject</label>

                                <input
                                    type="text"
                                    name="subject"
                                    value="{{ $teacher->subject }}"
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
                                >{{ $teacher->address }}</textarea>
                            </div>

                            <button
                                type="submit"
                                class="btn btn-success">
                                Update Teacher
                            </button>

                            <a
                                href="{{ route('teachers.index') }}"
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
