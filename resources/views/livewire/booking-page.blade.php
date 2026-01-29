<div class="flex h-screen w-full bg-slate-50 overflow-hidden font-sans">

    {{-- 1. SIDEBAR (TIDAK DIUBAH) --}}
    @include('livewire.booking.sidebar')

    {{-- 2. KONTEN UTAMA --}}
    <main class="flex-1 flex flex-col min-w-0 bg-slate-50">
        
        @include('livewire.booking.header')

        <div class="flex-1 flex flex-col h-full overflow-hidden bg-slate-50 font-sans">
    
            {{-- 1. HEADER HALAMAN (Sticky di atas) --}}
            <div class="bg-white border-b border-gray-200 px-8 py-5 flex-none shadow-sm z-10">
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-800 tracking-tight">{{ $currentClusterName ?? 'Nama Ruangan' }}</h1>
                        <p class="text-sm text-gray-500 mt-1 flex items-center gap-2">
                            <i class="fas fa-mouse-pointer text-indigo-500"></i>
                            Silakan klik pada PC yang tersedia untuk melakukan booking.
                        </p>
                    </div>
                    {{-- Tombol Kembali / Aksi Lain (Opsional) --}}
                    {{-- <button class="...">Kembali</button> --}}
                </div>
            </div>

            {{-- 2. WRAPPER KONTEN UTAMA --}}
            <div class="flex-1 overflow-hidden p-6">
                
                {{-- PERBAIKAN: Gunakan 'flex flex-row' untuk memaksa sejajar Kiri-Kanan --}}
                <div class="flex flex-row gap-6 h-full">

                    {{-- ========================================== --}}
                    {{-- PANEL KIRI: DENAH (Lebar Pasti 50%) --}}
                    {{-- ========================================== --}}
                    <div class="w-1/2 flex flex-col h-full overflow-hidden">
                        <div class="bg-white border border-gray-200 rounded-2xl shadow-sm flex flex-col h-full overflow-hidden">
                            
                            {{-- Header Panel --}}
                            <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center">
                                <h3 class="font-bold text-gray-700 flex items-center gap-2">
                                    <i class="far fa-map text-gray-400"></i> Layout Ruangan
                                </h3>
                                <span class="text-[10px] font-bold text-gray-400 border border-gray-200 px-2 py-0.5 rounded bg-white">
                                    REFERENSI
                                </span>
                            </div>

                            {{-- Body Panel (Gambar) --}}
                            <div class="flex-1 relative bg-slate-100/50 flex items-center justify-center p-4 overflow-hidden group">
                                <div class="absolute inset-0 bg-grid-slate-200 [mask-image:linear-gradient(0deg,white,rgba(255,255,255,0.6))]"></div>
                                <div class="relative z-10 w-full h-full flex items-center justify-center">
                                    @include('livewire.booking.denahBooking')
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ========================================== --}}
                    {{-- PANEL KANAN: DAFTAR PC (Lebar Pasti 50%) --}}
                    {{-- ========================================== --}}
                    <div class="w-1/2 flex flex-col h-full overflow-hidden">
                        <div class="bg-white border border-gray-200 rounded-2xl shadow-sm flex flex-col h-full overflow-hidden">
                            
                            {{-- Header Panel --}}
                            <div class="px-6 py-4 border-b border-gray-100 bg-white flex justify-between items-center gap-3">
                                <h3 class="font-bold text-gray-800 text-lg">Daftar Unit PC</h3>
                                
                                {{-- Legenda --}}
                                <div class="flex items-center gap-3 text-xs font-medium">
                                    <div class="flex items-center gap-1.5">
                                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span> Tersedia
                                    </div>
                                    <div class="flex items-center gap-1.5">
                                        <span class="w-2 h-2 rounded-full bg-gray-400"></span> Terisi
                                    </div>
                                    <div class="flex items-center gap-1.5">
                                        <span class="w-2 h-2 rounded-full bg-indigo-500"></span> Pilihan
                                    </div>
                                </div>
                            </div>

                            {{-- Area Scrollable --}}
                            <div class="flex-1 overflow-y-auto p-6 bg-slate-50/50 custom-scrollbar">
                                <div class="min-h-full pb-10"> 
                                    @include('livewire.booking.pc-grid-list',[
                                        'units'         => $cluster->inventory,
                                        'currentUserId' => auth()->id(),
                                        'mode'          => 'booking'
                                    ])
                                </div>
                            </div>
                            
                            {{-- Footer Kecil --}}
                            <div class="bg-gray-50 px-6 py-2 border-t border-gray-100 text-center">
                                <p class="text-[10px] text-gray-400">Pastikan Anda memilih PC yang sesuai.</p>
                            </div>

                        </div>
                    </div>

                </div>
            </div>
        </div>
    </main>
    @include('livewire.booking.modals')
</div>