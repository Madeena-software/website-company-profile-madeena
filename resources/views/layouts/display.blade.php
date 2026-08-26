<!DOCTYPE html>
<html lang="id" class="antialiased">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Live Display | PT Madeena Karya Indonesia' }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo-current.png') }}">
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    @livewireStyles
</head>
<body class="font-sans bg-slate-950 text-white min-h-screen overflow-hidden selection:bg-madeena-teal selection:text-white">
    {{ $slot }}
    @livewireScripts
</body>
</html>
