@props(['data' => null, 'isAdmin' => false, 'label' => 'Unit', 'currentUserId' => null, 'mode' => 'booking'])

@php
    $boxSize = "relative w-32 h-24 flex-none overflow-hidden rounded-2xl flex flex-col items-center justify-center border-2 transition-all duration-200 group";
    
    // Default Status
    $myBooking = false;
    $isBooked = false;

    if ($data) {
        // LOGIKA SEDERHANA:
        // 1. Cari booking yang statusnya spesifik 'booked' (abaikan status lain)
        //    Kita pakai 'where' agar tidak terambil history lama yang statusnya 'completed'/'cancelled'
        $bookingBooked = $data->bookings->whereIn('status', ['active', 'booked',])->first();

        if ($bookingBooked) {
            $isBooked = true;

            // 2. Cek apakah User ID sama
            if ($bookingBooked->user_id == $currentUserId) {
                $myBooking = true;
            }
        }
    }
    $isDisabled = false;
    $mode = $mode;

    if ($mode === 'manage') {
        // MODE MANAGE (TEKNISI): 
        // Semua tombol HARUS HIDUP agar bisa di-toggle (kunci/buka), kecuali benar-benar error data
        $isDisabled = false; 
    } else {
        // MODE BOOKING (ADMIN & USER):
        // Tombol mati jika:
        // 1. Kursi dikunci admin (!is_bookable)
        // 2. Kursi rusak (broken)
        // 3. Punya orang lain (User biasa tidak boleh klik, Admin boleh klik untuk lihat info)
        
        if (!$data || !$data->is_bookable || $data->status == 'broken') {
            $isDisabled = true; 
        } 
        elseif (!$isAdmin && ($isBooked && !$myBooking)) {
            $isDisabled = true;
        }
    }
@endphp

@if(!$data)
    {{-- ======================================================================== --}}
    {{-- KONDISI 1: DATA KOSONG (PENDING)                                         --}}
    {{-- ======================================================================== --}}
    <div class="{{ $boxSize }} border-dashed border-gray-300 bg-gray-50 opacity-60 select-none cursor-default">
        
        {{-- Ikon (Fixed Size & Shrink) --}}
        <svg class="w-8 h-8 mb-1 text-gray-300 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
        </svg>
        
        {{-- Label Container --}}
        <div class="flex flex-col items-center w-full px-1">
            <span class="font-bold text-xs text-gray-400 mb-0.5 w-full text-center truncate px-1">
                {{ $label }}
            </span>
            <span class="text-[8px] uppercase tracking-wider text-gray-300 font-bold leading-none">
                (PENDING)
            </span>
        </div>
    </div>

@else
    {{-- ======================================================================== --}}
    {{-- KONDISI 2: DATA ADA (AKTIF)                                              --}}
    {{-- ======================================================================== --}}
    <button 
        wire:click="clickSeat ({{ $data->id }})"
        @if(!$isAdmin && (!$data->is_bookable || $data->status == 'broken')) disabled @endif
        
        @if($isDisabled) disabled @endif

        class="{{ $boxSize }} hover:scale-105 shadow-sm
        
        {{-- LOGIKA WARNA --}}
        @if(!$data->is_bookable) 
            bg-gray-100 border-gray-200 text-gray-300 cursor-not-allowed {{-- DITUTUP ADMIN --}}
            {{ $mode === 'manage' ? 'cursor-pointer hover:bg-green-50 hover:scale-105' : 'cursor-not-allowed' }}
            
        @elseif($data->status == 'broken') 
            bg-red-50 border-red-200 text-red-400 cursor-not-allowed {{-- RUSAK --}}
            {{ $mode === 'manage' ? 'cursor-pointer hover:scale-105' : 'cursor-not-allowed' }}
            
        @elseif($data->status == 'maintenance') 
            bg-gray-100 border-gray-200 text-gray-400 cursor-not-allowed opacity-75"
            {{ $mode === 'manage' ? 'cursor-pointer hover:bg-green-50 hover:scale-105' : 'cursor-not-allowed' }}
        
        @elseif($isBooked) 
            @if($isAdmin)
                {{-- Admin melihat BIRU --}}
                bg-blue-400
            @elseif($myBooking)
                {{-- User Saya melihat KUNING --}}
                bg-amber-400
            @else
                {{-- User Lain melihat ABU --}}
                bg-gray-100 border-gray-300 text-gray-400 cursor-not-allowed
            @endif
            
        @else 
            {{-- KOSONG (HIJAU) --}}
            bg-green-50 border-green-300 text-green-700 hover:bg-green-100 hover:shadow-green-50 hover:border-green-400
        @endif
        "
    >
        {{-- Ikon (Fixed Size) --}}
        <svg class="w-8 h-8 mb-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
        </svg>

        {{-- Label Container --}}
        <div class="flex flex-col items-center w-full px-1">
            <span class="font-bold text-xs w-full text-center truncate px-1 {{ $isAdmin && !$data->is_bookable ? 'line-through decoration-gray-400' : '' }}">
                {{ $data->name }}
            </span>
            {{-- Spacer Kosong (Agar tinggi teks sama dengan kotak Pending) --}}
            <span class="text-[8px] h-[8px]">
                @if($myBooking) MILIK SAYA
                @elseif($data->status == 'broken') RUSAK
                @elseif($data->status == 'maintenance') MAINTENANCE
                @elseif($isBooked) BOOKED @endif
            </span> 
        </div>

        {{-- Gembok Admin --}}
        @if($isAdmin)
            <div class="absolute top-1 right-1 bg-white/80 backdrop-blur rounded-full p-1 shadow-sm border z-10">
                <span class="text-[20px] block w-5 h-4 text-center leading-3">{{ $data->is_bookable ? '🔓' : '🔒' }}</span>
            </div>
        @endif
    </button>
@endif