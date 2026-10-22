<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Degree | Academic Setup</title>

    <style>
        * { box-sizing: border-box; }

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

        h1 { margin-top: 0; }

        .subtitle {
            color: #6b7280;
            margin-bottom: 28px;
        }

        .field { margin-bottom: 22px; }

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

        textarea {
            min-height: 110px;
            resize: vertical;
        }

        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: #4f46e5;
        }

        .error-box {
            margin-bottom: 20px;
            padding: 12px 15px;
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

        .button-secondary { background: #64748b; }
    </style>
</head>

<body>
<div class="container">
    <div class="card">
        <h1>Edit Degree</h1>
        <p class="subtitle">Update the degree information below.</p>

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

        <form action="{{ route('degrees.update', $degree) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="field">
                <label for="course_id">Course *</label>

                <select id="course_id" name="course_id" required>
                    <option value="">Select a course</option>

                    @foreach ($courses as $course)
                        <option
                            value="{{ $course->id }}"
                            @selected(old('course_id', $degree->course_id) == $course->id)
                        >
                            {{ $course->course_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="field">
                <label for="degree_name">Degree Name *</label>

                <input
                    type="text"
                    id="degree_name"
                    name="degree_name"
                    value="{{ old('degree_name', $degree->degree_name) }}"
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
                >{{ old('description', $degree->description) }}</textarea>
            </div>

            <div class="actions">
                <a href="{{ route('degrees.index') }}"
                   class="button button-secondary">
                    Cancel
                </a>

                <button type="submit" class="button">
                    Update Degree
                </button>
            </div>
        </form>
    </div>
</div>
</body>
</html>
