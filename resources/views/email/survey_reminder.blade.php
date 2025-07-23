<!DOCTYPE html>
<html>
<head>
    {{-- <title>Reminder Schedule</title> --}}
</head>
<body>
    <p>Dear Participant,</p>

    <p>Thank you once again for your participation in our event titled: <b>{{ $survey->event->title }}</b>.</p>

    <p>To help us improve future events, we kindly ask you to complete the evaluation form before the deadline: <b>{{ \Carbon\Carbon::parse($survey->end_date)->format('d M Y') }}</b>.</p>

    <p><b>Important:</b> You need to complete the evaluation to be able to join our next event.</p>

    <p>To submit your evaluation:</p>
    <ol>
    <li>Open the <b>Darwinbox</b> app and click the <b>ExtraMile</b> button.</li>
    <li>Select <b>Your Voice Matters</b> and choose <b>({{ $survey->title }})</b>.</li>
    <li>Fill in the evaluation form completely and click <b>Submit</b>.</li>
    </ol>

    <p>Your feedback and participation are highly appreciated.</p>

    <p>Thank you, and have a great day!</p>

    <p><b>Best regards,</b><br>
    <b>Communication and Stakeholder Management Team</b></p>

</body>
</html>