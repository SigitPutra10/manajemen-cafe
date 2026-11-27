@props(['title' => null])

@php
    $resolvedCafeName = $title ?? (\App\Models\Setting::query()->value('cafe_name') ?: config('app.name', 'Laravel'));
@endphp

<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Masuk - {{ $resolvedCafeName }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-[#F7F5F0] text-[#1C1917] antialiased">
        <main class="flex min-h-screen flex-col items-center justify-center px-4 py-8 sm:px-6">
            <div class="w-full max-w-md rounded-lg border border-[#E2DDD5] bg-white p-6 sm:p-8" style="box-shadow: 0 1px 2px rgba(28, 25, 23, 0.05);">
                {{ $slot }}
            </div>
        </main>
    </body>
</html>
