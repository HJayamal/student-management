@extends('layouts.app')

@section('title', 'Student Images')

@section('content')

    <style>
        .image-gallery-page {
            background: #f8fafc;
            min-height: calc(100vh - 120px);
            padding: 10px 5px 35px;
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

        .gallery-card {
            background: #ffffff !important;
            border: 1px solid #e2e8f0 !important;
            border-radius: 20px !important;
            overflow: hidden;
            box-shadow: 0 8px 28px rgba(15, 23, 42, 0.07) !important;
        }

        .gallery-header {
            background: linear-gradient(135deg, #4f46e5, #6366f1) !important;
            color: #ffffff !important;
            padding: 20px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px;
        }

        .gallery-header-title {
            margin: 0;
            color: #ffffff !important;
            font-size: 20px;
            font-weight: 700;
        }

        .gallery-header-subtitle {
            color: rgba(255, 255, 255, 0.88) !important;
            font-size: 13px;
            margin-top: 3px;
        }

        .student-count {
            background: rgba(255, 255, 255, 0.16);
            border: 1px solid rgba(255, 255, 255, 0.25);
            color: #ffffff;
            border-radius: 999px;
            padding: 8px 13px;
            font-size: 12px;
            font-weight: 600;
            white-space: nowrap;
        }

        .gallery-body {
            background: #ffffff !important;
            padding: 24px;
        }

        .student-gallery {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 20px;
        }

        .student-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 5px 18px rgba(15, 23, 42, 0.06);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .student-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(15, 23, 42, 0.10);
        }

        .student-image-wrap {
            height: 240px;
            background: #f1f5f9;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .student-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .no-student-image {
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: #94a3b8;
            background: linear-gradient(135deg, #f8fafc, #eef2f7);
        }

        .no-student-image i {
            font-size: 48px;
            margin-bottom: 8px;
            color: #cbd5e1;
        }

        .no-student-image span {
            font-size: 13px;
            font-weight: 600;
        }

        .student-details {
            padding: 17px 18px 18px;
        }

        .student-name {
            color: #0f172a !important;
            font-size: 17px;
            font-weight: 700;
            margin-bottom: 10px;
            line-height: 1.3;
        }

        .student-info {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #64748b !important;
            font-size: 13px;
            margin-top: 7px;
            word-break: break-word;
        }

        .student-info i {
            color: #6366f1 !important;
            width: 16px;
            text-align: center;
            flex-shrink: 0;
        }

        .reg-badge {
            display: inline-flex;
            align-items: center;
            background: #eef2ff;
            color: #4338ca !important;
            padding: 5px 10px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .empty-gallery {
            padding: 65px 20px;
            text-align: center;
            color: #64748b;
        }

        .empty-gallery i {
            font-size: 56px;
            color: #cbd5e1;
            margin-bottom: 12px;
        }

        .empty-gallery-title {
            color: #334155 !important;
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 4px;
        }

        @media (max-width: 1100px) {
            .student-gallery {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 700px) {
            .image-gallery-page {
                padding: 5px 0 25px;
            }

            .page-title {
                font-size: 24px;
            }

            .gallery-header {
                padding: 16px;
            }

            .gallery-header-title {
                font-size: 17px;
            }

            .gallery-body {
                padding: 16px;
            }

            .student-gallery {
                grid-template-columns: 1fr;
            }

            .student-image-wrap {
                height: 260px;
            }
        }
    </style>

    <div class="image-gallery-page">

        <div class="mb-4">
            <div class="page-title">
                Student Images
            </div>

            <div class="page-subtitle">
                View all registered student photos and information
            </div>
        </div>

        <div class="gallery-card">

            <div class="gallery-header">

                <div>
                    <h3 class="gallery-header-title">
                        <i class="bi bi-images me-2"></i>
                        Student Image Gallery
                    </h3>

                    <div class="gallery-header-subtitle">
                        Registered student profiles
                    </div>
                </div>

                <div class="student-count">
                    {{ $students->count() }} Students
                </div>

            </div>

            <div class="gallery-body">

                @if($students->count() > 0)

                    <div class="student-gallery">

                        @foreach($students as $student)

                            <div class="student-card">

                                <div class="student-image-wrap">

                                    @if($student->image)

                                        <img
                                            src="{{ asset('storage/' . $student->image) }}"
                                            alt="{{ $student->name }}"
                                            class="student-image"
                                        >

                                    @else

                                        <div class="no-student-image">
                                            <i class="bi bi-person-circle"></i>
                                            <span>No Image Available</span>
                                        </div>

                                    @endif

                                </div>

                                <div class="student-details">

                                    <div class="reg-badge">
                                        <i class="bi bi-card-text me-1"></i>
                                        Reg No: {{ $student->reg_no }}
                                    </div>

                                    <div class="student-name">
                                        {{ $student->name }}
                                    </div>

                                    <div class="student-info">
                                        <i class="bi bi-envelope"></i>
                                        <span>{{ $student->email }}</span>
                                    </div>

                                    <div class="student-info">
                                        <i class="bi bi-telephone"></i>
                                        <span>{{ $student->phone }}</span>
                                    </div>

                                </div>

                            </div>

                        @endforeach

                    </div>

                @else

                    <div class="empty-gallery">

                        <i class="bi bi-images d-block"></i>

                        <div class="empty-gallery-title">
                            No Student Images Found
                        </div>

                        <div>
                            Add student images from the Student Management page.
                        </div>

                    </div>

                @endif

            </div>

        </div>

    </div>

@endsection
