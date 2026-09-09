<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Verify Your Email Address</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <h1 style="color: #1F2937;">Verify Your Email Address</h1>
    
    <p>Hello <strong>{{ $name }}</strong>,</p>
    
    <p>Thank you for registering with <strong>AutoProject-D Custom Garage</strong>. Please enter the 6-digit verification code below to activate your account:</p>
    
    <div style="background: #f3f4f6; border-radius: 8px; padding: 20px; text-align: center; margin: 24px 0;">
        <div style="font-size: 34px; font-weight: 800; letter-spacing: 10px; color: #E63946;">
            {{ $code }}
        </div>
    </div>
    
    <p>This code is valid for <strong>15 minutes</strong>. For security reasons, please do not share this code with anyone.</p>
    
    <p>If you did not request this account registration, no further action is required.</p>
    
    <hr style="border: none; border-top: 1px solid #e5e7eb; margin: 24px 0;">
    
    <p style="color: #6b7280; font-size: 14px;">
        Best regards,<br>
        <strong>AutoProject+ Team</strong><br>
        <em>AutoProject-D Custom Garage</em>
    </p>
</body>
</html>
