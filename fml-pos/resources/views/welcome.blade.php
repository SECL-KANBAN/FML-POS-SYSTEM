<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#08070b">
    <title>{{ config('app.name', 'FML POS') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <style>
        :root {
            color-scheme: dark;
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            font-synthesis: none;
            text-rendering: optimizeLegibility;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-width: 320px;
            min-height: 100vh;
            background: #08070b;
        }

        .welcome {
            display: grid;
            grid-template-columns: minmax(0, 2fr) minmax(340px, 1fr);
            min-height: 100vh;
            min-height: 100svh;
        }

        .art-panel {
            position: relative;
            display: block;
            min-height: 100vh;
            min-height: 100svh;
            overflow: hidden;
            background: #350553;
        }

        .pos-art {
            position: absolute;
            inset: 0;
            display: block;
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .auth-panel {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: clamp(32px, 5vw, 76px);
            background: #08070b;
        }

        .auth-panel::before {
            position: absolute;
            top: 0;
            right: 0;
            width: 0;
            height: 0;
            border-top: 30px solid #7e22ce;
            border-left: 24px solid transparent;
            content: "";
        }

        .auth-content {
            width: min(100%, 360px);
        }

        .brand {
            display: inline-flex;
            align-items: center;
            gap: 11px;
            color: #f5f3f7;
            font-size: 14px;
            font-weight: 750;
            letter-spacing: .12em;
            text-decoration: none;
            text-transform: uppercase;
        }

        .brand-mark {
            display: grid;
            width: 36px;
            height: 36px;
            place-items: center;
            border: 1px solid rgb(216 180 254 / 35%);
            border-radius: 11px;
            background: linear-gradient(145deg, #a855f7, #6b21a8);
            box-shadow: 0 5px 20px rgb(147 51 234 / 25%);
        }

        .brand-mark svg {
            width: 20px;
            height: 20px;
        }

        h1 {
            margin: 48px 0 10px;
            color: #fff;
            font-size: clamp(30px, 3vw, 38px);
            font-weight: 700;
            letter-spacing: -.045em;
            line-height: 1.15;
        }

        .intro {
            margin: 0;
            color: #a6a1ad;
            font-size: 15px;
            line-height: 1.7;
        }

        .auth-actions {
            display: grid;
            gap: 12px;
            margin-top: 34px;
        }

        .auth-link {
            display: inline-flex;
            min-height: 50px;
            align-items: center;
            justify-content: center;
            border: 1px solid transparent;
            border-radius: 9px;
            font-size: 14px;
            font-weight: 650;
            text-decoration: none;
            transition: background-color 150ms ease, border-color 150ms ease, transform 150ms ease;
        }

        .auth-link:hover {
            transform: translateY(-1px);
        }

        .auth-link:focus-visible,
        .brand:focus-visible {
            outline: 2px solid #c084fc;
            outline-offset: 4px;
        }

        .auth-link-primary {
            background: #9333ea;
            color: white;
        }

        .auth-link-primary:hover {
            background: #a855f7;
        }

        .auth-link-secondary {
            border-color: #37323d;
            background: #111014;
            color: #e8e4ed;
        }

        .auth-link-secondary:hover {
            border-color: #6b21a8;
            background: #17121d;
        }

        .auth-footnote {
            margin: 28px 0 0;
            color: #77717f;
            font-size: 12px;
            line-height: 1.6;
        }

        @media (max-width: 760px) {
            .welcome {
                grid-template-columns: 1fr;
            }

            .art-panel {
                min-height: 48svh;
            }

            .pos-art {
                max-height: none;
                width: 100%;
                height: 100%;
            }

            .auth-panel {
                min-height: 52svh;
                padding: 44px 28px;
            }

            h1 {
                margin-top: 32px;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            *,
            *::before,
            *::after {
                scroll-behavior: auto !important;
                transition-duration: .01ms !important;
            }
        }
    </style>
</head>
<body>
    <main class="welcome">
        <section class="art-panel" aria-label="Point of sale banner">
            <img class="pos-art" src="{{ asset('welcome-banner.svg') }}" alt="Point-of-sale banner with a payment terminal, receipt, coins, card, and mobile checkout">
        </section>

        <section class="auth-panel" aria-labelledby="welcome-heading">
            <div class="auth-content">
                <a class="brand" href="{{ url('/') }}" aria-label="{{ config('app.name', 'FML POS') }} home">
                    <span class="brand-mark" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none">
                            <path d="M4 5.5h16v13H4zM8 9v6m0-6h4m-4 3h3m3 3v-6l2 3 2-3v6" stroke="white" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7"/>
                        </svg>
                    </span>
                    {{ config('app.name', 'FML POS') }}
                </a>

                @auth
                    <h1 id="welcome-heading">Welcome back.</h1>
                    <p class="intro">Continue to your point of sale dashboard.</p>
                    <div class="auth-actions">
                        <a class="auth-link auth-link-primary" href="{{ route('dashboard') }}">Go to dashboard</a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button class="auth-link auth-link-secondary" type="submit" style="width: 100%; cursor: pointer; font: inherit;">Sign out</button>
                        </form>
                    </div>
                @else
                    <h1 id="welcome-heading">Checkout, made simple.</h1>
                    <p class="intro">Manage products, complete sales, and keep every transaction in one place.</p>
                    <div class="auth-actions">
                        @if (Route::has('login'))
                            <a class="auth-link auth-link-primary" href="{{ route('login') }}">Log in</a>
                        @endif
                        @if (Route::has('register'))
                            <a class="auth-link auth-link-secondary" href="{{ route('register') }}">Sign up</a>
                        @endif
                    </div>
                    <p class="auth-footnote">A smarter, simpler way to run your point of sale.</p>
                @endauth
            </div>
        </section>
    </main>
</body>
</html>
