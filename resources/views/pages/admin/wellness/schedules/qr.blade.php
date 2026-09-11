<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Attendance QR') }} &middot; {{ $schedule->activity->name }}</title>
    <script src="https://cdn.jsdelivr.net/npm/qr-code-styling@1.5.0/lib/qr-code-styling.js"></script>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            background: #fdf7ef;
            margin: 0;
            padding: 40px 20px;
            display: flex;
            justify-content: center;
        }
        .card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, .1);
            padding: 40px;
            max-width: 900px;
            width: 100%;
            display: flex;
            gap: 40px;
            flex-wrap: wrap;
        }
        .qr {
            flex: 0 0 300px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .details { flex: 1; min-width: 260px; }
        h1 { font-size: 26px; margin: 0 0 4px; }
        .type { color: #777; margin-bottom: 24px; }
        .label { font-weight: bold; color: #c10000; font-size: 13px; text-transform: uppercase; letter-spacing: .04em; }
        .value { margin: 2px 0 16px; font-size: 18px; }
        .hint {
            margin-top: 20px;
            padding: 12px 14px;
            background: #fdf7ef;
            border-left: 4px solid #ab2f2b;
            font-size: 13px;
            color: #555;
        }
        .actions { margin-top: 24px; }
        button {
            background: #ab2f2b;
            color: #fff;
            border: 0;
            border-radius: 6px;
            padding: 10px 18px;
            font-size: 14px;
            cursor: pointer;
        }
        @media print {
            body { background: #fff; padding: 0; }
            .card { box-shadow: none; }
            .actions, .hint { display: none; }
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="qr"><div id="qr"></div></div>

        <div class="details">
            <h1>{{ $schedule->activity->name }}</h1>
            <div class="type">{{ $schedule->activity->type?->name }}</div>

            <div class="label">{{ __('Date') }}</div>
            <div class="value">{{ $schedule->start_at->format('l, d F Y') }}</div>

            <div class="label">{{ __('Time') }}</div>
            <div class="value">{{ $schedule->start_at->format('H:i') }} &ndash; {{ $schedule->end_at->format('H:i') }}</div>

            <div class="label">{{ __('Location') }}</div>
            <div class="value">{{ $schedule->location ?: '-' }}</div>

            <div class="label">{{ __('Seats') }}</div>
            <div class="value">{{ $schedule->quota === null ? __('Unlimited') : $schedule->quota }}</div>

            <div class="hint">
                {!! __('Scanning is open from :from until :until.', [
                    'from' => '<strong>'.$schedule->checkInOpensAt()->format('d M H:i').'</strong>',
                    'until' => '<strong>'.$schedule->checkInClosesAt()->format('d M H:i').'</strong>',
                ]) !!}
                {{ __('Only approved participants can check in.') }}
            </div>

            <div class="actions">
                <button type="button" onclick="window.print()">{{ __('Print this QR') }}</button>
            </div>
        </div>
    </div>

    <script>
        new QRCodeStyling({
            width: 280,
            height: 280,
            type: 'svg',
            data: @json($schedule->qr_token),
            dotsOptions: { color: '#ab2f2b', type: 'rounded' },
            cornersSquareOptions: { color: '#7d1f1c' },
            backgroundOptions: { color: '#ffffff' }
        }).append(document.getElementById('qr'));
    </script>
</body>
</html>
