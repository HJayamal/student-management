@extends('layouts.app')

@section('title', 'Batch Management')

@section('content')

    <style>
        .batch-page {
            padding: 10px 5px 30px;
            background: #f8fafc;
            min-height: calc(100vh - 120px);
        }

        .page-heading {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 25px;
        }

        .page-title {
            color: #0f172a;
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 6px;
        }

        .page-subtitle {
            color: #64748b;
            font-size: 14px;
            margin: 0;
        }

        .batch-card {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            box-shadow: 0 8px 25px rgba(15, 23, 42, .05);
            overflow: hidden;
        }

        .batch-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
            padding: 20px 24px;
            border-bottom: 1px solid #e2e8f0;
        }

        .batch-card-header h3 {
            font-size: 18px;
            font-weight: 700;
            color: #0f172a;
            margin: 0;
        }

        .batch-card-body {
            padding: 22px 24px;
        }

        .batch-table {
            width: 100%;
            border-collapse: collapse;
        }

        .batch-table th {
            padding: 14px 12px;
            background: #f8fafc;
            color: #64748b;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: .4px;
            border-bottom: 1px solid #e2e8f0;
            text-align: left;
            white-space: nowrap;
        }

        .batch-table td {
            padding: 15px 12px;
            border-bottom: 1px solid #f1f5f9;
            color: #334155;
            font-size: 14px;
            vertical-align: middle;
        }

        .batch-table tbody tr:hover {
            background: #fafbff;
        }

        .batch-name {
            font-weight: 700;
            color: #0f172a;
        }

        .batch-badge {
            display: inline-block;
            padding: 6px 10px;
            border-radius: 8px;
            background: #eef2ff;
            color: #4338ca;
            font-size: 12px;
            font-weight: 600;
        }

        .action-buttons {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .action-buttons form {
            margin: 0;
        }

        .btn-add-batch,
        .btn-edit-batch,
        .btn-delete-batch,
        .btn-back {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 9px 13px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            border: 0;
            cursor: pointer;
        }

        .btn-add-batch {
            background: #4f46e5;
            color: white;
        }

        .btn-add-batch:hover {
            background: #4338ca;
            color: white;
        }

        .btn-back {
            background: #e2e8f0;
            color: #334155;
        }

        .btn-back:hover {
            background: #cbd5e1;
            color: #0f172a;
        }

        .btn-edit-batch {
            background: #e0f2fe;
            color: #0369a1;
        }

        .btn-edit-batch:hover {
            background: #bae6fd;
            color: #075985;
        }

        .btn-delete-batch {
            background: #fee2e2;
            color: #b91c1c;
        }

        .btn-delete-batch:hover {
            background: #fecaca;
            color: #991b1b;
        }

        .alert-message {
            padding: 14px 18px;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        .empty-state {
            text-align: center;
            padding: 45px 20px;
            color: #64748b;
        }

        .empty-state i {
            font-size: 36px;
            color: #94a3b8;
            display: block;
            margin-bottom: 12px;
        }

        .empty-state h4 {
            color: #334155;
            font-weight: 700;
            font-size: 17px;
        }

        .pagination-container {
            margin-top: 22px;
        }

        @media (max-width: 768px) {
            .page-title {
                font-size: 23px;
            }

            .batch-card-body {
                padding: 12px;
            }

            .batch-table th,
            .batch-table td {
                padding: 12px 10px;
            }
        }
    </style>

    <div class="batch-page">

        <div class="page-heading">
            <div>
                <h1 class="page-title">
                    <i class="bi bi-layers me-2" style="color:#4f46e5;"></i>
                    Batch Management
                </h1>

                <p class="page-subtitle">
                    Manage campus batches and their associated degrees.
                </p>
            </div>

            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ route('degrees.index') }}" class="btn-back">
                    <i class="bi bi-mortarboard"></i>
                    Degrees
                </a>

                <a href="{{ route('batches.create') }}" class="btn-add-batch">
                    <i class="bi bi-plus-lg"></i>
                    Add Batch
                </a>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-message" role="status">
                <i class="bi bi-check-circle me-2"></i>
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-message" role="alert">
                <i class="bi bi-exclamation-circle me-2"></i>
                {{ session('error') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger alert-message" role="alert">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <div class="batch-card">

            <div class="batch-card-header">
                <div>
                    <h3>Registered Batches</h3>
                    <p class="page-subtitle mt-1">
                        View and manage batches registered under degrees.
                    </p>
                </div>

                <span class="batch-badge">
                {{ $batches->total() }} Batches
            </span>
            </div>

            <div class="batch-card-body">

                <div class="table-responsive">
                    <table class="batch-table">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>Batch</th>
                            <th>Course</th>
                            <th>Degree</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Actions</th>
                        </tr>
                        </thead>

                        <tbody>
                        @forelse ($batches as $batch)
                            <tr>
                                <td>
                                    {{ $batches->firstItem() + $loop->index }}
                                </td>

                                <td>
                                <span class="batch-name">
                                    {{ $batch->batch_name }}
                                </span>
                                </td>

                                <td>
                                    {{ $batch->degree?->course?->course_name ?? '—' }}
                                </td>

                                <td>
                                    {{ $batch->degree?->degree_name ?? '—' }}
                                </td>

                                <td>
                                    {{ $batch->start_date
                                        ? \Illuminate\Support\Carbon::parse($batch->start_date)->format('d M Y')
                                        : '—' }}
                                </td>

                                <td>
                                    {{ $batch->end_date
                                        ? \Illuminate\Support\Carbon::parse($batch->end_date)->format('d M Y')
                                        : '—' }}
                                </td>

                                <td>
                                    <div class="action-buttons">
                                        <a
                                            href="{{ route('batches.edit', $batch) }}"
                                            class="btn-edit-batch"
                                        >
                                            <i class="bi bi-pencil-square"></i>
                                            Edit
                                        </a>

                                        <form
                                            action="{{ route('batches.destroy', $batch) }}"
                                            method="POST"
                                            onsubmit="return confirm('Are you sure you want to delete this batch?');"
                                        >
                                            @csrf
                                            @method('DELETE')

                                            <button
                                                type="submit"
                                                class="btn-delete-batch"
                                            >
                                                <i class="bi bi-trash"></i>
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">
                                    <div class="empty-state">
                                        <i class="bi bi-layers"></i>
                                        <h4>No batches registered yet</h4>
                                        <p>
                                            Create a batch to organise students
                                            under the correct degree.
                                        </p>

                                        <a
                                            href="{{ route('batches.create') }}"
                                            class="btn-add-batch"
                                        >
                                            <i class="bi bi-plus-lg"></i>
                                            Add First Batch
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="pagination-container">
                    {{ $batches->links() }}
                </div>

            </div>
        </div>

    </div>

@endsection
