<div class="flex-1 flex flex-col h-screen bg-slate-50 overflow-y-auto">
    
    {{-- A. PAGE HEADER --}}
    <div class="px-8 py-8">
        <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Manajemen Layout & Unit</h1>
        <p class="text-slate-500 mt-1">Pilih ruangan laboratorium untuk mengelola denah, status kursi, dan inventaris.</p>
    </div>

    {{-- B. GRID CLUSTERS --}}
    <div class="px-8 pb-12">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            
            @foreach($clusters as $cluster)
            @php
                // Nanti ganti ini dengan: $cluster->is_fully_booked
                $isFullBooked = $cluster->available_count  == 0; 
            @endphp
            <a href="{{ route('admin.booking.manage', $cluster->id) }}" class="group relative">
                
                {{-- Card Container --}}
                <div class="rounded-xl border shadow-sm p-5 h-full flex flex-col justify-between transition-all duration-300 group-hover:-translate-y-1
                    {{ $isFullBooked 
                        ? 'bg-purple-50/50 border-purple-300 hover:border-purple-500 hover:shadow-purple-100' 
                        : 'bg-white border-slate-200 hover:border-blue-600 hover:shadow-lg' 
                    }}">

                    {{-- Aksen Garis Emas (Hover Only) --}}
                    <div class="absolute top-0 left-4 right-4 h-1 rounded-b-md opacity-0 group-hover:opacity-100 transition-opacity
                        {{ $isFullBooked ? 'bg-purple-500' : 'bg-amber-400' }}">
                    </div>

                    {{-- 1. Header Card --}}
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            
                            {{-- Icon Box --}}
                            <div class="w-12 h-12 rounded-lg flex items-center justify-center transition-colors
                                {{ $isFullBooked 
                                    ? 'bg-purple-100 text-purple-600' 
                                    : 'bg-blue-50 text-blue-700 group-hover:bg-blue-600 group-hover:text-white' 
                                }}">
                                
                                @if($isFullBooked)
                                    {{-- Icon Kalender/Event jika Full Booked --}}
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                @else
                                    {{-- Icon Gedung Normal --}}
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2-2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                @endif
                            </div>

                            {{-- BADGE STATUS --}}
                            @if($isFullBooked)
                                <span class="px-2.5 py-1 bg-purple-600 text-white text-[10px] font-bold uppercase tracking-wider rounded-md shadow-sm shadow-purple-200 animate-pulse">
                                    Booked 1 Ruangan
                                </span>
                            @else
                                <span class="px-2.5 py-1 bg-slate-100 text-slate-600 text-xs font-bold rounded-full border border-slate-200">
                                    {{ $cluster->inventory()->count() ?? 0 }} Unit
                                </span>
                            @endif
                        </div>

                        {{-- Nama Ruangan --}}
                        <h3 class="text-lg font-bold mb-1 transition-colors
                            {{ $isFullBooked ? 'text-purple-900' : 'text-slate-800 group-hover:text-blue-700' }}">
                            {{ $cluster->name }}
                        </h3>
                        
                        {{-- Keterangan Tambahan --}}
                        @if($isFullBooked)
                            <p class="text-xs text-purple-600 font-bold flex items-center gap-1">
                                <span class="w-1.5 h-1.5 rounded-full bg-purple-600"></span>
                                Sedang digunakan Kelas/Event
                            </p>
                        @else
                            <p class="text-xs text-slate-400 font-medium uppercase tracking-wider">
                                Lab Informatika
                            </p>
                        @endif
                    </div>

                    {{-- 2. Mini Stats --}}
                    {{-- Update bagian Mini Stats di dalam Card --}}
                    <div class="mt-6 pt-4 border-t border-slate-100 grid grid-cols-4 gap-1 text-center">
                        
                        {{-- 1. TERSEDIA --}}
                        <div class="flex flex-col">
                            <span class="text-[10px] text-slate-400 font-bold uppercase truncate" title="Tersedia">Avail</span>
                            <span class="text-sm font-bold text-green-600">{{ $cluster->available_count ?? 0 }}</span>
                        </div>

                        {{-- 2. TERISI --}}
                        <div class="flex flex-col border-l border-slate-100">
                            <span class="text-[10px] text-slate-400 font-bold uppercase truncate" title="Terisi">Book</span>
                            <span class="text-sm font-bold text-blue-600">{{ $cluster->booked_count ?? 0 }}</span>
                        </div>

                        {{-- 3. MAINTENANCE (BARU - AMBER/ORANGE) --}}
                        <div class="flex flex-col border-l border-slate-100">
                            <span class="text-[10px] text-amber-500 font-bold uppercase truncate" title="Maintenance">Maint</span>
                            <span class="text-sm font-bold text-amber-500">{{ $cluster->maintenance_count ?? 0 }}</span>
                        </div>

                        {{-- 4. RUSAK --}}
                        <div class="flex flex-col border-l border-slate-100">
                            <span class="text-[10px] text-slate-400 font-bold uppercase truncate" title="Rusak">Bad</span>
                            <span class="text-sm font-bold text-red-500">{{ $cluster->broken_count ?? 0 }}</span>
                        </div>

                    </div>
                </div>
            </a>
            @endforeach

        </div>
    </div>
</div>