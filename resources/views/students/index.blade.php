@extends('layouts.app')

@section('title', 'Students')

@section('content')

    <style>


        .student-page {
            background: #f8fafc;
            min-height: calc(100vh - 120px);
            padding: 10px 5px 30px;
        }


        .page-title {
            color: #0f172a !important;
            font-size: 30px;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .page-subtitle {
            color: #64748b !important;
            font-size: 14px;
            margin-bottom: 28px;
        }

        /* =========================================
           CARDS
           ========================================= */

        .modern-card {
            background: #ffffff !important;
            border: 1px solid #e2e8f0 !important;
            border-radius: 18px !important;
            overflow: hidden;
            box-shadow: 0 8px 28px rgba(15, 23, 42, 0.07) !important;
        }

        .modern-card-header {
            min-height: 72px;
            padding: 16px 20px;
            color: #ffffff !important;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .register-header {
            background: linear-gradient(135deg, #10b981, #059669) !important;
        }

        .list-header {
            background: linear-gradient(135deg, #4f46e5, #6366f1) !important;
        }

        .modern-card-title {
            color: #ffffff !important;
            margin: 0;
            font-size: 18px;
            font-weight: 700;
        }

        .modern-card-header small {
            color: rgba(255, 255, 255, 0.85) !important;
        }

        .modern-card-body {
            background: #ffffff !important;
            padding: 24px;
        }



        .student-page .form-label {
            color: #334155 !important;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .student-page .form-control {
            background-color: #ffffff !important;
            color: #1e293b !important;
            border: 1px solid #dbe3ef !important;
            border-radius: 10px !important;
            padding: 11px 13px !important;
            min-height: 44px;
            box-shadow: none !important;
            transition: all 0.2s ease;
        }

        .student-page .form-control:focus {
            background-color: #ffffff !important;
            color: #1e293b !important;
            border-color: #6366f1 !important;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.12) !important;
        }

        .student-page textarea.form-control {
            min-height: 95px;
            resize: vertical;
        }

        .student-page .form-select {
            background-color: #ffffff !important;
            color: #1e293b !important;
            border: 1px solid #dbe3ef !important;
            border-radius: 10px !important;
            padding: 11px 13px !important;
            min-height: 44px;
            box-shadow: none !important;
        }

        .student-page .form-select:focus {
            background-color: #ffffff !important;
            color: #1e293b !important;
            border-color: #6366f1 !important;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.12) !important;
        }

        .student-page .form-control::placeholder {
            color: #94a3b8 !important;
            opacity: 1;
        }

        .student-page input[type="date"] {
            color: #1e293b !important;
        }

        .student-page input[type="file"] {
            padding: 9px 12px !important;
        }

        .student-page input[type="file"]::file-selector-button {
            border: none;
            background: #eef2ff;
            color: #4338ca;
            padding: 7px 12px;
            margin-right: 10px;
            border-radius: 7px;
            font-weight: 600;
            cursor: pointer;
        }

        .student-page .text-muted {
            color: #94a3b8 !important;
        }



        .register-btn {
            width: 100%;
            border: none !important;
            border-radius: 10px !important;
            background: linear-gradient(135deg, #10b981, #059669) !important;
            color: #ffffff !important;
            font-weight: 600;
            padding: 12px 16px;
            box-shadow: 0 5px 14px rgba(16, 185, 129, 0.18);
            transition: all 0.2s ease;
        }

        .register-btn:hover {
            color: #ffffff !important;
            transform: translateY(-1px);
            box-shadow: 0 7px 18px rgba(16, 185, 129, 0.22);
        }


        .student-page .alert {
            border-radius: 10px !important;
            border: none !important;
        }

        .student-page .alert-success {
            background: #ecfdf5 !important;
            color: #047857 !important;
        }

        .student-page .alert-danger {
            background: #fef2f2 !important;
            color: #b91c1c !important;
        }

        /* =========================================
           PDF BUTTON
           ========================================= */

        .import-form {
            display: inline-flex !important;
            align-items: center !important;
            margin: 0 !important;
            padding: 0 !important;
            flex: 0 0 auto !important;
        }

        .pdf-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #ffffff !important;
            color: #dc2626 !important;
            border: none !important;
            border-radius: 9px !important;
            padding: 9px 14px;
            font-size: 13px;
            font-weight: 600;
            white-space: nowrap;
            text-decoration: none !important;
            transition: all 0.2s ease;
        }
        .import-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #ffffff !important;
            color: #2563eb !important;
            border: none !important;
            border-radius: 9px !important;
            padding: 9px 14px;
            font-size: 13px;
            font-weight: 600;
            white-space: nowrap;
            cursor: pointer;
            margin-right: 6px;
            text-decoration: none !important;
            transition: all 0.2s ease;
        }

        .import-btn:hover {
            background: #eff6ff !important;
            color: #1d4ed8 !important;
            transform: translateY(-1px);
        }

        .header-actions {
            display: flex !important;
            flex-direction: row !important;
            align-items: center !important;
            justify-content: flex-end !important;
            gap: 8px !important;
            flex-wrap: nowrap !important;
            white-space: nowrap !important;
            width: auto !important;
            min-width: max-content !important;
        }
        .pdf-btn:hover {
            background: #fef2f2 !important;
            color: #b91c1c !important;
            transform: translateY(-1px);
        }



        .search-box {
            background: #f8fafc !important;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 12px;
            margin-bottom: 20px;
        }

        .search-box .form-control {
            background: #ffffff !important;
            color: #1e293b !important;
        }

        .search-box .btn-primary {
            background: #4f46e5 !important;
            border-color: #4f46e5 !important;
            border-radius: 9px !important;
            font-weight: 600;
            padding-left: 16px;
            padding-right: 16px;
        }

        .search-box .btn-primary:hover {
            background: #4338ca !important;
            border-color: #4338ca !important;
        }



        .student-table {
            background: #ffffff !important;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            overflow-x: auto;
            overflow-y: hidden;
            position: relative;
        }

        .student-table table {
            width: 100%;
            min-width: 1000px;
            margin-bottom: 0 !important;
            background: #ffffff !important;
        }

        .student-table thead th {
            background: #f8fafc !important;
            color: #334155 !important;
            border-bottom: 1px solid #e2e8f0 !important;
            border-top: none !important;
            font-size: 13px;
            font-weight: 700;
            padding: 14px 12px !important;
            white-space: nowrap;
        }

        .student-table tbody tr {
            background: #ffffff !important;
        }

        .student-table tbody td {
            background: #ffffff !important;
            color: #334155 !important;
            border-color: #e2e8f0 !important;
            padding: 12px !important;
            vertical-align: middle;
            font-size: 13px;
        }

        .student-table tbody tr:hover td {
            background: #f8faff !important;
        }

        /* =========================================
           STUDENT IMAGE
           ========================================= */

        .student-avatar {
            width: 46px;
            height: 46px;
            object-fit: cover;
            border-radius: 50%;
            border: 3px solid #eef2ff;
        }

        .no-image {
            width: 46px;
            height: 46px;
            border-radius: 50%;
            background: #f1f5f9 !important;
            color: #94a3b8 !important;
            font-size: 11px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: auto;
            border: 1px solid #e2e8f0;
        }

        /* =========================================
           ACTION BUTTONS
           ========================================= */

        .action-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: none !important;
            border-radius: 8px !important;
            font-size: 12px;
            font-weight: 600;
            padding: 8px 11px;
            margin-right: 4px;
            margin-bottom: 4px;
            white-space: nowrap;
            min-width: 78px;
            text-decoration: none !important;
        }

        .action-btn.btn-warning {
            background: #2563eb !important;
            color: #ffffff !important;
        }

        .action-btn.btn-warning:hover {
            background: #1d4ed8 !important;
            color: #ffffff !important;
        }

        .action-btn.btn-danger {
            background: #ef4444 !important;
            color: #ffffff !important;
        }

        .action-btn.btn-danger:hover {
            background: #dc2626 !important;
            color: #ffffff !important;
        }

        .action-column {
            min-width: 135px;
            width: 135px;
            position: sticky;
            right: 0;
            z-index: 4;
            background: #f8fafc !important;
            text-align: center;
            box-shadow: -6px 0 12px rgba(15, 23, 42, 0.05);
        }

        .action-cell {
            min-width: 135px;
            width: 135px;
            position: sticky;
            right: 0;
            z-index: 3;
            background: #ffffff !important;
            text-align: center;
            white-space: normal;
            box-shadow: -6px 0 12px rgba(15, 23, 42, 0.05);
        }

        .action-cell .action-btn {
            width: 108px;
            margin: 2px auto !important;
        }

        .action-cell form {
            display: block !important;
            margin: 0 !important;
        }

        /* =========================================
           NO DATA
           ========================================= */

        .empty-state {
            padding: 45px 20px !important;
            color: #64748b !important;
        }

        .empty-state i {
            color: #cbd5e1 !important;
        }

        .empty-state-title {
            color: #334155 !important;
            font-weight: 600;
        }

        /* =========================================
           RESPONSIVE
           ========================================= */

        @media (max-width: 992px) {

            .student-page {
                padding: 5px 0 25px;
            }

            .modern-card {
                margin-bottom: 20px;
            }

        }

        @media (max-width: 576px) {

            .page-title {
                font-size: 24px;
            }

            .modern-card-body {
                padding: 16px;
            }

            .modern-card-header {
                padding: 14px 16px;
            }

            .modern-card-title {
                font-size: 16px;
            }


            .header-actions {
                display: flex !important;
                flex-direction: row !important;
                flex-wrap: nowrap !important;
                align-items: center !important;
                gap: 4px !important;
            }

            .import-form {
                display: inline-flex !important;
            }

            .pdf-btn,
            .import-btn {
                padding: 7px 9px;
                font-size: 12px;
            }

            .header-actions {
                gap: 3px;
            }

            .student-table tbody td,
            .student-table thead th {
                font-size: 12px;
                padding: 9px !important;
            }


            .action-column,
            .action-cell {
                min-width: 125px;
                width: 125px;
            }

            .action-cell .action-btn {
                width: 100px;
                font-size: 11px;
                padding: 7px 8px;
            }

        }

        /* =========================================
           DATATABLES
           ========================================= */

        .dataTables_wrapper {
            color: #475569 !important;
            font-size: 13px;
            padding-top: 4px;
        }

        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_info,
        .dataTables_wrapper .dataTables_paginate {
            color: #64748b !important;
        }

        .dataTables_wrapper .dataTables_length {
            margin-bottom: 12px;
        }

        .dataTables_wrapper .dataTables_length label {
            color: #475569 !important;
            font-weight: 600;
        }

        .dataTables_wrapper .dataTables_length select {
            border: 1px solid #dbe3ef !important;
            border-radius: 8px !important;
            background: #ffffff !important;
            color: #334155 !important;
            padding: 5px 28px 5px 8px !important;
            margin: 0 5px;
            outline: none !important;
            box-shadow: none !important;
        }

        .dataTables_wrapper .dataTables_info {
            padding-top: 14px !important;
            font-size: 12px;
        }

        .dataTables_wrapper .dataTables_paginate {
            padding-top: 10px !important;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button {
            border: 1px solid #e2e8f0 !important;
            border-radius: 7px !important;
            background: #ffffff !important;
            color: #475569 !important;
            padding: 6px 10px !important;
            margin-left: 4px !important;
            font-size: 12px;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background: #eef2ff !important;
            color: #4338ca !important;
            border-color: #c7d2fe !important;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: #4f46e5 !important;
            color: #ffffff !important;
            border-color: #4f46e5 !important;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
            background: #4338ca !important;
            color: #ffffff !important;
            border-color: #4338ca !important;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button.disabled {
            color: #cbd5e1 !important;
            background: #f8fafc !important;
            border-color: #e2e8f0 !important;
        }

        table.dataTable {
            border-collapse: separate !important;
            border-spacing: 0;
        }

        table.dataTable thead th {
            color: #334155 !important;
        }

        table.dataTable thead .sorting,
        table.dataTable thead .sorting_asc,
        table.dataTable thead .sorting_desc {
            background-position: center right;
        }

        @media (max-width: 576px) {
            .dataTables_wrapper .dataTables_length {
                margin-bottom: 8px;
            }

            .dataTables_wrapper .dataTables_paginate .paginate_button {
                padding: 5px 8px !important;
                margin-left: 2px !important;
            }

            .dataTables_wrapper .dataTables_info {
                font-size: 11px;
            }
        }

    </style>


    <div class="student-page">

        <!-- PAGE HEADER -->
        <div class="mb-4">
            <div class="page-title">
                Student Management
            </div>
            <div class="page-subtitle">
                Register and manage student information
            </div>
        </div>

        <div class="row g-4">

            <!-- LIVEWIRE STUDENT REGISTRATION -->
            <div class="col-lg-5">
                <livewire:student-reg-form />
            </div>

            <!-- STUDENT LIST + EXISTING IMPORT / EXPORT ACTIONS -->
            <div class="col-lg-7">

                <div class="header-actions mb-3">
                    <form
                        action="{{ route('students.import') }}"
                        method="POST"
                        enctype="multipart/form-data"
                        class="d-inline import-form">
                        @csrf
                        <input
                            type="file"
                            name="file"
                            id="studentImport"
                            accept=".csv,.txt"
                            class="d-none">
                        <label for="studentImport" class="import-btn mb-0">
                            <i class="bi bi-upload me-1"></i>
                            Import CSV
                        </label>
                    </form>

                    <a href="{{ route('students.export-pdf') }}" class="pdf-btn">
                        <i class="bi bi-file-earmark-pdf me-1"></i>
                        Export PDF
                    </a>
                </div>

                <livewire:student-list-table />

            </div>

        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const importInput = document.getElementById('studentImport');

            if (importInput) {
                importInput.addEventListener('change', function () {
                    if (this.files.length > 0) {
                        this.form.submit();
                    }
                });
            }
        });
    </script>

@endsection
