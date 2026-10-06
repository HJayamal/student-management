<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">

    <title>Students List</title>

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

<h2>Student List</h2>

<table>

    <thead>
    <tr>
        <th>Reg No</th>
        <th>Name</th>
        <th>DOB</th>
        <th>Email</th>
        <th>Phone</th>
        <th>Address</th>
    </tr>
    </thead>

    <tbody>

    @foreach($students as $student)

        <tr>
            <td>{{ $student->reg_no }}</td>
            <td>{{ $student->name }}</td>
            <td>{{ $student->dob }}</td>
            <td>{{ $student->email }}</td>
            <td>{{ $student->phone }}</td>
            <td>{{ $student->address }}</td>
        </tr>

    @endforeach

    </tbody>

</table>

</body>
</html>
