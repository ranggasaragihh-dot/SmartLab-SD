<div class="min-h-screen flex items-center justify-center bg-gray-50 relative overflow-hidden font-sans">
    
    {{-- Background Decoration --}}
    <div class="absolute top-0 left-0 w-full h-64 bg-blue-700 transform -skew-y-3 z-0 origin-top-left"></div>
    <div class="absolute bottom-0 right-0 w-64 h-64 bg-amber-400 rounded-full mix-blend-multiply filter blur-3xl opacity-20 z-0"></div>

    {{-- Login Card --}}
    <div class="relative z-10 w-full max-w-md bg-white rounded-2xl shadow-2xl overflow-hidden border border-gray-100">
        
        {{-- Header --}}
        <div class="bg-blue-800 p-6 text-center relative">
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-40 h-40 bg-blue-500/30 rounded-full blur-2xl -z-10"></div>
            
            <div class="flex flex-col items-center">
                
                {{-- CONTAINER LOGO: Kotak Putih dengan Shadow & Rounded --}}
                <div class="w-20 h-20 bg-white rounded-2xl flex items-center justify-center mb-3 shadow-lg ring-4 ring-white/10">
                    {{-- LOGO IMAGE --}}
                    {{-- Class 'object-contain' wajib agar logo tidak gepeng --}}
                    <img 
                        src="{{ asset('images/logo-uajy.png') }}" 
                        alt="Logo UAJY" 
                        class="w-14 h-14 object-contain"
                    >

                </div>

                {{-- MAIN TITLE --}}
                <h1 class="text-2xl font-bold text-white tracking-tight">
                    ATMA-LAB
                </h1>

                {{-- SUB-TITLE --}}
                <p class="text-blue-100/80 text-xs font-medium mt-1 mb-4">
                    Integrated Booking & Inventory System
                </p>

                {{-- BADGE: SISTRUK EDITION --}}
                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-blue-900/50 border border-blue-400/30 backdrop-blur-md shadow-sm">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-amber-500"></span>
                    </span>
                    <span class="text-[10px] font-bold text-white tracking-wider uppercase">
                        SISTRUK <span class="text-blue-200 font-normal capitalize">Edition</span>
                    </span>
                </div>
            </div>
        </div>

        <div class="h-1.5 w-full bg-gradient-to-r from-amber-400 via-amber-500 to-amber-400"></div>
        
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
                <button type="submit" class="w-full mt-2 bg-amber-500 hover:bg-amber-600 text-white font-bold py-3 rounded-lg shadow-lg shadow-amber-500/30 hover:shadow-amber-500/50 transform hover:-translate-y-0.5 transition-all duration-200">
                    MASUK
                </button>

            </form>

            <div class="mt-8 text-center text-xs text-gray-400">
                &copy; {{ date('Y') }} Informatika UAJY | Powered by SISTRUK Engine.
            </div>
        </div>
    </div>
</div>