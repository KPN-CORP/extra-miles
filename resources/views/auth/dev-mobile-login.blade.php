<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Mobile Development Login') }} — {{ config('app.name') }}</title>
    <link rel="icon" type="image/ico" href="{{ asset('storage/img/favicon.ico') }}">
    <style>
        :root { color-scheme: light; }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #eef2f7;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            color: #313a46;
        }
        .card {
            width: 100%;
            max-width: 460px;
            margin: 24px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, .08);
            padding: 28px;
        }
        h1 { font-size: 20px; margin: 0 0 4px; }
        .subtitle { margin: 0 0 20px; font-size: 13px; color: #8391a2; }
        .banner {
            background: #fff3cd;
            border: 1px solid #ffe69c;
            color: #664d03;
            border-radius: 6px;
            padding: 10px 12px;
            font-size: 12px;
            line-height: 1.5;
            margin-bottom: 14px;
        }
        .banner.info {
            background: #e7f1ff;
            border-color: #b6d4fe;
            color: #084298;
            margin-bottom: 20px;
        }
        label { display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px; }
        input[type=text] {
            width: 100%;
            padding: 9px 12px;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            font-size: 14px;
            margin-bottom: 16px;
        }
        input:focus { outline: none; border-color: #727cf5; box-shadow: 0 0 0 3px rgba(114, 124, 245, .15); }
        .hint { font-size: 12px; color: #8391a2; margin: -12px 0 16px; }
        button {
            width: 100%;
            padding: 10px;
            border: 0;
            border-radius: 6px;
            background: #727cf5;
            color: #fff;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
        }
        button:hover { background: #6169d0; }
        .errors {
            background: #fdf1f3;
            border: 1px solid #f7c9d0;
            color: #a12c3f;
            border-radius: 6px;
            padding: 10px 12px;
            font-size: 13px;
            margin-bottom: 18px;
        }
        .errors ul { margin: 0; padding-left: 18px; }
        .remember { display: flex; align-items: center; gap: 8px; font-size: 13px; margin-bottom: 18px; }
        .remember label { margin: 0; font-weight: 400; }
        .token {
            background: #f4f6fb;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            padding: 12px;
            margin-bottom: 18px;
        }
        .token strong { display: block; font-size: 13px; margin-bottom: 8px; }
        .token textarea {
            width: 100%;
            height: 108px;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            padding: 8px;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 11px;
            line-height: 1.4;
            resize: vertical;
            word-break: break-all;
        }
        .token p { font-size: 12px; color: #8391a2; margin: 8px 0 0; line-height: 1.5; }
        .token code { background: #e9ecf3; border-radius: 3px; padding: 1px 4px; }
        .sso { text-align: center; margin: 18px 0 0; font-size: 13px; }
        .sso a { color: #727cf5; text-decoration: none; }
    </style>
</head>
<body>
    <div class="card">
        <h1>{{ __('Mobile Development Login') }}</h1>
        <p class="subtitle">{{ config('app.name') }} — {{ __('employee app') }}</p>

        <div class="banner">
            {!! __('development_mode_banner', [
                'flag' => '<strong>DEVELOPMENT_MODE</strong>',
                'code' => '<code>DEVELOPMENT_MODE</code>',
                'env' => '<code>.env</code>',
            ]) !!}
        </div>

        <div class="banner info">
            {!! __('dev_mobile_gate_banner', [
                'shortcut' => '<strong>F12 → Ctrl+Shift+M</strong>',
            ]) !!}
        </div>

        @if ($errors->any())
            <div class="errors">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if (session('devToken'))
            <div class="token">
                <strong>{{ __('Bearer token') }}</strong>
                <textarea readonly onclick="this.select()">{{ session('devToken') }}</textarea>
                <p>
                    {!! __('dev_mobile_token_hint', [
                        'storage' => '<code>sessionStorage.setItem(\'token\', \'…\')</code>',
                    ]) !!}
                </p>
            </div>
        @endif

        <form method="POST" action="{{ route('dev.mobile.login.store') }}">
            @csrf

            <label for="identifier">{{ __('Employee ID / Email') }}</label>
            <input type="text" id="identifier" name="identifier" value="{{ old('identifier') }}"
                   autofocus autocomplete="username" required>
            <p class="hint">{{ __('The employee whose session you want to borrow.') }}</p>

            <div class="remember">
                <input type="checkbox" id="show_token" name="show_token" value="1">
                <label for="show_token">{{ __('Show the token instead of opening the app') }}</label>
            </div>

            <button type="submit">{{ __('Open the employee app') }}</button>
        </form>

        <p class="sso">
            <a href="{{ route('login') }}">{{ __('Go to the admin development login') }}</a>
        </p>
    </div>
</body>
</html>
