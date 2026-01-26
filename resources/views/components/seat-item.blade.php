@props(['unit', 'action'])

@php
    // Logika warna dipindah ke sini agar HTML utama bersih
    $status = 'available';
    if (!$unit) $status = 'missing';
    elseif ($unit->status == 'maintenance') $status = 'maintenance';
    // ... logika isBooked / isMyBooking ...
    
    $colors = match($status) {
        'available' => 'bg-gray-100 hover:bg-gray-200 text-gray-600',
        'maintenance' => 'bg-red-100 text-red-400',
        'booked' => 'bg-gray-700 opacity-30',
        default => 'bg-gray-50 opacity-50'
    };
@endphp

{{-- Jika unit tidak ada di DB, sembunyikan atau tampilkan error --}}
@if($unit)
<button wire:click="{{ $action }}({{ $unit->id }})" class="{{ $colors }} w-full h-full rounded-lg flex items-center justify-center text-xs font-bold shadow-sm transition-all">
    {{ $unit->name }}
</button>
@else
<div class="w-full h-full border-2 border-dashed border-gray-200 rounded-lg"></div> 
@endif