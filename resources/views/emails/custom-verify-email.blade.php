<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Verify Your Email</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            background-color: #f4f4f7;
            color: #51545e;
            margin: 0;
            padding: 0;
            width: 100% !important;
        }
        .container {
            width: 100%;
            margin: 0;
            padding: 45px 0;
            background-color: #f4f4f7;
        }
        .content {
            max-width: 570px;
            margin: 0 auto;
            background-color: #ffffff;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .header h1 {
            color: #333333;
            font-size: 24px;
            font-weight: bold;
            margin-top: 0;
        }
        .body p {
            font-size: 16px;
            line-height: 1.6;
            margin-bottom: 25px;
        }
        .button-container {
            text-align: center;
            margin: 35px 0;
        }
        .button {
            background-color: #3b82f6;
            color: #ffffff !important;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
            display: inline-block;
            transition: background-color 0.3s ease;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 12px;
            color: #b0adc5;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="content">
            <div class="header">
                <h1>GymOS</h1>
            </div>
            <div class="body">
                <p>Hello,</p>
                <p>Thank you for joining GymOS! Please click the button below to verify your email address and complete your registration.</p>
                <div class="button-container">
                    <a href="{{ $url }}" class="button">Verify Email Address</a>
                </div>
                <p>If you did not create an account, no further action is required.</p>
                <p>Regards,<br>The GymOS Team</p>
            </div>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} GymOS. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
