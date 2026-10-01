<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Contact Us</title>

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

        .contact-container {
            width: 90%;
            max-width: 1000px;
            margin: 60px auto;
        }

        .contact-title {
            text-align: center;
            margin-bottom: 30px;
        }

        .contact-title h1 {
            color: #198754;
            font-size: 36px;
            margin-bottom: 10px;
        }

        .contact-title p {
            color: #666;
            font-size: 16px;
        }

        .contact-box {
            display: flex;
            gap: 25px;
        }

        .contact-card {
            flex: 1;
            background: white;
            padding: 30px;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        }

        .contact-icon {
            font-size: 40px;
            margin-bottom: 15px;
        }

        .contact-card h2 {
            color: #222;
            margin-bottom: 10px;
        }

        .contact-card p {
            color: #666;
            line-height: 1.6;
        }

        .contact-card a {
            color: #198754;
            text-decoration: none;
        }

        .contact-card a:hover {
            text-decoration: underline;
        }

        @media (max-width: 700px) {
            .contact-box {
                flex-direction: column;
            }
        }
    </style>
</head>

<body>

@include('component.navbar')

<div class="contact-container">

    <div class="contact-title">
        <h1>Contact Us</h1>
        <p>Have a question? Feel free to contact us.</p>
    </div>

    <div class="contact-box">

        <div class="contact-card">

            <div class="contact-icon">📧</div>

            <h2>Email</h2>

            <p>
                <a href="mailto:admin@studentmanage.com">
                    admin@studentmanage.com
                </a>
            </p>

        </div>

        <div class="contact-card">

            <div class="contact-icon">📞</div>

            <h2>Phone</h2>

            <p>
                <a href="tel:0124578945">
                    0124578945
                </a>
            </p>

        </div>

        <div class="contact-card">

            <div class="contact-icon">📍</div>

            <h2>Address</h2>

            <p>
                StudentManage<br>
                Sri Lanka
            </p>

        </div>

    </div>

</div>

</body>
</html>
