<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{{ $title ?? config('variables.templateName', 'To Quran') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/img/favicon/favicon.png') }}">
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
