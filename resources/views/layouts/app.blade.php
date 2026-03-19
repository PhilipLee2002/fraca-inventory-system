<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name', 'Inventory Management System'))</title>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <!-- Vite CSS -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body data-page="{{ request()->route()->getName() ? (str_contains(request()->route()->getName(), 'page.') ? explode('.', request()->route()->getName())[1] : explode('.', request()->route()->getName())[0]) : '' }}">
    <!-- Global loading indicator -->
    <div id="global-loading-indicator" class="d-none position-fixed top-0 start-0 w-100" style="z-index:9999;height:3px;">
        <div class="progress h-100 rounded-0 border-0">
            <div class="progress-bar progress-bar-striped progress-bar-animated bg-danger w-100"></div>
        </div>
    </div>

    <!-- Navigation -->
    @include('partials.navbar')

    <!-- Main Content -->
    <main class="container-fluid py-4">
        @yield('content')
    </main>

    <!-- Footer -->
    @include('partials.footer')

    <!-- Toast Container -->
    @include('partials.toast-container')

    <!-- Admin Verify Modal -->
    @include('partials.admin-verify-modal')

    <!-- Pass user and permissions data to JavaScript -->
    <script>
        window.appData = {
            user: <?php
                if (auth()->check()) {
                    $u = auth()->user();
                    $r = $u->role;
                    echo json_encode([
                        'id'    => $u->id,
                        'name'  => $u->name,
                        'email' => $u->email,
                        'role'  => $r ? $r->name : null,
                    ]);
                } else {
                    echo 'null';
                }
            ?>,
            permissions: <?php
                if (auth()->check()) {
                    $role = auth()->user()->role;
                    echo json_encode($role ? $role->rolePermissions()->pluck('name') : []);
                } else {
                    echo '[]';
                }
            ?>,
            csrfToken: '{{ csrf_token() }}'
        };
    </script>
</body>
</html>
