<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Management</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 30px;
            background: #f4f6fb;
            color: #1f2937;
            font-family: Arial, sans-serif;
        }

        .container {
            max-width: 1100px;
            margin: auto;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            margin-bottom: 25px;
        }

        h1 {
            margin: 0 0 8px;
        }

        .muted {
            color: #6b7280;
        }

        .card {
            padding: 24px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 3px 15px rgba(0, 0, 0, 0.05);
            overflow-x: auto;
        }

        .button {
            display: inline-block;
            padding: 10px 15px;
            border: 0;
            border-radius: 7px;
            background: #4f46e5;
            color: white;
            text-decoration: none;
            cursor: pointer;
            font-size: 14px;
        }

        .button-secondary {
            background: #64748b;
        }

        .button-danger {
            background: #dc2626;
        }

        .message {
            padding: 12px 15px;
            margin-bottom: 18px;
            border-radius: 7px;
        }

        .success {
            background: #dcfce7;
            color: #166534;
        }

        .error {
            background: #fee2e2;
            color: #991b1b;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        th, td {
            padding: 14px 12px;
            border-bottom: 1px solid #e5e7eb;
            text-align: left;
        }

        th {
            background: #f8fafc;
        }

        .actions {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .actions form {
            margin: 0;
        }

        @media (max-width: 650px) {
            body {
                padding: 15px;
            }

            .header {
                align-items: flex-start;
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
<div class="container">

    <div class="header">
        <div>
            <h1>Course Management</h1>
            <p class="muted">Manage campus courses and their degrees.</p>
        </div>

        <div>
            <a href="{{ route('home') }}" class="button button-secondary">
                Back to Dashboard
            </a>

            <a href="{{ route('courses.create') }}" class="button">
                + Add Course
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="message success">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="message error">
            {{ session('error') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="message error">
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <div class="card">
        <h2>Registered Courses</h2>

        <table>
            <thead>
            <tr>
                <th>#</th>
                <th>Course Name</th>
                <th>Description</th>
                <th>Degrees</th>
                <th>Actions</th>
            </tr>
            </thead>

            <tbody>
            @forelse ($courses as $course)
                <tr>
                    <td>{{ $courses->firstItem() + $loop->index }}</td>

                    <td>
                        <strong>{{ $course->course_name }}</strong>
                    </td>

                    <td>{{ $course->description ?: '—' }}</td>

                    <td>{{ $course->degrees_count }}</td>

                    <td>
                        <div class="actions">
                            <a
                                href="{{ route('courses.edit', $course) }}"
                                class="button"
                            >
                                Edit
                            </a>

                            <form
                                action="{{ route('courses.destroy', $course) }}"
                                method="POST"
                                onsubmit="return confirm('Are you sure you want to delete this course?');"
                            >
                                @csrf
                                @method('DELETE')

                                <button type="submit" class="button button-danger">
                                    Delete
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">
                        No courses registered yet. Click "Add Course" to create one.
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>

        <div style="margin-top: 20px;">
            {{ $courses->links() }}
        </div>
    </div>

</div>
</body>
</html>
