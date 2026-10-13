@extends('layouts.app')

@section('title', 'Livewire Student Management')

@section('content')

    <div class="container-fluid py-4">

        <div class="mb-4">
            <h2>Livewire Student Management</h2>
            <p class="text-muted">
                Student registration and student list
            </p>
        </div>

        <div class="row g-4">

            <div class="col-12">
                <livewire:student-reg-form />
            </div>

            <div class="col-12">
                <livewire:student-list-table />
            </div>

        </div>

    </div>

@endsection
