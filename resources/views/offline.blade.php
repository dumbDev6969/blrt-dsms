<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Offline — BLRT-DSMS</title>
    <style>
        /* Inline-only styles — no external assets available offline */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --bg:        #0f172a;  /* slate-950 */
            --surface:   #1e293b;  /* slate-900 */
            --border:    #334155;  /* slate-700 */
            --text:      #e2e8f0;  /* slate-200 */
            --muted:     #94a3b8;  /* slate-400 */
            --accent:    #3b82f6;  /* blue-500  */
            --accent-hl: #60a5fa;  /* blue-400  */
        }

        html, body {
            height: 100%;
            background-color: var(--bg);
            color: var(--text);
            font-family: 'Instrument Sans', ui-sans-serif, system-ui, -apple-system, sans-serif;
            -webkit-font-smoothing: antialiased;
        }

        body {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100dvh;
            padding: 1.5rem;
            text-align: center;
        }

        .card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 1rem;
            padding: 2.5rem 2rem;
            max-width: 26rem;
            width: 100%;
            box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5);
        }

        .icon-wrap {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 4rem;
            height: 4rem;
            border-radius: 0.75rem;
            background: rgba(59,130,246,0.12);
            border: 1px solid rgba(59,130,246,0.25);
            margin: 0 auto 1.5rem;
        }

        .icon-wrap svg {
            width: 2rem;
            height: 2rem;
            color: var(--accent-hl);
        }

        h1 {
            font-size: 1.375rem;
            font-weight: 600;
            color: var(--text);
            margin-bottom: 0.5rem;
            letter-spacing: -0.01em;
        }

        p {
            font-size: 0.9375rem;
            color: var(--muted);
            line-height: 1.6;
            margin-bottom: 1.75rem;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            background: var(--accent);
            color: #fff;
            border: none;
            border-radius: 0.5rem;
            padding: 0.625rem 1.25rem;
            font-size: 0.9375rem;
            font-weight: 500;
            font-family: inherit;
            cursor: pointer;
            transition: background 0.15s ease, transform 0.1s ease;
            text-decoration: none;
            width: 100%;
        }

        .btn:hover { background: #2563eb; }
        .btn:active { transform: scale(0.98); }

        .brand {
            margin-top: 2rem;
            font-size: 0.75rem;
            color: var(--border);
            letter-spacing: 0.05em;
            text-transform: uppercase;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon-wrap">
            {{-- Wifi-off SVG — inline, no external dependencies --}}
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                 stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M3 3l18 18M8.111 8.111A5.5 5.5 0 0 1 12 7c1.657 0 3.156.693 4.243 1.808M15.536 15.536A3.5 3.5 0 0 1 12 17a3.5 3.5 0 0 1-2.475-1.025M1.5 1.5C3.993 3.498 6.942 5 10.148 5.71M22.5 6A16.455 16.455 0 0 0 12 2c-1.61 0-3.174.23-4.653.66M12 21h.008" />
            </svg>
        </div>

        <h1>You're offline</h1>
        <p>
            It looks like you lost your internet connection.<br>
            Check your network and try again.
        </p>

        <button class="btn" onclick="window.location.reload()">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                 stroke="currentColor" stroke-width="2" style="width:1rem;height:1rem" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
            </svg>
            Try again
        </button>

        <p class="brand">BLRT-DSMS</p>
    </div>
</body>
</html>
