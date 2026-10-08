<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">

    <title>Teachers List</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 9px;
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
            font-size: 18px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            border: 1px solid #000;
            padding: 5px;
            vertical-align: middle;
        }

        th {
            background-color: #eeeeee;
            font-weight: bold;
        }

        .teacher-image {
            width: 45px;
            height: 45px;
            object-fit: cover;
        }

        .no-image {
            color: #777777;
            font-size: 8px;
        }
    </style>
</head>

<body>

<h2>Teachers List</h2>

<table>

    <thead>
    <tr>
        <th>Image</th>
        <th>Name</th>
        <th>Email</th>
        <th>Phone</th>
        <th>Gender</th>
        <th>Subject</th>
        <th>Address</th>
    </tr>
    </thead>

    <tbody>

    @foreach($teachers as $teacher)

        <tr>

            <td style="text-align: center;">

                @if($teacher->image && file_exists(public_path('storage/' . $teacher->image)))

                    <img
                        src="{{ public_path('storage/' . $teacher->image) }}"
                        class="teacher-image"
                        alt="Teacher Image">

                @else

                    <span class="no-image">
                        No Image
                    </span>

                @endif

            </td>

            <td>{{ $teacher->name }}</td>

            <td>{{ $teacher->email }}</td>

            <td>{{ $teacher->phone }}</td>

            <td>{{ $teacher->gender ?: '-' }}</td>

            <td>{{ $teacher->subject }}</td>

            <td>{{ $teacher->address }}</td>

        </tr>

    @endforeach

    </tbody>

</table>

</body>
</html>
