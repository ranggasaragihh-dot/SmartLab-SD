<div 
    x-data="{ 
        activeSlide: 0, 
        total: {{ $clusters->count() }},
        init() {
            setInterval(() => {
                this.activeSlide = (this.activeSlide + 1) % this.total;
            }, 15000); 
        }
    }" 
    class="h-screen w-full flex flex-col relative bg-slate-950 text-white font-sans overflow-hidden"
    wire:poll.10s
>
    {{-- HEADER --}}
    <div class="h-20 bg-slate-900 border-b border-slate-800 flex justify-between items-center px-8 shadow-lg z-20 shrink-0">
        <div class="flex items-center gap-4">
            <div class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center font-bold text-xl shadow-blue-500/50 shadow-lg">L</div>
            <div>
                <h1 class="text-xl font-bold tracking-wider uppercase text-slate-100">Lab Monitoring</h1>
                <p class="text-[10px] text-blue-400 font-mono tracking-widest">REAL-TIME AVAILABILITY</p>
            </div>
        </div>
        <div class="text-right" x-data="{ time: '', date: '' }" x-init="setInterval(() => { let d = new Date(); time = d.toLocaleTimeString('id-ID', {hour:'2-digit', minute:'2-digit'}); date = d.toLocaleDateString('id-ID', {weekday:'long', day:'numeric', month:'long'}); }, 1000)">
            <div class="text-3xl font-bold font-mono text-white tracking-widest" x-text="time"></div>
            <div class="text-[10px] text-slate-400 uppercase tracking-widest" x-text="date"></div>
        </div>
    </div>

    {{-- CONTENT CAROUSEL --}}
    <div class="flex-1 relative w-full overflow-hidden">
        
        @foreach($clusters as $index => $cluster)
            
            <div x-show="activeSlide === {{ $index }}"
                 x-transition:enter="transition ease-out duration-1000"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-300"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="absolute inset-0 flex w-full h-full" 
            > 
                {{-- KIRI: INFO & PETA (45%) --}}
                <div class="w-[45%] flex flex-col h-full border-r border-slate-800 bg-slate-900 relative z-10">
                    <div class="p-6 bg-slate-800 shadow-xl z-20 shrink-0">
                        <div class="flex justify-between items-start mb-4">
                            <div class="max-w-[75%]"> 
            
                                {{-- Nama Cluster --}}
                                <h2 class="text-3xl font-extrabold text-white leading-none tracking-tight mb-3">
                                    {{ $cluster->name }}
                                </h2>

                                {{-- SMART TAGS (Kegunaan & Spek) --}}
                                @if($cluster->description)
                                    <div class="flex flex-wrap gap-2">
                                        @php
                                            // Memecah text berdasarkan koma
                                            $tags = explode(',', $cluster->description);
                                        @endphp

                                        @foreach($tags as $tag)
                                            {{-- Style Tag: background agak terang, text normal (tidak uppercase) --}}
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-md bg-slate-700/80 border border-slate-600 text-slate-200 text-xs font-medium shadow-sm transition-transform hover:scale-105 cursor-default">
                                                {{-- Dot Accent --}}
                                                <span class="w-1.5 h-1.5 rounded-full bg-blue-400 mr-2 opacity-75"></span>
                                                {{ trim($tag) }}
                                            </span>
                                        @endforeach
                                    </div>
                                @else
                                    {{-- Fallback jika kosong --}}
                                    <div class="text-slate-500 text-sm italic">
                                        General Purpose Lab
                                    </div>
                                @endif

                            </div>
                            <div class="text-right">
                                <span class="text-4xl font-bold text-blue-500">{{ $cluster->inventory->count() }}</span>
                                <div class="text-[10px] text-slate-400 uppercase">Total Unit</div>
                            </div>
                        </div>

                        @php
                            $total = $cluster->inventory->count();
                            $booked = $cluster->inventory->filter(fn($i) => $i->bookings->isNotEmpty())->count();
                            $broken = $cluster->inventory->whereIn('status', ['broken', 'maintenance'])->count();
                            $available = $total - $booked - $broken;
                        @endphp
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div class="bg-emerald-900/30 border border-emerald-500/30 px-4 py-3 rounded-lg flex items-center justify-between">
                                <div>
                                    <div class="text-2xl font-bold text-white leading-none">{{ $available }}</div>
                                    <div class="text-[10px] text-emerald-400 font-bold uppercase mt-0.5">Available</div>
                                </div>
                                <div class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></div>
                            </div>
                            <div class="bg-blue-900/30 border border-blue-500/30 px-4 py-3 rounded-lg flex items-center justify-between">
                                <div>
                                    <div class="text-2xl font-bold text-white leading-none">{{ $booked }}</div>
                                    <div class="text-[10px] text-blue-400 font-bold uppercase mt-0.5">Terisi</div>
                                </div>
                                <div class="w-2 h-2 rounded-full bg-blue-500"></div>
                            </div>
                        </div>
                    </div>

                    <div class="flex-1 bg-slate-800/50 relative overflow-hidden group flex items-center justify-center p-4">
                        @if($cluster->denah_url)
                            <img src="{{ $cluster->denah_url }}" class="w-full h-full object-contain transition-transform duration-700 hover:scale-105">
                        @else
                            <div class="text-slate-600 flex flex-col items-center opacity-50">
                                <svg class="w-32 h-32 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="0.5" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0121 18.382V7.618a1 1 0 01-.553-.894L15 4m0 13V4m0 0L9 7"></path></svg>
                                <span class="text-lg font-light tracking-widest">MAP AREA</span>
                            </div>
                        @endif
                        <!-- <div class="absolute bottom-4 left-4 flex gap-2">
                             <div class="bg-black/60 px-2 py-1 rounded text-[10px] text-white backdrop-blur-sm border border-white/10">PINTU MASUK 🚪</div>
                        </div> -->
                    </div>
                </div>

                {{-- KANAN: GRID DINAMIS (55%) --}}
                <div class="w-[55%] h-full p-6 bg-slate-950">
                    
                    @if($cluster->currentEvent)
                        {{-- MODE EVENT --}}
                        <div class="w-full h-full bg-indigo-900/80 rounded-3xl border-2 border-indigo-500 flex flex-col items-center justify-center text-center p-12 relative overflow-hidden shadow-2xl animate-pulse">
                            <div class="absolute inset-0 opacity-10 bg-[url('https://www.transparenttextures.com/patterns/diagmonds-light.png')]"></div>
                            <h2 class="text-5xl font-bold text-white mb-4 drop-shadow-lg">SEDANG DIGUNAKAN</h2>
                            <h3 class="text-3xl font-bold text-indigo-100 uppercase tracking-wide mb-6">{{ $cluster->currentEvent->description }}</h3>
                            <div class="flex items-center gap-4 text-2xl text-indigo-200">
                                <span class="font-mono bg-black/30 px-3 py-1 rounded">{{ \Carbon\Carbon::parse($cluster->currentEvent->start_time)->format('H:i') }}</span>
                                <span>-</span>
                                <span class="font-mono bg-black/30 px-3 py-1 rounded">{{ \Carbon\Carbon::parse($cluster->currentEvent->end_time)->format('H:i') }}</span>
                            </div>
                        </div>
                    @else
                        {{-- MODE NORMAL --}}
                        @php
                            $count = $cluster->inventory->count();
                            $gridCols = 'grid-cols-4'; $cols = 4; $fontSize = 'text-2xl';

                            if ($count > 0) {
                                if ($count <= 6) {
                                    $cols = 2; $gridCols = 'grid-cols-2'; $fontSize = 'text-6xl';
                                } elseif ($count <= 12) {
                                    $cols = 4; $gridCols = 'grid-cols-4'; $fontSize = 'text-5xl';
                                } elseif ($count <= 20) {
                                    $cols = 5; $gridCols = 'grid-cols-5'; $fontSize = 'text-3xl';
                                } else {
                                    $cols = 8; $gridCols = 'grid-cols-6 md:grid-cols-8'; $fontSize = 'text-xl';
                                }
                            }
                            
                            $rowCount = max(1, (int) ceil($count / $cols));
                        @endphp

                        {{-- CONTAINER GRID (FIX CSS VARIABLE) --}}
                        <div class="h-full w-full pr-2">
                            <div class="grid {{ $gridCols }} gap-4 h-full w-full"
                                 style="--total-rows: {{ $rowCount }}; grid-template-rows: repeat(var(--total-rows), minmax(0, 1fr));">
                                
                                @foreach($cluster->inventory as $unit)
                                    @php
                                        $booking = $unit->bookings->filter(function($b) { return in_array($b->status, ['booked', 'active']); })->first();
                                        if ($unit->status == 'broken') {
                                            $bg = 'bg-red-900/20 border-red-800 text-red-500'; $statusText = 'OFF'; $isAvailable = false;
                                        } elseif ($unit->status == 'maintenance') {
                                            $bg = 'bg-amber-900/20 border-amber-800 text-amber-500'; $statusText = 'MAINT'; $isAvailable = false;
                                        } elseif ($booking) {
                                            $bg = 'bg-blue-600 shadow-lg shadow-blue-900/50 border-blue-500 text-white'; 
                                            $statusText = \Carbon\Carbon::parse($booking->end_time)->format('H:i'); 
                                            $isAvailable = false;
                                        } else {
                                            $bg = 'bg-emerald-600 shadow-lg shadow-emerald-900/50 border-emerald-500 text-white'; $statusText = 'READY'; $isAvailable = true;
                                        }
                                    @endphp

                                    <div class="{{ $bg }} border-2 rounded-xl h-full w-full flex flex-col items-center justify-center relative transition-all duration-500 group">
                                        <span class="{{ $fontSize }} font-bold tracking-tight leading-none mb-1 group-hover:scale-110 transition-transform">
                                            {{ $unit->name }}
                                        </span>
                                        <span class="text-[10px] uppercase font-bold tracking-wider opacity-90 bg-black/20 px-2 rounded-full">
                                            {{ $statusText }}
                                        </span>
                                        @if($isAvailable)
                                            <div class="absolute top-2 right-2 w-1.5 h-1.5 bg-white rounded-full animate-ping"></div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    {{-- FOOTER --}}
    <div class="h-10 bg-blue-950 border-t border-blue-900 flex items-center relative overflow-hidden z-20 shrink-0">
        <div class="absolute left-0 top-0 bottom-0 bg-blue-900 px-6 flex items-center font-bold text-xs uppercase tracking-widest z-10 shadow-xl text-blue-200">
            PENGUMUMAN
        </div>
        <div class="animate-marquee whitespace-nowrap pl-40 text-sm font-medium text-blue-100 flex gap-24 items-center">
            <span>📢 Harap tidak meninggalkan barang berharga di laboratorium.</span>
            <span>⚡ Dilarang membawa makanan dan minuman.</span>
            <span>📅 Jadwal Praktikum: Senin - Rabu.</span>
            <span>🔧 Laporkan kendala teknis ke Admin.</span>
        </div>
    </div>

    <style>
        .animate-marquee { animation: marquee 25s linear infinite; }
        @keyframes marquee {
            0% { transform: translateX(100%); }
            100% { transform: translateX(-100%); }
        }
    </style>
</div>