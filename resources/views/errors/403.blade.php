<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Access Restricted — Pilar Shrine Parish Portal</title>
    <link rel="icon" href="/images/pilar-shrine-logo.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/portal-theme.css'])
    <style>
        :root {
            --font-primary: 'Manrope', sans-serif;
            --font-heading: 'Manrope', sans-serif;
            --font-body: 'Manrope', sans-serif;
            --navy: #062f78;
            --navy-dark: #041e4d;
            --navy-light: #0d4399;
            --blue: #155cb4;
            --gold: #d8aa3c;
            --gold-light: #fef8eb;
            --ink: #0f172a;
            --muted: #64748b;
            --border: #e2e8f0;
            --bg: #f8fafc;
            --surface: #ffffff;
            --radius-lg: 16px;
            --shadow-lg: 0 16px 36px rgba(6, 47, 120, 0.09);
        }

        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: var(--font-body);
            background: linear-gradient(135deg, #f0f4f9 0%, #e2e8f0 100%);
            color: var(--ink);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }

        .error-card {
            background: var(--surface);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-lg);
            border: 1px solid var(--border);
            max-width: 520px;
            width: 100%;
            overflow: hidden;
            text-align: center;
        }

        .error-header {
            background: linear-gradient(135deg, var(--navy-dark) 0%, var(--navy) 100%);
            padding: 36px 24px 30px;
            position: relative;
        }

        .error-header::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--gold) 0%, #fbd561 50%, var(--gold) 100%);
        }

        .brand-logo {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            background: #ffffff;
            padding: 6px;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.25);
            margin: 0 auto 16px;
            display: block;
        }

        .error-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(216, 170, 60, 0.2);
            color: #ffd875;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            padding: 4px 12px;
            border-radius: 9999px;
            border: 1px solid rgba(216, 170, 60, 0.35);
        }

        .error-body {
            padding: 36px 32px 32px;
        }

        .error-title {
            font-family: var(--font-heading);
            font-size: 24px;
            font-weight: 700;
            color: var(--navy-dark);
            margin-bottom: 12px;
            line-height: 1.3;
        }

        .error-message {
            font-size: 14px;
            line-height: 1.6;
            color: var(--muted);
            margin-bottom: 24px;
        }

        .error-details-box {
            background: #fff8eb;
            border: 1px solid #fde68a;
            border-radius: 10px;
            padding: 12px 16px;
            font-size: 13px;
            color: #92400e;
            margin-bottom: 28px;
            display: flex;
            align-items: center;
            gap: 10px;
            text-align: left;
        }

        .error-details-box svg {
            flex-shrink: 0;
        }

        .actions-wrap {
            display: flex;
            gap: 12px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            font-size: 13px;
            font-weight: 600;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s ease;
            border: none;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--navy) 0%, var(--blue) 100%);
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(6, 47, 120, 0.25);
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, var(--navy-dark) 0%, var(--navy) 100%);
            transform: translateY(-1px);
        }

        .btn-outline {
            background: #ffffff;
            color: var(--ink);
            border: 1px solid var(--border);
        }

        .btn-outline:hover {
            background: #f1f5f9;
            border-color: #cbd5e1;
        }

        .error-footer {
            margin-top: 24px;
            padding-top: 20px;
            border-top: 1px solid #f1f5f9;
            font-size: 11px;
            color: #94a3b8;
        }

        .error-footer a {
            color: var(--blue);
            text-decoration: none;
            font-weight: 600;
        }

        .error-footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <main class="error-card" role="alert">
        <header class="error-header">
            <img src="/images/pilar-shrine-logo.png" alt="Our Lady of the Pillar Parish Shrine Logo" class="brand-logo">
            <span class="error-badge">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                    <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                </svg>
                HTTP 403 · Access Restricted
            </span>
        </header>

        <section class="error-body">
            <h1 class="error-title">Access Restricted</h1>
            <p class="error-message">
                You do not have the required permission to access this module or perform this action.
                Access in the parish portal is strictly governed by your assigned role and configured permissions.
            </p>

            @php
                $detailMessage = $exception?->getMessage();
            @endphp
            @if($detailMessage && !in_array($detailMessage, ['This action is unauthorized.', 'Unauthorized.'], true))
            <div class="error-details-box">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="8" x2="12" y2="12"></line>
                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                </svg>
                <span>{{ $detailMessage }}</span>
            </div>
            @endif

            <div class="actions-wrap">
                <button type="button" class="btn btn-outline" onclick="window.history.back()">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                        <line x1="19" y1="12" x2="5" y2="12"></line>
                        <polyline points="12 19 5 12 12 5"></polyline>
                    </svg>
                    Go Back
                </button>
                <a href="{{ route('portal') }}" class="btn btn-primary">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                        <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                        <polyline points="9 22 9 12 15 12 15 22"></polyline>
                    </svg>
                    Return to Portal
                </a>
            </div>

            <footer class="error-footer">
                Diocesan Shrine &amp; Parish of Our Lady of the Pillar · Pilar, Sorsogon
            </footer>
        </section>
    </main>
</body>
</html>

