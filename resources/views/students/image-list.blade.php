@extends('layouts.app')

@section('title', 'Student Image List')

@section('content')

    <div class="container-fluid">

        <div class="card">

            <div class="card-header bg-primary text-white">
                <h3 class="card-title">Student Image List</h3>
            </div>

            <div class="card-body">

                <div class="row">

                    @forelse($students as $student)

                        <div class="col-md-4 mb-4">

                            <div class="card h-100 shadow-sm">

                                @if($student->image)

                                    <img
                                        src="{{ asset('storage/' . $student->image) }}"
                                        class="card-img-top"
                                        style="height: 220px; object-fit: cover;"
                                        alt="Student Image">

                                @else

                                    <div
                                        class="d-flex align-items-center justify-content-center bg-light"
                                        style="height: 220px;">

                                    <span class="text-muted">
                                        No Image
                                    </span>

                                    </div>

                                @endif

                                <div class="card-body">

                                    <h5 class="card-title">
                                        {{ $student->name }}
                                    </h5>

                                    <p class="card-text">
                                        Reg No: {{ $student->reg_no }}
                                    </p>

                                    <p class="card-text">
                                        Email: {{ $student->email }}
                                    </p>

                                </div>

                            </div>

                        </div>

                    @empty

                        <div class="col-12 text-center">
                            <p>No students found.</p>
                        </div>

                    @endforelse

                </div>

            </div>

        </div>

    </div>

@endsection
