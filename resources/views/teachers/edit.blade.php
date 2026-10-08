@extends('layouts.app')

@section('title', 'Update Teacher')

@section('content')

    <style>
        .teacher-edit-page {
            background: #f8fafc;
            min-height: calc(100vh - 120px);
            padding: 20px 10px 40px;
        }

        .edit-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 8px 28px rgba(15, 23, 42, 0.07);
        }

        .edit-header {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: #ffffff;
            padding: 20px 24px;
        }

        .edit-header h3 {
            margin: 0;
            font-size: 20px;
            font-weight: 700;
        }

        .edit-header small {
            color: rgba(255,255,255,0.88);
        }

        .edit-body {
            padding: 28px;
        }

        .edit-body .form-label {
            color: #334155;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .edit-body .form-control,
        .edit-body .form-select {
            border: 1px solid #dbe3ef;
            border-radius: 10px;
            padding: 11px 13px;
            min-height: 44px;
            box-shadow: none !important;
        }

        .edit-body .form-control:focus,
        .edit-body .form-select:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.12) !important;
        }

        .current-image {
            width: 90px;
            height: 90px;
            object-fit: cover;
            border-radius: 14px;
            border: 1px solid #e2e8f0;
            display: block;
        }

        .no-image {
            width: 90px;
            height: 90px;
            border-radius: 14px;
            background: #f1f5f9;
            border: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #94a3b8;
            font-size: 12px;
            text-align: center;
        }

        .update-btn {
            border: none;
            border-radius: 10px;
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: #ffffff;
            font-weight: 600;
            padding: 11px 18px;
        }

        .update-btn:hover {
            color: #ffffff;
            background: linear-gradient(135deg, #1d4ed8, #1e40af);
        }

        .back-btn {
            border-radius: 10px;
            padding: 11px 18px;
            font-weight: 600;
        }

        .edit-body .alert {
            border-radius: 10px;
            border: none;
        }

        @media (max-width: 576px) {
            .edit-body {
                padding: 18px;
            }

            .edit-header {
                padding: 16px 18px;
            }
        }
    </style>

    <div class="teacher-edit-page">

        <div class="container">

            <div class="row">

                <div class="col-lg-7 col-md-9 mx-auto">

                    <div class="edit-card">

                        <div class="edit-header">
                            <h3>
                                <i class="bi bi-person-badge-fill me-2"></i>
                                Update Teacher
                            </h3>
                            <small>Edit teacher information</small>
                        </div>

                        <div class="edit-body">

                            @if($errors->any())
                                <div class="alert alert-danger">
                                    <div class="fw-semibold mb-1">
                                        Please fix the following:
                                    </div>

                                    @foreach($errors->all() as $error)
                                        <div>{{ $error }}</div>
                                    @endforeach
                                </div>
                            @endif

                            <form
                                action="{{ route('teachers.update', $teacher) }}"
                                method="POST"
                                enctype="multipart/form-data">

                                @csrf
                                @method('PUT')

                                <div class="mb-3">
                                    <label class="form-label">Name</label>

                                    <input
                                        type="text"
                                        name="name"
                                        value="{{ old('name', $teacher->name) }}"
                                        class="form-control"
                                        required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Email</label>

                                    <input
                                        type="email"
                                        name="email"
                                        value="{{ old('email', $teacher->email) }}"
                                        class="form-control"
                                        required>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Phone</label>

                                        <input
                                            type="text"
                                            name="phone"
                                            value="{{ old('phone', $teacher->phone) }}"
                                            class="form-control"
                                            required>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Gender</label>

                                        <select
                                            name="gender"
                                            class="form-select"
                                            required>

                                            <option value="">Select Gender</option>
                                            <option value="Male" {{ old('gender', $teacher->gender) == 'Male' ? 'selected' : '' }}>Male</option>
                                            <option value="Female" {{ old('gender', $teacher->gender) == 'Female' ? 'selected' : '' }}>Female</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Subject</label>

                                    <input
                                        type="text"
                                        name="subject"
                                        value="{{ old('subject', $teacher->subject) }}"
                                        class="form-control"
                                        required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Teacher Image</label>

                                    <div class="mb-2">
                                        @if($teacher->image)
                                            <img
                                                src="{{ asset('storage/' . $teacher->image) }}"
                                                alt="Teacher Image"
                                                class="current-image">
                                        @else
                                            <div class="no-image">
                                                No Image
                                            </div>
                                        @endif
                                    </div>

                                    <input
                                        type="file"
                                        name="image"
                                        class="form-control"
                                        accept=".jpg,.jpeg,.png">

                                    <small class="text-muted">
                                        JPG, JPEG or PNG. Leave empty to keep the current image.
                                    </small>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label">Address</label>

                                    <textarea
                                        name="address"
                                        class="form-control"
                                        rows="4"
                                        required>{{ old('address', $teacher->address) }}</textarea>
                                </div>

                                <div class="d-flex gap-2 flex-wrap">
                                    <button
                                        type="submit"
                                        class="update-btn">

                                        <i class="bi bi-check2-circle me-1"></i>
                                        Update Teacher
                                    </button>

                                    <a
                                        href="{{ route('teachers.index') }}"
                                        class="btn btn-secondary back-btn">

                                        <i class="bi bi-arrow-left me-1"></i>
                                        Back
                                    </a>
                                </div>

                            </form>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

@endsection
