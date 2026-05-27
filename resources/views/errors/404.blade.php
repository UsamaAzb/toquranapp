<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Page Not Found | To Quran</title>
  <style>
    :root {
      color-scheme: light;
      --ink: #2f3349;
      --muted: #6d7282;
      --brand: #1b365d;
      --soft: #f5f6fb;
      --line: #e5e7ef;
    }

    body {
      margin: 0;
      min-height: 100vh;
      display: grid;
      place-items: center;
      background: radial-gradient(circle at top left, #eef4ff 0, transparent 34%), var(--soft);
      color: var(--ink);
      font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
    }

    .not-found-card {
      width: min(520px, calc(100% - 32px));
      padding: 40px;
      border: 1px solid var(--line);
      border-radius: 20px;
      background: #fff;
      box-shadow: 0 14px 40px rgba(47, 51, 73, .10);
      text-align: center;
    }

    .not-found-card img {
      height: 42px;
      width: auto;
      margin-bottom: 24px;
    }

    .not-found-card h1 {
      margin: 0 0 10px;
      font-size: clamp(28px, 5vw, 40px);
      line-height: 1.1;
    }

    .not-found-card p {
      margin: 0 auto 28px;
      max-width: 360px;
      color: var(--muted);
      line-height: 1.6;
    }

    .not-found-card a {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      min-height: 42px;
      padding: 0 18px;
      border-radius: 10px;
      background: var(--brand);
      color: #fff;
      font-weight: 700;
      text-decoration: none;
    }
  </style>
</head>
<body>
  <main class="not-found-card">
    <img src="{{ asset('assets/img/logo/logo.png') }}" alt="To Quran">
    <h1>Page Not Found</h1>
    <p>The page may have moved, or this browser tab may belong to a previous login session.</p>
    <a href="{{ route('login') }}">Back to Login</a>
  </main>
</body>
</html>
