<div class="bg-white p-8 md:p-12 rounded-[3rem] shadow-xl border border-gray-100 min-h-[600px] flex flex-col items-center relative w-full max-w-6xl mx-auto select-none overflow-hidden">

    {{-- 1. DEKORASI ATAS: LAYAR PROYEKTOR --}}
    <div class="w-full flex flex-col items-center mb-10 transform scale-90 sm:scale-100">
        <div class="w-2/3 max-w-lg h-4 bg-gray-800 rounded-full shadow-lg mb-2 relative overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-r from-gray-700 via-gray-500 to-gray-700 opacity-50"></div>
        </div>
        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-[0.3em]">Layar Depan</span>
        <div class="w-1/2 h-16 bg-blue-400 blur-3xl opacity-10 rounded-full -mt-6"></div>
    </div>

    {{-- 2. GRID DINAMIS (PC) --}}
    <div class="w-full grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-6 justify-items-center mb-10">
        
        @foreach($units as $unit)
            @php
                // 1. Cek Status Fisik (Maintenance/Rusak vs Bisa Dipakai)
                // Sesuai validasi backend: if ($inventory->status !== 'available') return;
                // Jadi jika status bukan 'available' (misal: 'maintenance'), tombol mati total.
                // TAPI: Jika statusnya 'booked' (terisi), kita anggap masih 'available' secara fisik agar bisa diklik pemiliknya.
                $isFunctional = in_array($unit->status, ['available', 'booked', 'active']); 

                // 2. Cek Seleksi (Untuk highlight biru saat diklik)
                // Menggunakan variabel $selectedInventoryId sesuai backend Anda ($this->selectedInventoryId)
                $isSelected = (isset($selectedInventoryId) && $selectedInventoryId == $unit->id);

                // 3. Logika Tampilan (Visual Feedback)
                if (!$isFunctional) {
                    // KONDISI: RUSAK / MAINTENANCE (Benar-benar mati)
                    $cardStyle = 'bg-slate-100 border-transparent cursor-not-allowed opacity-50';
                    $iconStyle = 'text-slate-300';
                    $textStyle = 'text-slate-400';
                    $dotStyle  = ''; 
                } elseif ($isSelected) {
                    // KONDISI: SEDANG DIPILIH (Highlight Biru)
                    $cardStyle = 'bg-blue-600 border-blue-600 shadow-xl shadow-blue-200 transform scale-110 z-10 ring-4 ring-blue-50';
                    $iconStyle = 'text-white';
                    $textStyle = 'text-white';
                    $dotStyle  = '';
                } elseif ($unit->status == 'booked') {
                    // KONDISI: TERISI (Abu-abu tapi BISA DIKLIK untuk cek Skenario A)
                    // Kita tidak tahu ini punya siapa (karena di view loop), jadi biarkan backend yang menolak jika bukan pemilik.
                    $cardStyle = 'bg-gray-50 border-gray-200 hover:border-gray-400 cursor-pointer';
                    $iconStyle = 'text-gray-300';
                    $textStyle = 'text-gray-500';
                    $dotStyle  = '<span class="absolute top-2 right-2 text-[9px] font-bold text-gray-400 uppercase">Isi</span>';
                } else {
                    // KONDISI: KOSONG (Putih Bersih)
                    $cardStyle = 'bg-white border-gray-200 hover:border-blue-400 hover:shadow-lg hover:-translate-y-1 cursor-pointer group';
                    $iconStyle = 'text-gray-300 group-hover:text-blue-500';
                    $textStyle = 'text-gray-600 group-hover:text-blue-600';
                    $dotStyle  = '<span class="absolute top-2 right-2 w-1.5 h-1.5 bg-emerald-400 rounded-full animate-pulse"></span>';
                }
            @endphp

            <button 
                type="button"
                {{-- PERBAIKAN: Menggunakan clickSeat sesuai request --}}
                wire:click="clickSeat({{ $unit->id }})"
                
                {{-- Matikan tombol HANYA jika rusak/maintenance. --}}
                {{-- Jangan matikan jika 'booked', karena mungkin itu milik user sendiri --}}
                @disabled(!$isFunctional)
                
                class="relative w-24 h-24 sm:w-28 sm:h-28 rounded-2xl border flex flex-col items-center justify-center transition-all duration-300 ease-out {{ $cardStyle }}"
            >
                {!! $dotStyle !!}

                {{-- Icon Monitor --}}
                <div class="mb-2 transition-colors duration-300 {{ $iconStyle }}">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                </div>

                {{-- Nama PC --}}
                <span class="text-xs font-bold truncate max-w-[90%] {{ $textStyle }}">
                    {{ $unit->name }}
                </span>
            </button>

        @endforeach

    </div>

    {{-- 3. DEKORASI BAWAH: PINTU --}}
    <div class="mt-auto w-full flex flex-col items-center opacity-60">
        <div class="w-48 h-1 bg-gray-200 rounded-full mb-1"></div>
        <div class="w-24 h-4 bg-gray-800 rounded-sm flex items-center justify-center relative shadow-sm">
            <div class="absolute right-2 w-3 h-1 bg-gray-500 rounded-full"></div>
        </div>
        <span class="text-[10px] font-bold text-gray-400 mt-2 uppercase tracking-widest">Pintu Keluar</span>
    </div>

</div>