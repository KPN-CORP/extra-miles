<!DOCTYPE html>
<html>
<head>
    {{-- <title>Reminder Schedule</title> --}}
</head>
<body>
    <p>Dear Participant,</p>

    <p>This is a friendly reminder to complete the {{ $survey->category }} titled: <strong>{{ $survey->title }}</strong>.</p>

    <p>To help us improve future events, we kindly ask you to complete the evaluation form before the deadline: <strong>{{ \Carbon\Carbon::parse($survey->end_date)->format('d M Y') }}</strong>.</p>
    <p><b>Important</b>: You need to complete the evaluation to be able to join our next event.</p>

    <p>Your feedback and participation are highly appreciated.</p>

    <p>Thank you, and have a great day!</p>

    <p>Best regards,
    Communication and Stakeholder Management Team</p>
</body>
</html>