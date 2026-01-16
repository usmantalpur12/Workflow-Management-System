<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign in · Workflow Management System</title>
    <link rel="stylesheet" href="{{ asset('css/modern.css') }}">
    <style>
        body {
            min-height: 100vh;
            margin: 0;
            display: flex;
            align-items: stretch;
            justify-content: center;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: radial-gradient(circle at top left, #1d4ed8 0, #0f172a 40%, #020617 80%);
            color: #f9fafb;
        }

        .login-shell {
            width: 100%;
            max-width: 1120px;
            margin: 2.5rem 1.25rem;
            border-radius: 24px;
            overflow: hidden;
            background: rgba(15, 23, 42, 0.9);
            box-shadow:
                0 24px 60px rgba(15, 23, 42, 0.65),
                0 0 0 1px rgba(148, 163, 184, 0.12);
            display: grid;
            grid-template-columns: minmax(0, 1.1fr) minmax(0, 1.2fr);
        }

        .login-hero {
            position: relative;
            padding: 2.5rem 2.25rem;
            background: radial-gradient(circle at top left, rgba(59, 130, 246, 0.28), transparent 55%),
                        radial-gradient(circle at bottom right, rgba(20, 184, 166, 0.24), transparent 55%),
                        linear-gradient(145deg, #020617, #020617);
            color: #e5e7eb;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            gap: 2rem;
        }

        .login-hero-header {
            display: flex;
            align-items: center;
            gap: 0.85rem;
        }

        .login-hero-logo {
            width: 44px;
            height: 44px;
            border-radius: 14px;
            background: radial-gradient(circle at 30% 20%, #facc15, #f97316 45%, #ef4444 75%);
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 10px 25px rgba(248, 250, 252, 0.08);
        }

        .login-hero-logo img {
            width: 26px;
            height: 26px;
            object-fit: contain;
            filter: drop-shadow(0 4px 10px rgba(15, 23, 42, 0.85));
        }

        .login-hero-title {
            display: flex;
            flex-direction: column;
            gap: 0.15rem;
        }

        .login-hero-title h1 {
            font-size: 1.35rem;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            color: #e5e7eb;
        }

        .login-hero-title span {
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.16em;
            color: #9ca3af;
        }

        .login-hero-body h2 {
            font-size: 1.85rem;
            line-height: 1.3;
            font-weight: 700;
            margin-bottom: 0.75rem;
            color: #e5e7eb;
        }

        .login-hero-body p {
            color: #9ca3af;
            font-size: 0.95rem;
            max-width: 26rem;
        }

        .login-hero-metrics {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 1rem;
            margin-top: 1.5rem;
        }

        .metric {
            padding: 0.9rem 0.85rem;
            border-radius: 14px;
            background: radial-gradient(circle at top left, rgba(59, 130, 246, 0.18), transparent 55%),
                        rgba(15, 23, 42, 0.85);
            border: 1px solid rgba(148, 163, 184, 0.24);
        }

        .metric span {
            display: block;
        }

        .metric-label {
            font-size: 0.75rem;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: #9ca3af;
            margin-bottom: 0.2rem;
        }

        .metric-value {
            font-size: 1.1rem;
            font-weight: 600;
            color: #e5e7eb;
        }

        .login-hero-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            margin-top: 1rem;
            font-size: 0.8rem;
            color: #9ca3af;
        }

        .login-hero-footer img {
            max-height: 38px;
            opacity: 0.8;
        }

        .login-panel {
            padding: 2.5rem 2.25rem;
            background: #020617;
            color: #e5e7eb;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .login-panel-inner {
            max-width: 420px;
            margin-left: auto;
            margin-right: auto;
        }

        .login-heading {
            margin-bottom: 1.75rem;
        }

        .login-heading h2 {
            font-size: 1.65rem;
            font-weight: 700;
            margin-bottom: 0.4rem;
            letter-spacing: -0.02em;
        }

        .login-heading p {
            color: #9ca3af;
            font-size: 0.9rem;
        }

        .login-heading p span {
            color: #e5e7eb;
            font-weight: 500;
        }

        .alert {
            border-radius: 12px;
            padding: 0.8rem 0.9rem;
            font-size: 0.88rem;
            margin-bottom: 1.2rem;
        }

        .alert ul {
            margin-bottom: 0;
            padding-left: 1.1rem;
        }

        .form-group {
            margin-bottom: 1.1rem;
        }

        .form-label {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 0.9rem;
            color: #d1d5db;
            margin-bottom: 0.25rem;
        }

        .form-label span {
            font-size: 0.8rem;
            color: #9ca3af;
        }

        .form-control {
            width: 100%;
            border-radius: 10px;
            border: 1px solid rgba(148, 163, 184, 0.55);
            padding: 0.7rem 0.9rem;
            font-size: 0.95rem;
            background: rgba(15, 23, 42, 0.9);
            color: #e5e7eb;
            transition: border-color 0.15s ease, box-shadow 0.15s ease, background-color 0.15s ease, transform 0.1s ease;
        }

        .form-control::placeholder {
            color: #6b7280;
        }

        .form-control:focus {
            outline: none;
            border-color: #3b82f6;
            background: rgba(15, 23, 42, 0.95);
            box-shadow: 0 0 0 1px rgba(59, 130, 246, 0.55);
            transform: translateY(-1px);
        }

        .login-footer-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            margin-bottom: 1.2rem;
            font-size: 0.85rem;
        }

        .form-check {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
        }

        .form-check-input {
            width: 14px;
            height: 14px;
            border-radius: 4px;
            border: 1px solid rgba(148, 163, 184, 0.7);
            background-color: transparent;
        }

        .form-check-label {
            color: #9ca3af;
            font-size: 0.85rem;
        }

        .forgot-link {
            color: #93c5fd;
            text-decoration: none;
            font-size: 0.82rem;
        }

        .forgot-link:hover {
            text-decoration: underline;
        }

        .btn-primary {
            width: 100%;
            padding: 0.82rem;
            border-radius: 999px;
            border: none;
            background: linear-gradient(135deg, #2563eb, #4f46e5);
            color: #f9fafb;
            font-weight: 600;
            font-size: 0.98rem;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            box-shadow:
                0 14px 30px rgba(37, 99, 235, 0.55),
                0 0 0 1px rgba(147, 197, 253, 0.22);
            transition: transform 0.12s ease, box-shadow 0.12s ease, filter 0.12s ease;
        }

        .btn-primary:hover {
            filter: brightness(1.05);
            transform: translateY(-1px);
            box-shadow:
                0 18px 40px rgba(37, 99, 235, 0.65),
                0 0 0 1px rgba(147, 197, 253, 0.32);
        }

        .btn-primary:active {
            transform: translateY(0);
            box-shadow:
                0 10px 24px rgba(30, 64, 175, 0.7),
                0 0 0 1px rgba(129, 140, 248, 0.45);
        }

        .login-meta {
            margin-top: 1.1rem;
            font-size: 0.78rem;
            color: #6b7280;
            display: flex;
            justify-content: space-between;
            gap: 0.75rem;
            flex-wrap: wrap;
        }

        .pill {
            padding: 0.25rem 0.6rem;
            border-radius: 999px;
            background: rgba(15, 23, 42, 0.9);
            border: 1px solid rgba(148, 163, 184, 0.35);
            font-size: 0.78rem;
            color: #9ca3af;
        }

        @media (max-width: 900px) {
            .login-shell {
                grid-template-columns: minmax(0, 1fr);
                max-width: 540px;
            }

            .login-hero {
                display: none;
            }

            .login-panel {
                padding: 2rem 1.75rem;
            }
        }

        @media (max-width: 540px) {
            .login-shell {
                margin: 1.5rem 1rem;
                border-radius: 18px;
            }

            .login-panel {
                padding: 1.75rem 1.4rem;
            }
        }
    </style>
</head>
<body>
    <div class="login-shell">
        <div class="login-hero">
            <div>
                <div class="login-hero-header">
                    <div class="login-hero-logo">
                        <img src="{{ asset('soft-ui-dashboard-main/assets/img/logo-ct.png') }}" alt="Workflow Logo">
                    </div>
                    <div class="login-hero-title">
                        <h1>Workflow OS</h1>
                        <span>Attendance · Projects · HR</span>
                    </div>
                </div>
                <div class="login-hero-body">
                    <h2>Stay on top of your team’s workday.</h2>
                    <p>
                        Track attendance, manage projects, and keep HR operations aligned in a single,
                        modern dashboard designed for busy teams.
                    </p>
                    <div class="login-hero-metrics">
                        <div class="metric">
                            <span class="metric-label">On-time check‑ins</span>
                            <span class="metric-value">98%</span>
                        </div>
                        <div class="metric">
                            <span class="metric-label">Projects tracked</span>
                            <span class="metric-value">120+</span>
                        </div>
                        <div class="metric">
                            <span class="metric-label">Avg. response</span>
                            <span class="metric-value">1.4 min</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="login-hero-footer">
                <span>Secure by design · Role‑based access · Audit ready</span>
                <img src="{{ asset('soft-ui-dashboard-main/assets/img/illustrations/illustration-signin.jpg') }}" alt="Dashboard Preview">
            </div>
        </div>

        <div class="login-panel">
            <div class="login-panel-inner">
                <div class="login-heading">
                    <h2>Welcome back</h2>
                    <p>
                        Sign in to continue to <span>Workflow Management System</span>.
                    </p>
                </div>

                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('login') }}" method="POST" autocomplete="on">
                    @csrf
                    <div class="form-group">
                        <label for="email" class="form-label">
                            <span>Email address</span>
                            <span>Use your work email</span>
                        </label>
                        <input
                            type="email"
                            name="email"
                            id="email"
                            class="form-control"
                            required
                            autofocus
                            placeholder="e.g. superadmin@example.com"
                            value="{{ old('email') }}"
                        >
                    </div>

                    <div class="form-group">
                        <label for="password" class="form-label">
                            <span>Password</span>
                            <span>Default: <strong>password</strong></span>
                        </label>
                        <input
                            type="password"
                            name="password"
                            id="password"
                            class="form-control"
                            required
                            placeholder="Enter your password"
                        >
                    </div>

                    <div class="login-footer-row">
                        <div class="form-check">
                            <input type="checkbox" name="remember" id="remember" class="form-check-input">
                            <label for="remember" class="form-check-label">Remember this device</label>
                        </div>
                        <span class="forgot-link">Need help signing in?</span>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        Continue to dashboard
                    </button>

                    <div class="login-meta">
                        <span>Super Admin: superadmin@example.com</span>
                        <span class="pill">HR Admin: hradmin@example.com</span>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>