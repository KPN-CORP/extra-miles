<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Development Login') }} — {{ config('app.name') }}</title>
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
            max-width: 420px;
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
            margin-bottom: 20px;
        }
        label { display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px; }
        input[type=text], input[type=password] {
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
        .sso { text-align: center; margin: 18px 0 0; font-size: 13px; }
        .sso a { color: #727cf5; text-decoration: none; }
    </style>
</head>
<body>
    <div class="card">
        <h1>{{ __('Development Login') }}</h1>
        <p class="subtitle">{{ config('app.name') }} — {{ __('admin back-office') }}</p>

        <div class="banner">
            {!! __('development_mode_banner', [
                'flag' => '<strong>DEVELOPMENT_MODE</strong>',
                'code' => '<code>DEVELOPMENT_MODE</code>',
                'env' => '<code>.env</code>',
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

        <form method="POST" action="{{ route('dev.login.store') }}">
            @csrf

            <label for="identifier">{{ __('Employee ID / Email') }}</label>
            <input type="text" id="identifier" name="identifier" value="{{ old('identifier') }}"
                   autofocus autocomplete="username" required>

            <label for="password">{{ __('Password') }}</label>
            <input type="password" id="password" name="password" autocomplete="current-password">
            <p class="hint">{{ __('Leave blank — most SSO accounts have no password set.') }}</p>

            <div class="remember">
                <input type="checkbox" id="remember" name="remember" value="1">
                <label for="remember">{{ __('Remember me') }}</label>
            </div>

            <button type="submit">{{ __('Sign in') }}</button>
        </form>

        <p class="sso">
            <a href="https://kpncorporation.darwinbox.com">{{ __('Sign in with Darwinbox SSO instead') }}</a>
        </p>
    </div>
</body>
</html>
