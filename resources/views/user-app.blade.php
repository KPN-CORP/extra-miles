<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=2.0, user-scalable=no" />
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    <title>Extra Mile</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;700&display=swap" rel="stylesheet">
    @viteReactRefresh
    @vite(['resources/js/users/main.jsx', 'resources/css/app.css'])
</head>
<body>
    <div id="root"></div>
</body>
</html>