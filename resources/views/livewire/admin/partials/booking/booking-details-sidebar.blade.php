<div x-data="{ open: @entangle('showDetailSidebar') }" 
     x-show="open" 
     @keydown.escape.window="open = false"
     class="relative z-[70]" 
     aria-labelledby="slide-over-title" 
     role="dialog" 
     aria-modal="true"
     style="display: none;">

    {{-- Backdrop --}}
    <div x-show="open" 
         x-transition.opacity.duration.300ms
         class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity"
         @click="open = false"></div>

    <div class="fixed inset-0 overflow-hidden">
        <div class="absolute inset-0 overflow-hidden">
            <div class="pointer-events-none fixed inset-y-0 right-0 flex max-w-full pl-10">
                
                {{-- Panel Sidebar --}}
                <div x-show="open" 
                     x-transition:enter="transform transition ease-in-out duration-300" 
                     x-transition:enter-start="translate-x-full" 
                     x-transition:enter-end="translate-x-0" 
                     x-transition:leave="transform transition ease-in-out duration-300" 
                     x-transition:leave-start="translate-x-0" 
                     x-transition:leave-end="translate-x-full" 
                     class="pointer-events-auto w-screen max-w-md">
                    
                    <div class="flex h-full flex-col overflow-y-auto bg-white shadow-2xl">
                        
                        {{-- HEADER SIDEBAR --}}
                        <div class="bg-slate-900 px-6 py-5 shrink-0 border-b border-slate-800">
                            <div class="flex items-start justify-between">
                                <div>
                                    <h2 class="text-white text-sm font-black uppercase tracking-widest" id="slide-over-title">
                                        Detail Transaksi
                                    </h2>
                                    <p class="text-[11px] text-slate-400 mt-1 font-mono">ID Booking: #{{ str_pad($selectedBooking->id ?? 0, 4, '0', STR_PAD_LEFT) }}</p>
                                </div>
                                <button type="button" @click="open = false" class="rounded-full bg-slate-800 p-1.5 text-slate-400 hover:text-white hover:bg-slate-700 transition-all focus:outline-none">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                </button>
                            </div>
                        </div>

                        {{-- BODY CONTENT --}}
                        @if($selectedBooking)
                        <div class="relative flex-1 px-6 py-6 space-y-6">

                            {{-- 1. STATUS BANNER (Dynamic Alert) --}}
                            @if($selectedBooking->status === 'cancelled')
                                <div class="rounded-xl bg-red-50 border border-red-200 p-4 shadow-sm animate-in fade-in slide-in-from-top-2">
                                    <div class="flex items-start gap-3">
                                        <div class="shrink-0 mt-0.5">
                                            <svg class="h-5 w-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                                        </div>
                                        <div>
                                            <h3 class="text-sm font-black text-red-900 uppercase tracking-tight">Booking Dibatalkan</h3>
                                            <div class="mt-2.5 text-xs text-red-800 space-y-2 bg-white/50 p-3 rounded-lg border border-red-100">
                                                <p><span class="font-bold block text-[10px] text-red-500 uppercase tracking-wider mb-0.5">Alasan:</span> "{{ $selectedBooking->cancel_reason ?? 'Tidak ada keterangan' }}"</p>
                                                <p><span class="font-bold block text-[10px] text-red-500 uppercase tracking-wider mb-0.5">Dibatalkan Oleh:</span> Administrator Lab (Sistem)</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @elseif($selectedBooking->status === 'finished')
                                <div class="rounded-xl bg-slate-50 border border-slate-200 p-4 flex items-center gap-3">
                                    <div class="shrink-0 w-8 h-8 rounded-full bg-emerald-100 flex items-center justify-center">
                                        <svg class="h-5 w-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                    </div>
                                    <div>
                                        <h3 class="text-sm font-bold text-slate-800">Sesi Telah Selesai</h3>
                                        <p class="text-[11px] text-slate-500 mt-0.5">Sesi berakhir dan unit telah dibebaskan.</p>
                                    </div>
                                </div>
                            @endif

                            {{-- 2. INFORMASI USER --}}
                            <div>
                                <h4 class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2.5 flex items-center gap-2">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                    Identitas Pemohon
                                </h4>
                                <div class="flex items-center gap-3 p-3.5 rounded-xl border border-slate-200 bg-white shadow-sm">
                                    <div class="h-10 w-10 rounded-full bg-slate-900 flex items-center justify-center text-white font-black text-sm shadow-md">
                                        {{ substr($selectedBooking->user->name ?? 'U', 0, 1) }}
                                    </div>
                                    <div>
                                        <p class="text-sm font-black text-slate-900">{{ $selectedBooking->user->name ?? 'Unknown' }}</p>
                                        <p class="text-xs text-slate-500 font-medium">{{ $selectedBooking->user->email ?? '-' }}</p>
                                    </div>
                                </div>
                            </div>

                            {{-- 3. DETAIL JADWAL & ASET --}}
                            <div>
                                <h4 class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2.5 flex items-center gap-2">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"></path></svg>
                                    Rincian Aset & Waktu
                                </h4>
                                <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm">
                                    <div class="grid grid-cols-2 border-b border-slate-100 divide-x divide-slate-100">
                                        <div class="p-3.5 bg-slate-50/50">
                                            <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider mb-1">Unit / Aset</p>
                                            <p class="text-sm font-black text-slate-900">{{ $selectedBooking->inventory->name ?? 'Ruangan Penuh (Event)' }}</p>
                                        </div>
                                        <div class="p-3.5 bg-slate-50/50">
                                            <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider mb-1">Lokasi Cluster</p>
                                            <p class="text-sm font-bold text-slate-700">{{ $selectedBooking->inventory->cluster->name ?? '-' }}</p>
                                        </div>
                                    </div>
                                    <div class="p-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-lg bg-blue-50 flex items-center justify-center text-blue-600">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                            </div>
                                            <div>
                                                <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider mb-0.5">Jadwal Pelaksanaan</p>
                                                <p class="text-sm font-black text-slate-900">
                                                    {{ \Carbon\Carbon::parse($selectedBooking->start_time)->format('H:i') }} <span class="text-slate-400 font-normal mx-1">s/d</span> {{ \Carbon\Carbon::parse($selectedBooking->end_time)->format('H:i') }}
                                                </p>
                                                <p class="text-xs text-slate-500 font-medium">
                                                    {{ \Carbon\Carbon::parse($selectedBooking->start_time)->format('l, d F Y') }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            {{-- 4. KETERANGAN / TUJUAN (Jika Ada) --}}
                            @if($selectedBooking->purpose)
                            <div>
                                <h4 class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2.5">Agenda / Tujuan</h4>
                                <div class="bg-amber-50/50 border border-amber-100 p-4 rounded-xl">
                                    <p class="text-sm font-medium text-amber-900 italic">
                                        "{{ $selectedBooking->purpose }}"
                                    </p>
                                </div>
                            </div>
                            @endif

                        </div>
                        @else
                        <div class="flex-1 flex flex-col items-center justify-center p-6 text-center">
                            <div class="w-12 h-12 border-4 border-slate-100 border-t-slate-900 rounded-full animate-spin mb-4"></div>
                            <p class="text-sm font-bold text-slate-500">Memuat data transaksi...</p>
                        </div>
                        @endif

                        {{-- FOOTER SIDEBAR --}}
                        <div class="border-t border-slate-200 px-6 py-4 bg-slate-50 shrink-0">
                            <button @click="open = false" class="w-full rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-bold text-white shadow-lg shadow-slate-900/20 hover:bg-slate-800 transition-all active:scale-95">
                                Tutup Panel
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>