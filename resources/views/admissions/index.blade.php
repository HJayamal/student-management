@extends('layouts.app')

@section('title', 'Admissions')

@section('content')

    <div class="container-fluid">

        <div class="row">

            <!-- Admission Form -->
            <div class="col-md-5">

                <div class="card">

                    <div class="card-header bg-success text-white">
                        <h3 class="card-title">Student Admission</h3>
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

                        <form action="{{ route('admissions.store') }}" method="POST">

                            @csrf

                            <div class="mb-3">
                                <label class="form-label">Admission No</label>
                                <input
                                    type="text"
                                    name="admission_no"
                                    class="form-control"
                                    required
                                >
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Student Reg No</label>
                                <input
                                    type="text"
                                    name="reg_no"
                                    class="form-control"
                                    required
                                >
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Course</label>
                                <input
                                    type="text"
                                    name="course"
                                    class="form-control"
                                    placeholder="Example: Software Engineering"
                                    required
                                >
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Admission Date</label>
                                <input
                                    type="date"
                                    name="admission_date"
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

                                    <option value="Active">Active</option>
                                    <option value="Inactive">Inactive</option>

                                </select>

                            </div>

                            <button
                                type="submit"
                                class="btn btn-success w-100">

                                Add Admission

                            </button>

                        </form>

                    </div>

                </div>

            </div>


            <!-- Admission List -->
            <div class="col-md-7">

                <div class="card">

                    <div class="card-header bg-primary text-white">
                        <h3 class="card-title">Admission List</h3>
                    </div>

                    <div class="card-body">

                        <div class="table-responsive">

                            <table class="table table-bordered table-striped">

                                <thead>

                                <tr>
                                    <th>Admission No</th>
                                    <th>Reg No</th>
                                    <th>Course</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>

                                </thead>

                                <tbody>

                                @forelse($admissions as $admission)

                                    <tr>

                                        <td>
                                            {{ $admission->admission_no }}
                                        </td>

                                        <td>
                                            {{ $admission->reg_no }}
                                        </td>

                                        <td>
                                            {{ $admission->course }}
                                        </td>

                                        <td>
                                            {{ $admission->admission_date }}
                                        </td>

                                        <td>
                                            {{ $admission->status }}
                                        </td>

                                        <td>

                                            <a
                                                href="{{ route('admissions.edit', $admission) }}"
                                                class="btn btn-warning btn-sm">

                                                Update

                                            </a>

                                            <form
                                                action="{{ route('admissions.destroy', $admission) }}"
                                                method="POST"
                                                style="display:inline;">

                                                @csrf
                                                @method('DELETE')

                                                <button
                                                    type="submit"
                                                    class="btn btn-danger btn-sm"
                                                    onclick="return confirm('Delete this admission?')">

                                                    Delete

                                                </button>

                                            </form>

                                        </td>

                                    </tr>

                                @empty

                                    <tr>

                                        <td colspan="6" class="text-center">

                                            No admissions found.

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
