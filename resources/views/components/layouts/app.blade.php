<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- @vite(['resources/css/app.css', 'resources/css/user-mobile.css', 'resources/js/app.js']) -->
    
    <title>{{ $title ?? 'Smart Lab Booking' }}</title>

    <script src="https://cdn.tailwindcss.com"></script>
    
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-gray-100 font-sans antialiased">
    
    <nav class="bg-white shadow mb-8">
        <div class="max-w-7xl mx-auto px-4 py-4 flex justify-between items-center">
            <div class="text-xl font-bold text-blue-600">Smart Lab UAJY</div>
            
            @auth
                <div class="text-sm text-gray-600">
                    Halo, {{ Auth::user()->name }}
                    <form method="POST" action="{{ route('logout') }}" class="inline ml-2">
                        @csrf
                        <button type="submit" class="text-red-500 hover:underline text-xs">(Logout)</button>
                    </form>
                </div>
            @endauth
        </div>
    </nav>

    <main class="max-w-7xl mx-auto px-4 pb-12">
        {{ $slot }}
    </main>

</body>
</html>