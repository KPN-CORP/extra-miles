<!DOCTYPE html>
<html>
<head>
    {{-- <title>Reminder Schedule</title> --}}
</head>
<body>
    <p><strong>{{ __('Dear :name,', ['name' => $name]) }}</strong></p>
    {!! $messages !!}
</body>
</html>