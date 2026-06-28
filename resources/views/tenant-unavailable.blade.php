<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Demo Unavailable</title>
        <style>
            body {
                margin: 0;
                min-height: 100vh;
                display: grid;
                place-items: center;
                font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
                background: #f8fafc;
                color: #0f172a;
            }
            main {
                width: min(92vw, 560px);
                padding: 32px;
                background: #ffffff;
                border: 1px solid #e2e8f0;
                border-radius: 8px;
                box-shadow: 0 18px 50px rgba(15, 23, 42, .08);
            }
            h1 {
                margin: 0 0 12px;
                font-size: 28px;
                line-height: 1.2;
            }
            p {
                margin: 0;
                color: #475569;
                line-height: 1.6;
            }
        </style>
    </head>
    <body>
        <main>
            <h1>Demo unavailable</h1>
            <p>{{ $tenant->name }} is currently inactive, suspended, or past its demo expiry date. Please contact the site administrator to reactivate this demo.</p>
        </main>
    </body>
</html>
