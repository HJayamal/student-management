@extends('layouts.app')

@section('title', 'Update Subject')

@section('content')

    <div class="container">

        <div class="col-md-6 mx-auto">

            <div class="card">

                <div class="card-header bg-warning">
                    <h3 class="card-title">Update Subject</h3>
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
                        action="{{ route('subjects.update', $subject) }}"
                        method="POST">

                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label class="form-label">Subject Code</label>

                            <input
                                type="text"
                                name="subject_code"
                                value="{{ $subject->subject_code }}"
                                class="form-control"
                                required
                            >
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Subject Name</label>

                            <input
                                type="text"
                                name="subject_name"
                                value="{{ $subject->subject_name }}"
                                class="form-control"
                                required
                            >
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Teacher</label>

                            <input
                                type="text"
                                name="teacher"
                                value="{{ $subject->teacher }}"
                                class="form-control"
                                required
                            >
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Description</label>

                            <textarea
                                name="description"
                                class="form-control"
                                rows="3"
                            >{{ $subject->description }}</textarea>
                        </div>

                        <button
                            type="submit"
                            class="btn btn-success">
                            Update Subject
                        </button>

                        <a
                            href="{{ route('subjects.index') }}"
                            class="btn btn-secondary">
                            Back
                        </a>

                    </form>

                </div>

            </div>

        </div>

    </div>

@endsection
