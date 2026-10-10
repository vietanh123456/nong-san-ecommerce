<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mã OTP đặt lại mật khẩu</title>
</head>
<body style="margin: 0; padding: 24px; background-color: #f3f4f6; color: #1f2937; font-family: Arial, sans-serif;">
    <main style="max-width: 560px; margin: 0 auto; padding: 32px; background-color: #ffffff; border-radius: 12px;">
        <h1 style="margin-top: 0; color: #065f46;">Đặt lại mật khẩu</h1>
        <p>Bạn vừa yêu cầu đặt lại mật khẩu tài khoản Nông Sản Việt.</p>
        <p>Mã OTP của bạn là:</p>
        <p style="padding: 16px; background-color: #ecfdf5; border-radius: 8px; color: #065f46; font-size: 28px; font-weight: bold; letter-spacing: 8px; text-align: center;">
            {{ $code }}
        </p>
        <p>Mã có hiệu lực trong 5 phút. Không chia sẻ mã này với bất kỳ ai.</p>
        <p>Nếu bạn không thực hiện yêu cầu này, hãy bỏ qua email.</p>
    </main>
</body>
</html>
