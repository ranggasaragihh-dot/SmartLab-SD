<div class="min-h-screen flex items-center justify-center bg-gray-50 relative overflow-hidden">
    
    {{-- Background Decoration --}}
    <div class="absolute top-0 left-0 w-full h-64 bg-blue-700 transform -skew-y-3 z-0 origin-top-left"></div>
    <div class="absolute bottom-0 right-0 w-64 h-64 bg-amber-400 rounded-full mix-blend-multiply filter blur-3xl opacity-20 z-0"></div>

    {{-- Login Card --}}
    <div class="relative z-10 w-full max-w-md bg-white rounded-2xl shadow-2xl overflow-hidden border border-gray-100">
        
        {{-- Header --}}
        <div class="bg-blue-800 p-8 text-center relative">
            <div class="absolute bottom-0 left-0 w-full h-2 bg-amber-400"></div>
            
            <div class="w-20 h-20 bg-white rounded-full flex items-center justify-center mx-auto mb-4 shadow-lg border-4 border-blue-100">
                {{-- Icon Login --}}
                <svg class="w-10 h-10 text-blue-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" /></svg>
            </div>
            
            <h2 class="text-2xl font-bold text-white tracking-wide">SmartLab Login</h2>
            <p class="text-blue-200 text-sm mt-1">Informatika UAJY</p>
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
                &copy; {{ date('Y') }} Informatika UAJY.
            </div>
        </div>
    </div>
</div>