@props(['units' => [], 'isAdmin' => false, 'currentUserId' => null, 'mode' => 'booking'])

<div class="w-full grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-6 justify-items-center mb-10">

    @foreach($units as $unit)
        @php
            // 1. Tentukan Status Dasar
            $isBooked = $unit->is_booked; // Pastikan di model/query ada status ini
            $isMyBooking = ($isBooked && $unit->user_id == $currentUserId); // Cek kepemilikan
            $isDisabled = false;
            $cardStyle = '';
            $textStyle = '';
            $dotStyle = ''; // Opsional jika ada dot indikator

            // 2. Logika Warna & Cursor
            
            if ($unit->status == 'broken') {
                // KASUS: RUSAK
                $cardStyle = 'bg-red-50 border-red-200 text-red-400';
                $isDisabled = ($mode !== 'manage');

            } elseif ($unit->status == 'maintenance') {
                // KASUS: MAINTENANCE
                $cardStyle = 'bg-gray-100 border-gray-200 text-gray-400 opacity-75';
                $isDisabled = ($mode !== 'manage');

            } elseif ($isBooked) {
                // KASUS: SUDAH DIBOOKING
                if ($isMyBooking) {
                    $cardStyle = 'bg-amber-100 border-amber-300 text-amber-700'; // Punya Sendiri
                    $isDisabled = false; // User bisa klik punya sendiri (misal untuk batal)
                } else {
                    $cardStyle = 'bg-gray-100 border-gray-300 text-gray-400'; // Punya Orang Lain
                    $isDisabled = true;
                }

            } else {
                // KASUS: AVAILABLE (HIJAU)
                $cardStyle = 'bg-green-50 border-green-300 text-green-700 hover:bg-green-100 hover:shadow-md hover:border-green-400 cursor-pointer';
                $isDisabled = false;
            }

            // 3. Override Cursor jika Disabled
            if ($isDisabled) {
                $cardStyle .= ' cursor-not-allowed';
            } else {
                $cardStyle .= ' cursor-pointer hover:scale-105';
            }
        @endphp

        <button 
            type="button"
            {{-- PENTING: wire:click hanya aktif jika tidak disabled --}}
            @if(!$isDisabled) wire:click="clickSeat({{ $unit->id }})" @endif
            @if($isDisabled) disabled @endif
            class="relative w-24 h-24 sm:w-28 sm:h-28 rounded-2xl border flex flex-col items-center justify-center transition-all duration-300 ease-out {{ $cardStyle }}"
        >
            {{-- Icon Monitor --}}
            <div class="mb-2 transition-colors duration-300">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
            </div>

            {{-- Nama PC --}}
            <span class="text-xs font-bold truncate max-w-[90%]">
                {{ $unit->name }}
            </span>

            {{-- Indikator Status (Opsional) --}}
            @if($isMyBooking)
                <span class="absolute top-1 right-1 flex h-3 w-3">
                  <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                  <span class="relative inline-flex rounded-full h-3 w-3 bg-amber-500"></span>
                </span>
            @endif
        </button>

    @endforeach

</div>