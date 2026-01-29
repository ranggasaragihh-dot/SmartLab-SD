<div class="min-h-screen flex items-center justify-center bg-gray-50 relative overflow-hidden">
    
    {{-- Background Decoration --}}
    <div class="absolute top-0 left-0 w-full h-64 bg-blue-700 transform -skew-y-3 z-0 origin-top-left"></div>
    <div class="absolute bottom-0 right-0 w-64 h-64 bg-amber-400 rounded-full mix-blend-multiply filter blur-3xl opacity-20 z-0"></div>

    {{-- Login Card --}}
    <div class="relative z-10 w-full max-w-md bg-white rounded-2xl shadow-2xl overflow-hidden border border-gray-100">
        
        {{-- Header --}}
        <div class="bg-blue-900 p-10 text-center relative overflow-hidden">
            {{-- Hiasan Background (Opsional - agar tidak terlalu flat) --}}
            <div class="absolute top-0 left-0 w-full h-full bg-white/5 pointer-events-none"></div>
            <div class="absolute bottom-0 left-0 w-full h-1.5 bg-gradient-to-r from-amber-400 via-amber-300 to-amber-500"></div>
            
            <div class="relative z-10 flex flex-col items-center">
                
                {{-- AREA LOGO --}}
                <div class="w-24 h-24 bg-white rounded-2xl flex items-center justify-center mb-5 shadow-xl border-4 border-blue-800/50 transform rotate-3 hover:rotate-0 transition-transform duration-300">
                    
                    {{-- OPSI 1: JIKA PAKAI GAMBAR FILE (Hapus komentar ini jika file sudah ada) --}}
                    <img src="{{ asset('images/logo-uajy.png') }}" alt="Logo UAJY" class="w-16 h-16 object-contain">

                    {{-- OPSI 2: JIKA BELUM ADA GAMBAR (Pakai Icon Sementara) --}}
                    <!-- <svg class="w-12 h-12 text-blue-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.384-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                    </svg> -->

                </div>
                
                {{-- MAIN TITLE --}}
                {{-- Font Sans-serif, Tebal (Black/Bold), dan Tracking Tight agar terlihat modern --}}
                <h1 class="text-4xl font-black text-white tracking-tight font-sans drop-shadow-sm">
                    ATMA<span class="text-amber-400">-</span>LAB
                </h1>

                {{-- SUB-TITLE --}}
                <p class="text-blue-200 text-sm font-medium mt-2 tracking-wide uppercase opacity-90">
                    Integrated Booking & Inventory System
                </p>

                {{-- BADGE: SISTRUK EDITION --}}
                <div class="mt-5">
                    <span class="inline-flex items-center px-4 py-1.5 rounded-full text-xs font-bold bg-blue-800 text-amber-400 border border-blue-700 shadow-sm">
                        <span class="w-2 h-2 bg-amber-400 rounded-full mr-2 animate-pulse"></span>
                        SISTRUK EDITION (Lab. Struktur Data)
                    </span>
                </div>

            </div>
        </div>

        {{-- Form --}}
        <div class="p-8 pt-10">
            <form wire:submit="login" class="space-y-6">
                
                {{-- Input Email --}}
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-2 ml-1">Email / NPM</label>
                    {{-- HAPUS 'form.' --}}
                    <input wire:model="email" type="email" class="w-full pl-4 pr-4 py-3 rounded-lg border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all outline-none bg-gray-50 focus:bg-white" placeholder="Email Anda" required autofocus>
                    @error('email') <span class="text-red-500 text-xs mt-1 ml-1 block">{{ $message }}</span> @enderror
                </div>

                {{-- Input Password --}}
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-2 ml-1">Password</label>
                    {{-- HAPUS 'form.' --}}
                    <input wire:model="password" type="password" class="w-full pl-4 pr-4 py-3 rounded-lg border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all outline-none bg-gray-50 focus:bg-white" placeholder="••••••••" required>
                    @error('password') <span class="text-red-500 text-xs mt-1 ml-1 block">{{ $message }}</span> @enderror
                </div>

                {{-- Remember Me --}}
                <div class="flex items-center justify-between text-sm">
                    <label class="flex items-center gap-2 cursor-pointer">
                        {{-- HAPUS 'form.' --}}
                        <input wire:model="remember" type="checkbox" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <span class="text-gray-600">Ingat saya</span>
                    </label>
                </div>
                {{-- Submit Button --}}
                <button type="submit" class="w-full bg-amber-500 hover:bg-amber-600 text-white font-bold py-3 rounded-lg shadow-md hover:shadow-lg transform hover:-translate-y-0.5 transition-all duration-200">
                    MASUK
                </button>

            </form>

            <div class="mt-8 text-center text-xs text-gray-400">
                &copy; {{ date('Y') }} Informatika UAJY | Powered by SISTRUK Engine.
            </div>
        </div>
    </div>
</div>