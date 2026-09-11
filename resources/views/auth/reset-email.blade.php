<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Reset Password') }}</title>
</head>
<body>
    <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 40px 60px; background-color: #f8fafd;">
        <div style="background-color: white; padding: 40px; border-radius: 10px; border: 1px solid rgba(0,0,0,0.1);">
            <h3 style="color: #333;">{{ __('Hi :name,', ['name' => $user->name]) }}</h3>
            <p>{{ __('You are receiving this email because we received a password reset request for your account.') }}</p>
            <div style="margin: 30px; text-align: center"><a href="{{ $resetUrl }}" style="background-color: #007bff; color: #fff; padding: 10px 20px; text-decoration: none; border-radius: 5px;">{{ __('Reset Password') }}</a></div>
            <p>{{ __('This password reset link will expire in 60 minutes.') }}</p>
            <p style="margin-top: 20px;">{{ __('Thank you,') }}</p>
            <p>{{ __('HC Support') }}</p>
        </div>
    </div>
</body>
</html>
