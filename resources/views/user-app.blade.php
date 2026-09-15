<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    {{-- viewport-fit=cover diperlukan agar env(safe-area-inset-*) terisi di
         perangkat bernotch; tanpa itu header dan tab bar tertimpa notch. --}}
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=2.0, user-scalable=no, viewport-fit=cover" />
    <meta name="theme-color" content="#b91c1c">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    <title>Extra Mile</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @viteReactRefresh
    @vite(['resources/js/users/main.jsx', 'resources/css/app.css'])
</head>
<body>
    <div id="root"></div>
</body>
</html>