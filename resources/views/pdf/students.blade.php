<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">

    <title>Students List</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
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
            padding: 6px;
            vertical-align: middle;
        }

        th {
            background-color: #eeeeee;
            font-weight: bold;
        }
    </style>
</head>

<body>

<h2>Student List</h2>

<table>

    <thead>
    <tr>
        <th>Reg No</th>
        <th>First Name</th>
        <th>Last Name</th>
        <th>Gender</th>
        <th>NIC</th>
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
            <td>{{ $student->first_name ?: $student->name }}</td>
            <td>{{ $student->last_name ?: '-' }}</td>
            <td>{{ $student->gender ?: '-' }}</td>
            <td>{{ $student->nic ?: '-' }}</td>
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
