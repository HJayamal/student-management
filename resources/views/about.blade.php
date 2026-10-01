<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>About Us</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: #f5f7fb;
        }

        .about-container {
            width: 90%;
            max-width: 900px;
            margin: 60px auto;
        }

        .about-box {
            background: white;
            padding: 45px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            text-align: center;
        }

        .about-box h1 {
            color: #198754;
            font-size: 36px;
            margin-bottom: 20px;
        }

        .about-box p {
            color: #555;
            font-size: 17px;
            line-height: 1.8;
            margin-bottom: 15px;
        }

        .about-icon {
            font-size: 50px;
            margin-bottom: 15px;
        }

        .about-btn {
            display: inline-block;
            margin-top: 15px;
            padding: 11px 22px;
            background: #198754;
            color: white;
            text-decoration: none;
            border-radius: 6px;
        }

        .about-btn:hover {
            background: #157347;
        }
    </style>
</head>

<body>

@include('component.navbar')

<div class="about-container">

    <div class="about-box">

        <div class="about-icon">🎓</div>

        <h1>About Us</h1>

        <p>
            Welcome to <strong>StudentManage</strong>, a simple and
            user-friendly Student Management System.
        </p>

        <p>
            Our system helps manage student information such as
            registration number, name, email, phone number and date of birth
            in an organized way.
        </p>

        <p>
            It allows users to register, view, update and delete
            student records easily.
        </p>

        <a href="{{ route('home') }}" class="about-btn">
            Go to Student Management
        </a>

    </div>

</div>

</body>
</html>
