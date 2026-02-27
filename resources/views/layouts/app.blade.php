<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'LiveChat') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <!-- Tailwind CSS (temporary CDN for prototyping if Vite isn't setup fully for UI yet, otherwise use Vite) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs" defer></script>
</head>

<body class="bg-gray-50 text-gray-900 font-sans antialiased h-screen flex overflow-hidden">

    <!-- Sidebar Component -->
    <x-sidebar />

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col min-w-0">
        <!-- Header Component -->
        <x-header />

        <!-- Main Content -->
        <main class="flex-1 overflow-y-auto p-6 w-full">
            @yield('content')
        </main>
    </div>

    @stack('scripts')
</body>

</html>