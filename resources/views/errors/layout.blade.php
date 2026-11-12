<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('titre') — Suivi des dossiers</title>
    <style>
        :root { color-scheme: light dark; }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            font-family: Figtree, ui-sans-serif, system-ui, -apple-system, "Segoe UI", sans-serif;
            background: #f8fafc;
            color: #1f2937;
        }
        .carte {
            width: 100%;
            max-width: 560px;
            background: #fff;
            border: 1px solid #e5e7eb;
            border-top: 4px solid #4f46e5;
            border-radius: 12px;
            padding: 40px 36px;
            box-shadow: 0 10px 25px -12px rgba(15, 23, 42, .35);
        }
        .code {
            margin: 0;
            font-size: 48px;
            font-weight: 700;
            letter-spacing: -2px;
            color: #4f46e5;
            line-height: 1;
        }
        h1 { margin: 12px 0 10px; font-size: 20px; }
        p { margin: 0 0 22px; font-size: 15px; line-height: 1.6; color: #4b5563; }
        .actions { display: flex; flex-wrap: wrap; gap: 10px; }
        a {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 9px 16px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            background: #4f46e5;
            color: #fff;
        }
        a.secondaire { background: #fff; color: #374151; border: 1px solid #d1d5db; }
        a:focus-visible { outline: 3px solid #c7d2fe; outline-offset: 2px; }
        @media (prefers-color-scheme: dark) {
            body { background: #111827; color: #f3f4f6; }
            .carte { background: #1f2937; border-color: #374151; }
            p { color: #d1d5db; }
            a.secondaire { background: #1f2937; color: #e5e7eb; border-color: #4b5563; }
        }
    </style>
</head>
<body>
    <main class="carte">
        <p class="code">@yield('code')</p>
        <h1>@yield('titre')</h1>
        <p>@yield('message')</p>
        <div class="actions">
            @auth
                <a href="{{ route('dashboard') }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" style="width:16px;height:16px" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12 11.54 2.71a.75.75 0 0 1 1.06 0l9.29 9.29M4.5 9.75v10.5a.75.75 0 0 0 .75.75h3.75a.75.75 0 0 0 .75-.75V15a.75.75 0 0 1 .75-.75h3a.75.75 0 0 1 .75.75v5.25a.75.75 0 0 0 .75.75h3.75a.75.75 0 0 0 .75-.75V9.75" /></svg>
                    Retour au tableau de bord
                </a>
            @endauth
            <a href="{{ url('/') }}" class="secondaire">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" style="width:16px;height:16px" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
                Retour à l'accueil
            </a>
        </div>
    </main>
</body>
</html>
