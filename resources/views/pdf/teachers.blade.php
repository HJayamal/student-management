<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">

    <title>Teachers List</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            border: 1px solid #000;
            padding: 8px;
        }

        th {
            background-color: #eeeeee;
        }
    </style>
</head>

<body>

<h2>Teachers List</h2>

<table>

    <thead>
    <tr>
        <th>Name</th>
        <th>Email</th>
        <th>Phone</th>
        <th>Subject</th>
        <th>Address</th>
    </tr>
    </thead>

    <tbody>

    @foreach($teachers as $teacher)

        <tr>
            <td>{{ $teacher->name }}</td>
            <td>{{ $teacher->email }}</td>
            <td>{{ $teacher->phone }}</td>
            <td>{{ $teacher->subject }}</td>
            <td>{{ $teacher->address }}</td>
        </tr>

    @endforeach

    </tbody>

</table>

</body>
</html>
