<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CAS Dashboard</title>
    <style>
        :root {
            color-scheme: light;
            font-family: "Iowan Old Style", "Palatino Linotype", "Book Antiqua", serif;
            --bg: #eef2f4;
            --panel: #fcfbf8;
            --ink: #1d2430;
            --muted: #5d6979;
            --line: #d3dbe2;
            --accent: #8b2f1d;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            background:
                linear-gradient(135deg, rgba(139, 47, 29, 0.08), transparent 28%),
                linear-gradient(315deg, rgba(13, 76, 146, 0.1), transparent 34%),
                var(--bg);
            color: var(--ink);
        }

        main {
            max-width: 1080px;
            margin: 0 auto;
            padding: 48px 24px 80px;
        }

        header {
            display: flex;
            justify-content: space-between;
            gap: 16px;
            align-items: flex-start;
            margin-bottom: 28px;
        }

        h1 {
            margin: 0 0 10px;
            font-size: clamp(2.2rem, 5vw, 4rem);
            line-height: 0.95;
        }

        p {
            margin: 0;
            color: var(--muted);
            line-height: 1.6;
        }

        .actions {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 12px 18px;
            border-radius: 999px;
            text-decoration: none;
            font-weight: 600;
            border: 1px solid rgba(29, 36, 48, 0.14);
            color: var(--ink);
            background: rgba(255, 255, 255, 0.7);
        }

        .grid {
            display: grid;
            gap: 20px;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        }

        .panel {
            background: var(--panel);
            border: 1px solid var(--line);
            border-radius: 22px;
            padding: 24px;
            box-shadow: 0 18px 42px rgba(29, 36, 48, 0.08);
        }

        .eyebrow {
            margin: 0 0 10px;
            font-size: 0.82rem;
            text-transform: uppercase;
            letter-spacing: 0.12em;
            color: var(--accent);
        }

        dl {
            margin: 0;
            display: grid;
            gap: 10px;
        }

        dt {
            color: var(--muted);
            font-size: 0.92rem;
        }

        dd {
            margin: 0;
            font-size: 1.06rem;
        }

        code {
            font-family: "SFMono-Regular", Menlo, Consolas, monospace;
            font-size: 0.92rem;
        }
    </style>
</head>
<body>
<main>
    <header>
        <div>
            <h1>CAS Dashboard</h1>
            <p>The user below was created from the CAS ticket validation response.</p>
        </div>

        <div class="actions">
            <a class="button" href="{{ route('home') }}">Home</a>
            <a class="button" href="{{ route('whoami') }}">whoami</a>
            <a class="button" href="{{ route('laravel-cas-logout') }}">Log out</a>
        </div>
    </header>

    <section class="grid">
        <article class="panel">
            <p class="eyebrow">Identity</p>
            <dl>
                <div>
                    <dt>Name</dt>
                    <dd>{{ $user->name }}</dd>
                </div>
                <div>
                    <dt>Email</dt>
                    <dd>{{ $user->email }}</dd>
                </div>
            </dl>
        </article>

        <article class="panel">
            <p class="eyebrow">Mapped Attributes</p>
            <dl>
                <div>
                    <dt>departmentNumber</dt>
                    <dd>{{ $user->departmentNumber ?? 'null' }}</dd>
                </div>
                <div>
                    <dt>department_number</dt>
                    <dd>{{ $user->department_number ?? 'null' }}</dd>
                </div>
                <div>
                    <dt>organisation</dt>
                    <dd>{{ $user->organisation ?? 'null' }}</dd>
                </div>
            </dl>
        </article>

        <article class="panel">
            <p class="eyebrow">Routes Under Test</p>
            <dl>
                <div>
                    <dt>Login</dt>
                    <dd><code>{{ route('laravel-cas-login') }}</code></dd>
                </div>
                <div>
                    <dt>Callback</dt>
                    <dd><code>{{ route('laravel-cas-callback') }}</code></dd>
                </div>
                <div>
                    <dt>Logout</dt>
                    <dd><code>{{ route('laravel-cas-logout') }}</code></dd>
                </div>
            </dl>
        </article>
    </section>
</main>
</body>
</html>
