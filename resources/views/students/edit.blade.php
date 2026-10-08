@extends('layouts.app')

@section('title', 'Update Student')

@section('content')

    <style>
        .student-edit-page {
            background: #f8fafc;
            min-height: calc(100vh - 120px);
            padding: 20px 5px 35px;
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
            padding: 18px 22px;
        }

        .edit-header h3 {
            margin: 0;
            font-size: 20px;
            font-weight: 700;
        }

        .edit-header small {
            color: rgba(255, 255, 255, 0.85);
        }

        .edit-body {
            padding: 26px;
        }

        .student-edit-page .form-label {
            color: #334155;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .student-edit-page .form-control,
        .student-edit-page .form-select {
            min-height: 44px;
            border: 1px solid #dbe3ef;
            border-radius: 10px;
            background: #ffffff;
            color: #1e293b;
            box-shadow: none;
        }

        .student-edit-page .form-control:focus,
        .student-edit-page .form-select:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.12);
        }

        .student-edit-page textarea.form-control {
            min-height: 95px;
            resize: vertical;
        }

        .student-edit-page input[type="file"] {
            padding: 9px 12px;
        }

        .current-image-box {
            margin-bottom: 12px;
        }

        .current-image {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 14px;
            border: 3px solid #eef2ff;
        }

        .no-image-box {
            width: 120px;
            height: 120px;
            border-radius: 14px;
            background: #f1f5f9;
            border: 1px solid #e2e8f0;
            color: #94a3b8;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            font-weight: 600;
        }

        .edit-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 8px;
        }

        .update-student-btn {
            border: none;
            border-radius: 10px;
            background: linear-gradient(135deg, #10b981, #059669);
            color: #ffffff;
            font-weight: 600;
            padding: 11px 18px;
        }

        .update-student-btn:hover {
            background: linear-gradient(135deg, #059669, #047857);
            color: #ffffff;
        }

        .back-btn {
            border: none;
            border-radius: 10px;
            background: #e2e8f0;
            color: #334155;
            font-weight: 600;
            padding: 11px 18px;
            text-decoration: none;
        }

        .back-btn:hover {
            background: #cbd5e1;
            color: #1e293b;
        }

        .student-edit-page .alert {
            border-radius: 10px;
            border: none;
        }

        .student-edit-page .alert-danger {
            background: #fef2f2;
            color: #b91c1c;
        }

        @media (max-width: 576px) {
            .edit-body {
                padding: 18px;
            }

            .edit-header {
                padding: 15px 18px;
            }
        }
    </style>

    <div class="student-edit-page">

        <div class="row justify-content-center">

            <div class="col-lg-7 col-md-9">

                <div class="edit-card">

                    <div class="edit-header">
                        <h3>
                            <i class="bi bi-pencil-square me-2"></i>
                            Update Student
                        </h3>

                        <small>
                            Edit student information
                        </small>
                    </div>

                    <div class="edit-body">

                        @if($errors->any())
                            <div class="alert alert-danger mb-4">
                                <div class="fw-semibold mb-1">Please fix the following:</div>

                                @foreach($errors->all() as $error)
                                    <div>
                                        <i class="bi bi-exclamation-circle me-1"></i>
                                        {{ $error }}
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <form
                            action="{{ route('students.update', $student) }}"
                            method="POST"
                            enctype="multipart/form-data">

                            @csrf
                            @method('PUT')

                            <!-- Register No -->
                            <div class="mb-3">
                                <label class="form-label">Register No</label>

                                <input
                                    type="text"
                                    name="reg_no"
                                    value="{{ old('reg_no', $student->reg_no) }}"
                                    class="form-control"
                                    required
                                >
                            </div>

                            <div class="row">

                                <!-- First Name -->
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">First Name</label>

                                    <input
                                        type="text"
                                        name="first_name"
                                        value="{{ old('first_name', $student->first_name) }}"
                                        class="form-control"
                                        required
                                    >
                                </div>

                                <!-- Last Name -->
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Last Name</label>

                                    <input
                                        type="text"
                                        name="last_name"
                                        value="{{ old('last_name', $student->last_name) }}"
                                        class="form-control"
                                        required
                                    >
                                </div>

                            </div>

                            <!-- Gender -->
                            <div class="mb-3">
                                <label class="form-label">Gender</label>

                                <select name="gender" class="form-select" required>
                                    <option value="">Select Gender</option>
                                    <option value="Male"
                                        {{ old('gender', $student->gender) == 'Male' ? 'selected' : '' }}>
                                        Male
                                    </option>
                                    <option value="Female"
                                        {{ old('gender', $student->gender) == 'Female' ? 'selected' : '' }}>
                                        Female
                                    </option>
                                </select>
                            </div>

                            <!-- NIC -->
                            <div class="mb-3">
                                <label class="form-label">NIC</label>

                                <input
                                    type="text"
                                    name="nic"
                                    value="{{ old('nic', $student->nic) }}"
                                    class="form-control"
                                    placeholder="Enter NIC number"
                                    required
                                >
                            </div>

                            <!-- Email -->
                            <div class="mb-3">
                                <label class="form-label">Email</label>

                                <input
                                    type="email"
                                    name="email"
                                    value="{{ old('email', $student->email) }}"
                                    class="form-control"
                                    required
                                >
                            </div>

                            <!-- Phone -->
                            <div class="mb-3">
                                <label class="form-label">Phone No</label>

                                <input
                                    type="text"
                                    name="phone"
                                    value="{{ old('phone', $student->phone) }}"
                                    class="form-control"
                                    required
                                >
                            </div>

                            <!-- Date of Birth -->
                            <div class="mb-3">
                                <label class="form-label">Date of Birth</label>

                                <input
                                    type="date"
                                    name="dob"
                                    value="{{ old('dob', $student->dob) }}"
                                    class="form-control"
                                    required
                                >
                            </div>

                            <!-- Password -->
                            <div class="mb-3">
                                <label class="form-label">New Password</label>

                                <input
                                    type="password"
                                    name="password"
                                    class="form-control"
                                    placeholder="Leave empty to keep old password"
                                >

                                <small class="text-muted">
                                    Leave this empty if you do not want to change the password.
                                </small>
                            </div>

                            <!-- Student Image -->
                            <div class="mb-3">

                                <label class="form-label">Student Image</label>

                                <div class="current-image-box">

                                    @if($student->image)

                                        <img
                                            src="{{ asset('storage/' . $student->image) }}"
                                            class="current-image"
                                            alt="Current Student Image"
                                        >

                                    @else

                                        <div class="no-image-box">
                                            No Image
                                        </div>

                                    @endif

                                </div>

                                <input
                                    type="file"
                                    name="image"
                                    class="form-control"
                                    accept=".jpg,.jpeg,.png"
                                >

                                <small class="text-muted">
                                    JPG, JPEG or PNG (Max 2MB)
                                </small>

                            </div>

                            <!-- Address -->
                            <div class="mb-4">
                                <label class="form-label">Address</label>

                                <textarea
                                    name="address"
                                    class="form-control"
                                    rows="3"
                                    required
                                >{{ old('address', $student->address) }}</textarea>
                            </div>

                            <!-- Buttons -->
                            <div class="edit-actions">

                                <button
                                    type="submit"
                                    class="update-student-btn">

                                    <i class="bi bi-check-circle me-1"></i>
                                    Update Student

                                </button>

                                <a
                                    href="{{ route('students.index') }}"
                                    class="back-btn">

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

@endsection
