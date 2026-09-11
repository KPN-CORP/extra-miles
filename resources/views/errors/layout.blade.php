<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title') &middot; {{ config('app.name', 'Extra Miles') }}</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    {{-- Error pages are intentionally self-contained: no Vite manifest, no CDN.
         They must render even when the build or the network is broken. --}}
    <style>
        :root {
            --em-primary: #AB2F2B;
            --em-primary-dark: #8C2522;
            --em-primary-soft: rgba(171, 47, 43, .10);
            --em-primary-ring: rgba(171, 47, 43, .05);
            --em-accent: #DEBD69;
            --em-bg: #FBF7F4;
            --em-surface: #FFFFFF;
            --em-text: #1F2426;
            --em-muted: #6B7280;
            --em-border: #ECE4DE;
            --em-shadow: 0 18px 50px -22px rgba(31, 36, 38, .35);
        }

        @media (prefers-color-scheme: dark) {
            :root {
                --em-primary: #D9615C;
                --em-primary-dark: #C24C47;
                --em-primary-soft: rgba(217, 97, 92, .14);
                --em-primary-ring: rgba(217, 97, 92, .07);
                --em-bg: #16181A;
                --em-surface: #1F2224;
                --em-text: #F3F1EF;
                --em-muted: #A3A9AE;
                --em-border: #2F3336;
                --em-shadow: 0 18px 50px -22px rgba(0, 0, 0, .7);
            }
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            height: 100%;
        }

        body {
            margin: 0;
            padding: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--em-bg);
            background-image:
                radial-gradient(circle at 12% 0%, rgba(171, 47, 43, .10), transparent 45%),
                radial-gradient(circle at 88% 100%, rgba(222, 189, 105, .16), transparent 45%);
            color: var(--em-text);
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            font-size: 16px;
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
        }

        .error-card {
            width: 100%;
            max-width: 520px;
            padding: 40px 32px 32px;
            border: 1px solid var(--em-border);
            border-radius: 20px;
            background: var(--em-surface);
            box-shadow: var(--em-shadow);
            text-align: center;
            animation: rise .45s cubic-bezier(.21, .61, .35, 1) both;
        }

        @keyframes rise {
            from {
                opacity: 0;
                transform: translateY(14px);
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .error-card {
                animation: none;
            }
        }

        .error-badge {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 88px;
            height: 88px;
            margin: 0 auto 26px;
            border-radius: 50%;
            color: var(--em-primary);
            background: var(--em-primary-soft);
            box-shadow: 0 0 0 10px var(--em-primary-ring);
        }

        .error-badge svg {
            width: 42px;
            height: 42px;
        }

        .error-code {
            display: inline-block;
            margin-bottom: 12px;
            padding: 4px 12px;
            border-radius: 999px;
            background: var(--em-primary-soft);
            color: var(--em-primary);
            font-size: 13px;
            font-weight: 700;
            letter-spacing: .14em;
            text-transform: uppercase;
        }

        .error-title {
            margin: 0 0 10px;
            font-size: 26px;
            line-height: 1.25;
            font-weight: 700;
            letter-spacing: -.01em;
        }

        .error-text {
            margin: 0 auto;
            max-width: 40ch;
            color: var(--em-muted);
            font-size: 15px;
        }

        .error-detail {
            margin: 20px auto 0;
            padding: 10px 14px;
            max-width: 100%;
            border-radius: 10px;
            border: 1px dashed var(--em-border);
            color: var(--em-muted);
            font-size: 13px;
            word-break: break-word;
        }

        .error-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            justify-content: center;
            margin-top: 28px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            min-width: 148px;
            padding: 11px 22px;
            border: 1px solid transparent;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: background-color .15s ease, border-color .15s ease, transform .15s ease;
        }

        .btn:focus-visible {
            outline: 2px solid var(--em-primary);
            outline-offset: 3px;
        }

        .btn:active {
            transform: translateY(1px);
        }

        .btn-primary {
            background: var(--em-primary);
            color: #fff;
        }

        .btn-primary:hover {
            background: var(--em-primary-dark);
        }

        .btn-ghost {
            background: transparent;
            border-color: var(--em-border);
            color: var(--em-text);
        }

        .btn-ghost:hover {
            border-color: var(--em-primary);
            color: var(--em-primary);
        }

        .error-footer {
            margin-top: 28px;
            padding-top: 20px;
            border-top: 1px solid var(--em-border);
            color: var(--em-muted);
            font-size: 13px;
        }

        @media (max-width: 420px) {
            .error-card {
                padding: 32px 20px 24px;
                border-radius: 16px;
            }

            .error-title {
                font-size: 22px;
            }

            .btn {
                width: 100%;
            }
        }
    </style>
</head>

<body>
    <main class="error-card" role="main">
        <div class="error-badge" aria-hidden="true">
            @yield('icon')
        </div>

        <span class="error-code">{{ __('Error') }} @yield('code')</span>
        <h1 class="error-title">@yield('title')</h1>
        <p class="error-text">@yield('message')</p>

        {{-- Only pages that opt in render a detail line, so a raw 5xx exception
             message is never echoed back to the browser. --}}
        @hasSection('detail')
            <p class="error-detail">@yield('detail')</p>
        @endif


        <div class="error-actions">
            <a href="{{ url('/') }}" class="btn btn-primary">{{ __('Back to Home') }}</a>
            <button type="button" class="btn btn-ghost" onclick="history.back()">{{ __('Go Back') }}</button>
        </div>

        <p class="error-footer">{{ __('If the problem keeps happening, please contact your administrator.') }}</p>
    </main>
</body>

</html>
