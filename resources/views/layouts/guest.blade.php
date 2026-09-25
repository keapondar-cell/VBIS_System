<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Victor Bernal Provincial High School</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="guest-background min-h-screen flex flex-col sm:justify-center items-center px-4 py-8 sm:px-6 sm:py-10">
            <div>
                <a href="/">
                    <img src="/images/school-logo.jpg" alt="Victor Bernal Provincial High School logo" class="h-24 w-24 rounded-full object-cover drop-shadow-sm" />
                </a>
            </div>

            <div class="guest-login-card w-full sm:max-w-md mt-6 px-6 py-4 bg-white/95 shadow-2xl ring-1 ring-white/70 overflow-hidden sm:rounded-lg">
                {{ $slot }}
            </div>
        </div>
        <style>
            .guest-background {
                position: relative;
                isolation: isolate;
                background-image: linear-gradient(rgba(8, 37, 58, .48), rgba(8, 37, 58, .48)), url('/images/school-building.jfif');
                background-size: cover;
                background-position: center;
                background-attachment: fixed;
            }
            .guest-background::before {
                content: "";
                position: absolute;
                inset: 0;
                background: rgba(7, 35, 54, .12);
                z-index: -1;
            }
            .guest-login-card { backdrop-filter: blur(4px); }
            @media (max-width: 640px) {
                .guest-background { background-attachment: scroll; background-position: center; }
            }
        </style>
    </body>
</html>
