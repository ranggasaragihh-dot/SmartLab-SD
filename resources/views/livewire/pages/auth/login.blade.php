<div class="min-h-screen flex items-center justify-center bg-gray-50 relative overflow-hidden font-sans">
    <div class="absolute top-0 left-0 w-full h-64 bg-blue-700 transform -skew-y-3 z-0 origin-top-left"></div>
    <div class="absolute bottom-0 right-0 w-64 h-64 bg-amber-400 rounded-full mix-blend-multiply filter blur-3xl opacity-20 z-0"></div>

    <div class="relative z-10 w-full max-w-md bg-white rounded-2xl shadow-2xl overflow-hidden border border-gray-100">
        <div class="bg-blue-800 p-6 text-center relative">
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-40 h-40 bg-blue-500/30 rounded-full blur-2xl -z-10"></div>
            
            <div class="flex flex-col items-center">
                <div class="w-20 h-20 bg-white rounded-2xl flex items-center justify-center mb-3 shadow-lg ring-4 ring-white/10">
                    <img 
                        src="{{ asset('images/logo-uajy.png') }}" 
                        alt="Logo UAJY" 
                        class="w-14 h-14 object-contain"
                    >
                </div>

                <h1 class="text-2xl font-bold text-white tracking-tight">
                    ATMA-LAB
                </h1>

                <p class="text-blue-100/80 text-xs font-medium mt-1 mb-4">
                    Integrated Booking & Inventory System
                </p>

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
        
        <div class="p-8 pt-10">
            <form wire:submit="login" class="space-y-6">
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-2 ml-1">
                        Nomor Induk NPM / NPP
                    </label>
                    
                    <input 
                        wire:model="nomor_induk" 
                        type="text" 
                        class="w-full pl-4 pr-4 py-3 rounded-lg border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all outline-none bg-gray-50 focus:bg-white" 
                        placeholder="Masukkan Nomor Induk NPM/NPP" 
                        required 
                        autofocus
                    >

                    @error('nomor_induk') 
                        <span class="text-red-500 text-xs mt-1 ml-1 block">{{ $message }}</span> 
                    @enderror
                </div>

                <div x-data="{ showPassword: false }">
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-2 ml-1">Password</label>
                    
                    {{-- Wrapper relative untuk menampung input dan ikon absolute --}}
                    <div class="relative">
                        <input 
                            wire:model="password" 
                            {{-- Tipe input berubah dinamis berdasarkan state Alpine --}}
                            x-bind:type="showPassword ? 'text' : 'password'" 
                            {{-- Padding right (pr) diperlebar jadi pr-12 agar teks tidak menabrak ikon --}}
                            class="w-full pl-4 pr-12 py-3 rounded-lg border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all outline-none bg-gray-50 focus:bg-white" 
                            placeholder="••••••••" 
                            required
                        >
                        
                        {{-- Tombol Toggle Mata --}}
                        <button 
                            type="button" 
                            @click="showPassword = !showPassword"
                            class="absolute inset-y-0 right-0 flex items-center pr-4 text-gray-400 hover:text-blue-500 transition-colors focus:outline-none"
                            tabindex="-1" {{-- Agar tidak mengganggu alur tombol Tab saat user mengetik --}}
                        >
                            {{-- Ikon Mata Terbuka (Muncul saat password tersembunyi) --}}
                            <svg x-show="!showPassword" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>

                            {{-- Ikon Mata Tertutup/Dicoret (Muncul saat password terlihat) --}}
                            <svg x-show="showPassword" style="display: none;" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                            </svg>
                        </button>
                    </div>
                    
                    @error('password') <span class="text-red-500 text-xs mt-1 ml-1 block">{{ $message }}</span> @enderror
                </div>

                <div class="flex items-center justify-between text-sm">
                    <label class="flex items-center gap-2 cursor-pointer">
                        {{-- HAPUS 'form.' --}}
                        <input wire:model="remember" type="checkbox" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <span class="text-gray-600">Ingat saya</span>
                    </label>
                </div>
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