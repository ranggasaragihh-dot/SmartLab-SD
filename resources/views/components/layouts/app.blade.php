<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('components.layouts.head')
    <title>{{ $title ?? 'Smart Lab Booking' }}</title>
</head>
<body class="bg-gray-100 font-sans antialiased text-slate-900">

    <nav class="bg-white shadow mb-8">
        <div class="max-w-7xl mx-auto px-4 pt-[calc(env(safe-area-inset-top,0px)+1rem)] pb-4 flex justify-between items-center">
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