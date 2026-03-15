<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Inventory System') }}</title>

        <!-- Styles & Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-light">
        <div class="d-flex min-vh-100 justify-content-center align-items-center">
            <div class="card shadow-sm w-100" style="max-width: 420px;">
                <div class="card-header text-center">
                    <a href="/" class="text-decoration-none">
                        <x-application-logo class="w-20 h-20" />
                    </a>
                </div>
                <div class="card-body">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </body>
</html>
