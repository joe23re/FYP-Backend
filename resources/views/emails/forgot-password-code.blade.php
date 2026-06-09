<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>VAGDIAG Verification Code</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f4f7ff; padding: 30px;">
    <div style="max-width: 500px; margin: auto; background: white; border-radius: 12px; padding: 30px; text-align: center;">
        <h1 style="color: #2D7EEB;">VAGDIAG</h1>

        <h2>Password Reset Code</h2>

        <p>Your verification code is:</p>

        <div style="font-size: 36px; font-weight: bold; letter-spacing: 8px; color: #2D7EEB; margin: 25px 0;">
            {{ $code }}
        </div>

        <p>This code will expire in 10 minutes.</p>

        <p style="color: #777;">
            If you did not request this code, you can ignore this email.
        </p>
    </div>
</body>
</html>