@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')

    <div class="container-fluid">

        <h2 class="mb-4">Dashboard</h2>

        <div class="row">

            <!-- Students -->
            <div class="col-md-4 mb-3">

                <div class="small-box text-bg-primary">

                    <div class="inner">

                        <h3>{{ $students }}</h3>

                        <p>Total Students</p>

                    </div>

                    <div class="icon">
                        <i class="bi bi-people"></i>
                    </div>

                    <a href="{{ route('students.index') }}"
                       class="small-box-footer">

                        View Students
                        <i class="bi bi-arrow-right"></i>

                    </a>

                </div>

            </div>


            <!-- Admissions -->
            <div class="col-md-4 mb-3">

                <div class="small-box text-bg-success">

                    <div class="inner">

                        <h3>{{ $admissions }}</h3>

                        <p>Total Admissions</p>

                    </div>

                    <div class="icon">
                        <i class="bi bi-person-plus"></i>
                    </div>

                    <a href="{{ route('admissions.index') }}"
                       class="small-box-footer">

                        View Admissions
                        <i class="bi bi-arrow-right"></i>

                    </a>

                </div>

            </div>


            <!-- Teachers -->
            <div class="col-md-4 mb-3">

                <div class="small-box text-bg-warning">

                    <div class="inner">

                        <h3>{{ $teachers }}</h3>

                        <p>Total Teachers</p>

                    </div>

                    <div class="icon">
                        <i class="bi bi-person-badge"></i>
                    </div>

                    <a href="{{ route('teachers.index') }}"
                       class="small-box-footer">

                        View Teachers
                        <i class="bi bi-arrow-right"></i>

                    </a>

                </div>

            </div>


            <!-- Subjects -->
            <div class="col-md-4 mb-3">

                <div class="small-box text-bg-info">

                    <div class="inner">

                        <h3>{{ $subjects }}</h3>

                        <p>Total Subjects</p>

                    </div>

                    <div class="icon">
                        <i class="bi bi-book"></i>
                    </div>

                    <a href="{{ route('subjects.index') }}"
                       class="small-box-footer">

                        View Subjects
                        <i class="bi bi-arrow-right"></i>

                    </a>

                </div>

            </div>


            <!-- Exams -->
            <div class="col-md-4 mb-3">

                <div class="small-box text-bg-danger">

                    <div class="inner">

                        <h3>{{ $exams }}</h3>

                        <p>Total Exams</p>

                    </div>

                    <div class="icon">
                        <i class="bi bi-pencil-square"></i>
                    </div>

                    <a href="{{ route('exams.index') }}"
                       class="small-box-footer">

                        View Exams
                        <i class="bi bi-arrow-right"></i>

                    </a>

                </div>

            </div>

        </div>

    </div>

@endsection
