<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{{ $title ?? 'My Islands' }}</title>
    <link rel="icon" type="image/x-icon" href="https://app.toquran.org/assets/img/favicon/favicon.ico">
<link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">

  @vite(['resources/css/app.css','resources/js/app.js'])
  <style>
      body{
          font-family: "Public Sans", -apple-system, blinkmacsystemfont, "Segoe UI", "Oxygen", "Ubuntu", "Cantarell", "Fira Sans", "Droid Sans", "Helvetica Neue", sans-serif !important;

      }
  </style>
  @livewireStyles
</head>
<body>
  {{ $slot }}

  @livewireScripts
</body>
</html>
