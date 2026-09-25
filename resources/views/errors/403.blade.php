<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>403 Forbidden</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-gray-100 font-sans">
        <div class="min-h-screen flex items-center justify-center px-4">
            <div class="max-w-xl w-full bg-white shadow rounded-lg p-8 text-center">
                <h1 class="text-3xl font-semibold mb-4 text-gray-900">403 — Forbidden</h1>
                <p class="text-gray-700 mb-6">You do not have permission to access this resource.</p>
                <div class="flex justify-center gap-3">
                    <a href="{{ url()->previous() ?: route('inventory.dashboard') }}" class="inline-block px-4 py-2 bg-blue-600 text-white rounded">Go back</a>
                    <a href="{{ route('dashboard') }}" class="inline-block px-4 py-2 bg-gray-200 text-gray-800 rounded">Dashboard</a>
                </div>
            </div>
        </div>
    </body>
</html>
