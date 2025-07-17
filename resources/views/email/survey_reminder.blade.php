<!DOCTYPE html>
<html>
<head>
    {{-- <title>Reminder Schedule</title> --}}
</head>
<body>
    <p>Dear Participant,</p>

    <p>This is a friendly reminder to complete the {{ $survey->category }} titled: <strong>{{ $survey->title }}</strong>.</p>

    <p>Please make sure to submit your response before the deadline on <strong>{{ \Carbon\Carbon::parse($survey->end_date)->format('d M Y') }}</strong>.</p>

    <p><em>Your participation is highly appreciated.</em></p>

    <p>Thank you and have a great day!</p>

</body>
</html>