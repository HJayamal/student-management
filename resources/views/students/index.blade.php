<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Management</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f7fb;
            margin: 0;
        }

        .container {
            width: 95%;
            margin: 30px auto;
        }

        .row {
            display: flex;
            gap: 25px;
        }

        .form-box,
        .list-box {
            background: white;
            padding: 20px;
            border-radius: 8px;
            flex: 1;
        }

        input {
            width: 100%;
            padding: 9px;
            margin: 8px 0 15px;
            box-sizing: border-box;
        }

        button {
            padding: 9px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .register {
            background: green;
            color: white;
            width: 100%;
        }

        .update {
            background: orange;
            color: white;
            text-decoration: none;
            padding: 6px 10px;
            border-radius: 4px;
        }

        .delete {
            background: red;
            color: white;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 10px;
            border: 1px solid #ddd;
        }

        th {
            background: green;
            color: white;
        }

        .success {
            background: #d1e7dd;
            padding: 10px;
            margin-bottom: 15px;
        }

        .error {
            color: red;
            margin-bottom: 15px;
        }

        @media (max-width: 900px) {
            .row {
                flex-direction: column;
            }
        }
    </style>
</head>

<body>

@include('component.navbar')

<div class="container">

    <h1>Student Management System</h1>

    @if(session('success'))
        <div class="success">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="error">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <div class="row">

        <!-- Register Form -->
        <div class="form-box">

            <h2>Student Register</h2>

            <form action="{{ route('students.store') }}" method="POST">

                @csrf

                <label>Register No</label>
                <input type="text" name="reg_no" required>

                <label>Full Name</label>
                <input type="text" name="name" required>

                <label>Email</label>
                <input type="email" name="email" required>

                <label>Phone No</label>
                <input type="text" name="phone" required>

                <label>Date of Birth</label>
                <input type="date" name="dob" required>

                <label>Password</label>
                <input type="password" name="password" required>

                <label>Address</label>
                <input type="text" name="address" required>

                <button type="submit" class="register">
                    Register
                </button>

            </form>

        </div>

        <!-- Student List -->
        <div class="list-box">

            <h2>Student List</h2>

            <table>

                <tr>
                    <th>Reg No</th>
                    <th>Name</th>
                    <th>DOB</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Action</th>
                </tr>

                @forelse($students as $student)

                    <tr>

                        <td>{{ $student->reg_no }}</td>
                        <td>{{ $student->name }}</td>
                        <td>{{ $student->dob }}</td>
                        <td>{{ $student->email }}</td>
                        <td>{{ $student->phone }}</td>

                        <td>

                            <a href="{{ route('students.edit', $student) }}"
                               class="update">
                                Update
                            </a>

                            <form action="{{ route('students.destroy', $student) }}"
                                  method="POST"
                                  style="display:inline;">

                                @csrf
                                @method('DELETE')

                                <button type="submit"
                                        class="delete"
                                        onclick="return confirm('Delete this student?')">
                                    Delete
                                </button>

                            </form>

                        </td>

                    </tr>

                @empty

                    <tr>
                        <td colspan="6">
                            No students found.
                        </td>
                    </tr>

                @endforelse

            </table>

        </div>

    </div>

</div>

</body>
</html>
