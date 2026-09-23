<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Attendance QR') }} &middot; {{ $type->name }}</title>
    <script src="https://cdn.jsdelivr.net/npm/qr-code-styling@1.5.0/lib/qr-code-styling.js"></script>
    <style>
        /* One sheet, portrait, nothing that can push to a second page. */
        @page { size: A4 portrait; margin: 12mm; }

        * { box-sizing: border-box; }

        html, body { height: 100%; }

        body {
            font-family: Arial, Helvetica, sans-serif;
            background: #f4f4f5;
            margin: 0;
            padding: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .sheet {
            background: #fff;
            width: 100%;
            max-width: 620px;
            padding: 48px 40px 40px;
            border-radius: 10px;
            box-shadow: 0 2px 16px rgba(0, 0, 0, .08);
            text-align: center;
        }

        h1 {
            font-size: 34px;
            line-height: 1.15;
            margin: 0;
            color: #1c1917;
            /* Long type names shrink the QR rather than spilling onto page two. */
            overflow-wrap: break-word;
        }

        .kicker {
            font-size: 12px;
            letter-spacing: .14em;
            text-transform: uppercase;
            color: #ab2f2b;
            margin: 0 0 10px;
        }

        .qr { margin: 32px auto 0; }

        /* The library writes a fixed-size svg. Blocks all the way down so the
           width below actually applies -- as a flex item it would just sit at
           its intrinsic size and ignore the cap. */
        .qr, .qr > div { display: block; }

        .qr svg {
            display: block;
            width: 100%;
            height: auto;
            max-width: 460px;
            margin: 0 auto;
        }

        .window {
            margin: 28px 0 0;
            font-size: 15px;
            color: #57534e;
        }

        .actions { margin-top: 28px; }

        button {
            background: #ab2f2b;
            color: #fff;
            border: 0;
            border-radius: 6px;
            padding: 10px 20px;
            font-size: 14px;
            cursor: pointer;
        }

        @media print {
            body { background: #fff; padding: 0; display: block; }
            .sheet { max-width: none; box-shadow: none; border-radius: 0; padding: 0; }
            .actions { display: none; }
            /* A4 printable area is 186 x 273mm; the rest of the sheet takes
               about 50mm even with a three-line title. */
            .qr svg { max-width: 150mm; }
        }
    </style>
</head>
<body>
    <div class="sheet">
        <p class="kicker">{{ __('Attendance QR') }}</p>
        <h1>{{ $type->name }}</h1>

        <div class="qr"><div id="qr"></div></div>

        <p class="window">
            {{ __('Scan opens :before min before the session starts and closes :after min after it ends.', [
                'before' => $type->checkInOpensMinutesBefore(),
                'after' => $type->checkInClosesMinutesAfter(),
            ]) }}
        </p>

        <div class="actions">
            <button type="button" onclick="window.print()">{{ __('Print this QR') }}</button>
        </div>
    </div>

    <script>
        const SIZE = 420;
        const target = document.getElementById('qr');

        new QRCodeStyling({
            width: SIZE,
            height: SIZE,
            type: 'svg',
            data: @json($type->qr_token),
            margin: 0,
            dotsOptions: { color: '#1c1917', type: 'rounded' },
            cornersSquareOptions: { color: '#ab2f2b' },
            backgroundOptions: { color: '#ffffff' }
        }).append(target);

        // The library emits width/height attributes and no viewBox, so a CSS
        // width just stretches the element while the drawing stays at its
        // natural size in the top-left corner -- visibly off-centre, and the
        // wrong size on paper. Swapping those attributes for a viewBox makes it
        // scale properly to whatever width the sheet gives it.
        const svg = target.querySelector('svg');

        if (svg) {
            svg.setAttribute('viewBox', `0 0 ${SIZE} ${SIZE}`);
            svg.setAttribute('preserveAspectRatio', 'xMidYMid meet');
            svg.removeAttribute('width');
            svg.removeAttribute('height');
        }
    </script>
</body>
</html>
