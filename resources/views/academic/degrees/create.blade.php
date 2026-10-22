<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Degree | Academic Setup</title>

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
            margin-top: 0;
            margin-bottom: 28px;
            color: #6b7280;
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

        input, select, textarea {
            width: 100%;
            padding: 12px 13px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 15px;
            font-family: inherit;
            background: white;
        }

        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: #4f46e5;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.12);
        }

        textarea {
            min-height: 110px;
            resize: vertical;
        }

        .error-box {
            margin-bottom: 20px;
            padding: 12px 15px;
            background: #fee2e2;
            color: #991b1b;
            border-radius: 8px;
        }

        .hint {
            display: block;
            margin-top: 7px;
            color: #6b7280;
            font-size: 13px;
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
    </style>
</head>

<body>
<div class="container">
    <div class="card">
        <h1>Add New Degree</h1>

        <p class="subtitle">
            Select a registered course and add a degree under it.
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

        @if ($courses->isEmpty())
            <div class="error-box">
                Please register a course before adding a degree.
            </div>

            <a href="{{ route('courses.create') }}"
               class="button">
                Add Course
            </a>
        @else
            <form action="{{ route('degrees.store') }}" method="POST">
                @csrf

                <div class="field">
                    <label for="course_id">Course *</label>

                    <select id="course_id" name="course_id" required>
                        <option value="">Select a course</option>

                        @foreach ($courses as $course)
                            <option
                                value="{{ $course->id }}"
                                @selected(old('course_id') == $course->id)
                            >
                                {{ $course->course_name }}
                            </option>
                        @endforeach
                    </select>

                    <span class="hint">
                        Select the course to which this degree belongs.
                    </span>
                </div>

                <div class="field">
                    <label for="degree_name">Degree Name *</label>

                    <input
                        type="text"
                        id="degree_name"
                        name="degree_name"
                        value="{{ old('degree_name') }}"
                        placeholder="e.g. BSc (Honours) in Software Engineering"
                        maxlength="255"
                        required
                    >
                </div>

                <div class="field">
                    <label for="description">Description</label>

                    <textarea
                        id="description"
                        name="description"
                        placeholder="Enter degree details (optional)"
                    >{{ old('description') }}</textarea>
                </div>

                <div class="actions">
                    <a href="{{ route('degrees.index') }}"
                       class="button button-secondary">
                        Cancel
                    </a>

                    <button type="submit" class="button">
                        Save Degree
                    </button>
                </div>
            </form>
        @endif
    </div>
</div>
</body>
</html>
