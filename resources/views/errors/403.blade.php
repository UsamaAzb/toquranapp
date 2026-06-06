<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Access Unavailable | To Quran</title>
  <link rel="icon" type="image/x-icon" href="{{ asset('assets/img/favicon/favicon.ico') }}">
  <style>
    * {
      box-sizing: border-box;
    }

    :root {
      color-scheme: light;
      --ink: #2f3349;
      --muted: #6d7282;
      --brand: #1b365d;
      --brand-soft: #eef4ff;
      --soft: #f5f6fb;
      --line: #e5e7ef;
      --warning: #f59e0b;
    }

    body {
      margin: 0;
      min-height: 100vh;
      min-height: 100dvh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: clamp(20px, 4vh, 48px) 24px;
      background: radial-gradient(circle at top left, #fff7ed 0, transparent 34%), var(--soft);
      color: var(--ink);
      font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
    }

    .access-card {
      width: min(520px, 100%);
      padding: 40px;
      border: 1px solid var(--line);
      border-radius: 20px;
      background: #fff;
      box-shadow: 0 14px 40px rgba(47, 51, 73, .10);
      text-align: center;
    }

    .access-card img {
      height: 42px;
      width: auto;
      margin-bottom: 24px;
    }

    .access-code {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      min-height: 30px;
      padding: 0 12px;
      margin-bottom: 16px;
      border-radius: 999px;
      background: rgba(245, 158, 11, .12);
      color: #9a5b00;
      font-size: 13px;
      font-weight: 700;
      letter-spacing: 0;
    }

    .access-card h1 {
      margin: 0 0 10px;
      font-size: clamp(28px, 5vw, 40px);
      line-height: 1.1;
    }

    .access-card p {
      margin: 0 auto 28px;
      max-width: 380px;
      color: var(--muted);
      line-height: 1.6;
    }

    .access-actions {
      display: flex;
      flex-wrap: wrap;
      justify-content: center;
      gap: 12px;
    }

    .access-actions form {
      display: flex;
      margin: 0;
    }

    .access-card a,
    .access-card button {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      min-height: 42px;
      min-width: 152px;
      padding: 0 18px;
      border: 0;
      border-radius: 10px;
      font: inherit;
      font-weight: 700;
      text-decoration: none;
      cursor: pointer;
    }

    .access-card .primary-action {
      background: var(--brand);
      color: #fff;
    }

    .access-card .secondary-action {
      border: 1px solid var(--line);
      background: var(--brand-soft);
      color: var(--brand);
    }

    @media (max-width: 480px) {
      body {
        padding: 16px;
      }

      .access-card {
        padding: 28px 20px;
        border-radius: 16px;
      }

      .access-card img {
        max-width: 220px;
        height: auto;
      }

      .access-card a,
      .access-card form,
      .access-card button {
        width: 100%;
      }
    }
  </style>
</head>
<body>
  @php
    $supportEmail = config('mail.support_address', 'support@toquran.org');
  @endphp

  <main class="access-card">
    <img src="{{ asset('assets/img/logo/logo.png') }}" alt="To Quran">
    <div class="access-code">403</div>
    <h1>Access Unavailable</h1>
    <p>{{ $exception->getMessage() ?: 'This page is not available for the current account.' }}</p>
    <div class="access-actions">
      @auth
        <form method="POST" action="{{ route('logout') }}" target="_top">
          @csrf
          <button class="primary-action" type="submit">Switch Account</button>
        </form>
      @else
        <a class="primary-action" href="{{ route('login') }}" target="_top">Go to Login</a>
      @endauth
      <a class="secondary-action" href="mailto:{{ $supportEmail }}" target="_top">Contact Support</a>
    </div>
  </main>
</body>
</html>
