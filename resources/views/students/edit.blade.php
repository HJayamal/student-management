<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Update Student</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f7fb;
            margin: 0;
        }

        .container {
            width: 500px;
            margin: 40px auto;
            background: white;
            padding: 25px;
            border-radius: 8px;
        }

        input {
            width: 100%;
            padding: 10px;
            margin: 8px 0 15px;
            box-sizing: border-box;
        }

        button {
            width: 100%;
            padding: 10px;
            background: green;
            color: white;
            border: none;
            cursor: pointer;
        }

        .error {
            color: red;
            margin-bottom: 15px;
        }
    </style>
</head>

<body>

@include('component.navbar')

<div class="container">

    <h1>Update Student</h1>

    @if($errors->any())
        <div class="error">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <form action="{{ route('students.update', $student) }}" method="POST">

        @csrf
        @method('PUT')

        <label>Register No</label>
        <input
            type="text"
            name="reg_no"
            value="{{ $student->reg_no }}"
            required
        >

        <label>Full Name</label>
        <input
            type="text"
            name="name"
            value="{{ $student->name }}"
            required
        >

        <label>Email</label>
        <input
            type="email"
            name="email"
            value="{{ $student->email }}"
            required
        >

        <label>Phone No</label>
        <input
            type="text"
            name="phone"
            value="{{ $student->phone }}"
            required
        >

        <label>Date of Birth</label>
        <input
            type="date"
            name="dob"
            value="{{ $student->dob }}"
            required
        >

        <label>Password</label>
        <input
            type="password"
            name="password"
            placeholder="Enter new password"
        >

        <label>Address</label>
        <input
            type="text"
            name="address"
            value="{{ $student->address }}"
            required
        >

        <button type="submit">
            Update Student
        </button>

    </form>

</div>

</body>
</html>
