<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Laravel CAS Workbench</title>
    <style>
        :root {
            color-scheme: light;
            font-family: "Iowan Old Style", "Palatino Linotype", "Book Antiqua", serif;
            --bg: #f5f1e8;
            --card: #fffdf8;
            --ink: #1e2430;
            --muted: #5c6775;
            --accent: #0d4c92;
            --accent-2: #9c3d10;
            --line: #d8cdb8;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            background:
                radial-gradient(circle at top left, rgba(13, 76, 146, 0.12), transparent 34%),
                radial-gradient(circle at bottom right, rgba(156, 61, 16, 0.14), transparent 30%),
                var(--bg);
            color: var(--ink);
        }

        main {
            max-width: 960px;
            margin: 0 auto;
            padding: 64px 24px 80px;
        }

        .card {
            background: var(--card);
            border: 1px solid var(--line);
            border-radius: 24px;
            padding: 32px;
            box-shadow: 0 24px 60px rgba(30, 36, 48, 0.08);
        }

        h1 {
            margin: 0 0 12px;
            font-size: clamp(2.4rem, 5vw, 4.5rem);
            line-height: 0.95;
        }

        p {
            color: var(--muted);
            font-size: 1.1rem;
            line-height: 1.6;
        }

        .actions {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 24px;
        }

        .button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 12px 18px;
            border-radius: 999px;
            text-decoration: none;
            font-weight: 600;
            border: 1px solid transparent;
        }

        .button.primary {
            background: var(--accent);
            color: #fff;
        }

        .button.secondary {
            background: transparent;
            color: var(--accent);
            border-color: rgba(13, 76, 146, 0.28);
        }

        .status {
            margin-top: 28px;
            padding-top: 24px;
            border-top: 1px solid var(--line);
        }

        .status-label {
            font-size: 0.85rem;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: var(--accent-2);
        }

        .status-value {
            margin-top: 6px;
            font-size: 1.4rem;
        }
    </style>
</head>
<body>
<main>
    <section class="card">
        <h1>Laravel CAS Workbench</h1>
        <p>
            This local host app exercises the package with a real browser flow, a real callback route,
            and a CAS protocol test server instead of demo mode shortcuts.
        </p>

        <div class="actions">
            <a class="button primary" href="{{ route('dashboard') }}">Open dashboard</a>
            @if ($user)
                <a class="button secondary" href="{{ route('laravel-cas-logout') }}">Log out</a>
            @else
                <a class="button secondary" href="{{ route('laravel-cas-login') }}">Log in</a>
            @endif
        </div>

        <div class="status">
            <div class="status-label">Session</div>
            <div class="status-value">
                @if ($user)
                    Authenticated as {{ $user->email }}
                @else
                    Guest
                @endif
            </div>
        </div>
    </section>
</main>
</body>
</html>
