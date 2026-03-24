<div x-data="{ open: false }" @toggle-sidebar.window="open = !open">
    <div x-show="open" @click="open = false" x-transition.opacity class="fixed inset-0 z-40 bg-slate-900/60 backdrop-blur-sm md:hidden" style="display: none;"></div>
    <aside class="fixed inset-y-0 left-0 z-50 w-64 bg-[#1a237e] text-white flex flex-col transition-transform duration-300 transform 
              -translate-x-full md:translate-x-0 md:static md:h-screen shrink-0 shadow-2xl border-r border-white/10 font-sans"
       :class="open ? 'translate-x-0' : ''">

    <div class="relative shrink-0">
        <div class="absolute top-0 left-0 w-full h-1 bg-amber-400 z-20"></div>
        <div class="h-20 flex items-center gap-4 px-6 border-b border-white/5 relative">
            <div class="w-9 h-9 bg-white rounded-lg flex items-center justify-center shadow-lg shrink-0">
                 <img src="{{ asset('images/logo-uajy.png') }}" class="w-6 h-6 object-contain" alt="Logo">
            </div>
            <div class="flex flex-col justify-center">
                <h1 class="font-extrabold text-lg tracking-wide leading-none font-sans">LAB SD</h1>
                <p class="text-[10px] text-blue-200 uppercase tracking-widest font-medium mt-1">Lab Struktur Data</p>
            </div>
            
            <button @click="open = false" class="md:hidden ml-auto text-blue-300 hover:text-white">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
    </div>

    <nav class="flex-1 overflow-y-auto py-6 px-3 space-y-1">
        <p class="px-3 text-[10px] font-bold text-blue-300/50 uppercase tracking-widest mb-3 ml-1">
            Daftar Ruangan
        </p>

        @foreach($allowedClusters as $cluster)
            <button 
                wire:click="$set('selectedClusterId', {{ $cluster->id }})"
                @click="open = false"
                class="w-full text-left px-4 py-3.5 flex items-center justify-between rounded-xl transition-all duration-200 group mb-1 relative overflow-hidden
                {{ $selectedClusterId == $cluster->id 
                    ? 'bg-white/10 text-white shadow-md border-l-4 border-amber-400' 
                    : 'text-blue-200 hover:bg-white/5 border-l-4 border-transparent' 
                }}"
            >
                @if($selectedClusterId == $cluster->id)
                    <div class="absolute inset-0 bg-gradient-to-r from-blue-400/10 to-transparent pointer-events-none"></div>
                @endif

                <div class="flex items-center gap-3 relative z-10">
                    <span class="text-sm font-bold truncate tracking-wide">{{ $cluster->name }}</span>
                </div>
                
                @if($selectedClusterId == $cluster->id)
                    <svg class="w-4 h-4 text-amber-400 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                @endif
            </button>
        @endforeach
    </nav>

    <div class="border-t border-white/5 p-4 bg-black/10">
        <div class="flex items-center justify-between gap-3">
            <div class="flex items-center gap-3 min-w-0">
                <div class="relative shrink-0">
                    <div class="w-10 h-10 rounded-full bg-white text-[#1a237e] flex items-center justify-center font-bold border-2 border-amber-400 shadow-sm">
                        {{ substr(Auth::user()->name ?? 'A', 0, 1) }}
                    </div>
                    <div class="absolute bottom-0 right-0 w-2.5 h-2.5 bg-green-500 border-2 border-[#1a237e] rounded-full"></div>
                </div>
                <div class="overflow-hidden">
                    <h4 class="font-bold text-sm text-white truncate leading-tight">{{ Auth::user()->name ?? 'Mahasiswa' }}</h4>
                    <p class="text-[10px] text-blue-200 truncate font-mono mt-0.5">{{ Auth::user()->nomor_induk ?? '2107xxxx' }}</p>
                </div>
            </div>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" 
                        title="Keluar"
                        class="group flex items-center justify-center w-9 h-9 rounded-lg text-blue-300 hover:text-white transition-all">
                    <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
                </button>
            </form>
        </div>
    </div>
</aside>
</div>