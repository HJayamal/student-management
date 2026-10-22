<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Course | Academic Setup</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 30px 15px;
            background: #f4f6fb;
            color: #1f2937;
            font-family: Arial, sans-serif;
        }

        .container {
            max-width: 720px;
            margin: 30px auto;
        }

        .card {
            padding: 30px;
            background: white;
            border-radius: 14px;
            box-shadow: 0 4px 18px rgba(0, 0, 0, 0.06);
        }

        h1 {
            margin-top: 0;
            margin-bottom: 8px;
        }

        .subtitle {
            color: #6b7280;
            margin-bottom: 28px;
            line-height: 1.5;
        }

        .field {
            margin-bottom: 22px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
        }

        input, textarea {
            width: 100%;
            padding: 12px 13px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 15px;
            font-family: inherit;
        }

        input:focus, textarea:focus {
            outline: none;
            border-color: #4f46e5;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.12);
        }

        textarea {
            min-height: 120px;
            resize: vertical;
        }

        .error-box {
            padding: 12px 15px;
            margin-bottom: 20px;
            background: #fee2e2;
            color: #991b1b;
            border-radius: 8px;
        }

        .actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 28px;
        }

        .button {
            display: inline-block;
            padding: 12px 18px;
            border: none;
            border-radius: 8px;
            background: #4f46e5;
            color: white;
            text-decoration: none;
            font-size: 14px;
            cursor: pointer;
        }

        .button-secondary {
            background: #64748b;
        }

        @media (max-width: 500px) {
            .card {
                padding: 20px;
            }

            .actions {
                flex-direction: column-reverse;
            }

            .actions .button {
                text-align: center;
            }
        }
    </style>
</head>

<body>
<div class="container">
    <div class="card">
        <h1>Edit Course</h1>

        <p class="subtitle">
            Update the course information below.
        </p>

        @if ($errors->any())
            <div class="error-box" role="alert">
                <strong>Please correct the following:</strong>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form
            action="{{ route('courses.update', $course) }}"
            method="POST"
        >
            @csrf
            @method('PUT')

            <div class="field">
                <label for="course_name">Course Name *</label>

                <input
                    type="text"
                    id="course_name"
                    name="course_name"
                    value="{{ old('course_name', $course->course_name) }}"
                    maxlength="255"
                    required
                    autofocus
                >
            </div>

            <div class="field">
                <label for="description">Description</label>

                <textarea
                    id="description"
                    name="description"
                    placeholder="Enter a course description (optional)"
                >{{ old('description', $course->description) }}</textarea>
            </div>

            <div class="actions">
                <a
                    href="{{ route('courses.index') }}"
                    class="button button-secondary"
                >
                    Cancel
                </a>

                <button type="submit" class="button">
                    Update Course
                </button>
            </div>
        </form>
    </div>
</div>
</body>
</html>
