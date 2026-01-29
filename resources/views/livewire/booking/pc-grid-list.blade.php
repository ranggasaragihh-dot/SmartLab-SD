@props(['units' => [], 'isAdmin' => false, 'currentUserId' => null, 'mode' => 'booking'])

<div class="w-full grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-6 justify-items-center mb-10">

    @foreach($units as $unit)
       @php
            // --- 1. DETEKSI DATA (One-Liner) ---
            // Mengambil booking aktif (jika ada)
            $activeBooking = $unit->bookings->whereIn('status', ['booked', 'active'])->first();
            
            // Cek apakah booking tersebut milik saya (Return true/false langsung)
            $isMyBooking = $activeBooking && ((int)$activeBooking->user_id === (int)$currentUserId);

            // --- 2. LOGIKA VISUAL & AKSES ---
            // Default: Disabled (False)
            $isDisabled = true; 

            // PRIORITAS 1: Rusak
            if ($unit->status == 'broken') {
                $cardStyle = 'bg-red-50 border-red-200 text-red-400';

            // PRIORITAS 2: Maintenance / Closed
            } elseif ($unit->status == 'maintenance' || !$unit->is_bookable) {
                $cardStyle = 'bg-gray-100 border-gray-200 text-gray-400 opacity-75';

            // PRIORITAS 3: Booked (Gunakan Ternary untuk mempersingkat if-else)
            } elseif ($activeBooking) {
                // Jika punya saya: Kuning. Jika orang lain: Abu.
                $cardStyle = $isMyBooking 
                    ? 'bg-amber-100 border-amber-300 text-amber-700' 
                    : 'bg-gray-100 border-gray-300 text-gray-400';
                
                // Khusus punya saya, tombol aktif
                $isDisabled = !$isMyBooking;

            // PRIORITAS 4: Available
            } else {
                $cardStyle = 'bg-green-50 border-green-300 text-green-700 hover:bg-green-100 hover:shadow-md hover:border-green-400';
                $isDisabled = false;
            }

            // --- 3. FINALISASI STYLE ---
            // Gabungkan string class cursor
            $cardStyle .= $isDisabled ? ' cursor-not-allowed' : ' cursor-pointer hover:scale-105';
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

            <span class="text-[8px] h-[8px]">
                @if($isMyBooking) MILIK SAYA @endif
            </span> 
        </button>

    @endforeach

</div>