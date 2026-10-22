<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Degree Management</title>

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
            <h1>Degree Management</h1>
            <p class="muted">
                Manage degrees under each registered course.
            </p>
        </div>

        <div>
            <a href="{{ route('courses.index') }}" class="button button-secondary">
                Manage Courses
            </a>

            <a href="{{ route('home') }}" class="button button-secondary">
                Dashboard
            </a>

            <a href="{{ route('degrees.create') }}" class="button">
                + Add Degree
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
        <h2>Registered Degrees</h2>

        <table>
            <thead>
            <tr>
                <th>#</th>
                <th>Degree Name</th>
                <th>Course</th>
                <th>Batches</th>
                <th>Actions</th>
            </tr>
            </thead>

            <tbody>
            @forelse ($degrees as $degree)
                <tr>
                    <td>{{ $degrees->firstItem() + $loop->index }}</td>

                    <td>
                        <strong>{{ $degree->degree_name }}</strong>
                    </td>

                    <td>{{ $degree->course?->course_name ?? '—' }}</td>

                    <td>{{ $degree->batches_count }}</td>

                    <td>
                        <div class="actions">
                            <a
                                href="{{ route('degrees.edit', $degree) }}"
                                class="button"
                            >
                                Edit
                            </a>

                            <form
                                action="{{ route('degrees.destroy', $degree) }}"
                                method="POST"
                                onsubmit="return confirm('Are you sure you want to delete this degree?');"
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
                        No degrees registered yet. Click "Add Degree" to create one.
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>

        <div style="margin-top: 20px;">
            {{ $degrees->links() }}
        </div>
    </div>

</div>
</body>
</html>
