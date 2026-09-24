<!DOCTYPE html>
<html>
<head>
    {{-- <title>Reminder Schedule</title> --}}
</head>
<body>
    <p>{{ __('Dear Participant,') }}</p>

    <p>{!! __('Thank you once again for your participation in our event titled: :title.', ['title' => '<b>'.e($survey->event->title).'</b>']) !!}</p>

    <p>{!! __('To help us improve future events, we kindly ask you to complete the evaluation form before the deadline: :deadline.', ['deadline' => '<b>'.\Carbon\Carbon::parse($survey->end_date)->translatedFormat('d M Y').'</b>']) !!}</p>

    <p><b>{{ __('Important:') }}</b> {{ __('You need to complete the evaluation to be able to join our next event.') }}</p>

    <p>{{ __('To submit your evaluation:') }}</p>
    <ol>
    <li>{!! __('Open the :app app and click the :button button.', ['app' => '<b>Darwinbox</b>', 'button' => '<b>ExtraMile</b>']) !!}</li>
    <li>{!! __('Select :menu and choose :survey.', ['menu' => '<b>'.__('Your Voice Matters').'</b>', 'survey' => '<b>('.e($survey->title).')</b>']) !!}</li>
    <li>{!! __('Fill in the evaluation form completely and click :submit.', ['submit' => '<b>'.__('Submit').'</b>']) !!}</li>
    </ol>

    <p>{{ __('Your feedback and participation are highly appreciated.') }}</p>

    <p>{{ __('Thank you, and have a great day!') }}</p>

    <p><b>{{ __('Best regards,') }}</b><br>
    <b>{{ __('Communication and Stakeholder Management Team') }}</b></p>

</body>
</html>