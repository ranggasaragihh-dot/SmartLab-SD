<div class="flex h-screen w-full bg-white text-gray-800 font-sans overflow-hidden">

    {{-- A. PANGGIL SIDEBAR --}}
    @include('livewire.booking.sidebar')

    {{-- B. KONTEN UTAMA --}}
    <main class="flex-1 flex flex-col h-full relative bg-gray-50">
        
        {{-- PANGGIL HEADER --}}
        @include('livewire.booking.header')

        {{-- AREA SCROLLABLE --}}
        <div class="flex-1 overflow-y-auto p-8">
            <div class="max-w-5xl mx-auto pb-20">

                {{-- Judul & Legenda (Common) --}}
                <div class="mb-6 flex items-center justify-between">
                    <h2 class="text-xl font-bold text-gray-800">#{{ $currentClusterName }}</h2>
                    <div class="flex gap-3 text-[10px] font-bold uppercase text-gray-400 bg-white px-3 py-1.5 rounded-full shadow-sm">
                        <span class="flex items-center gap-1"><i class="w-2 h-2 rounded-full bg-gray-200"></i> Kosong</span>
                        <span class="flex items-center gap-1"><i class="w-2 h-2 rounded-full bg-amber-400"></i> Saya</span>
                        <span class="flex items-center gap-1"><i class="w-2 h-2 rounded-full bg-gray-600 opacity-30"></i> Isi</span>
                    </div>
                </div>

                {{-- LOGIKA PEMILIHAN LAYOUT DENAH --}}
                @if($currentClusterName == 'PC-01' || $currentClusterName == 'Lab RPL')
                    
                    {{-- PERBAIKAN DI SINI: --}}
                    {{-- Kita WAJIB mengirim parameter 'items' dan 'isAdmin' --}}
                    @include('livewire.booking.layouts.denah', [
                        'items'   => $inventories,  {{-- Pastikan nama variabel ini sesuai dengan di Controller --}}
                        'isAdmin' => false          {{-- Matikan mode edit, aktifkan mode booking --}}
                    ])
                
                @elseif($currentClusterName == 'Lab Jaringan')
                    <div class="p-10 text-center text-gray-400">Layout Lab Jaringan belum dibuat.</div>

                @else
                    <div class="bg-white p-10 rounded-3xl shadow-sm text-center">
                        <p class="text-gray-400">Denah untuk ruangan ini belum tersedia.</p>
                    </div>
                @endif

            </div>
        </div>
    </main>

    {{-- C. PANGGIL MODALS --}}
    @include('livewire.booking.modals')

</div>