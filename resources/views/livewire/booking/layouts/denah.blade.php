@php
    // 1. Defensive Coding (Anti Error Undefined Variable)
    $isAdmin = $isAdmin ?? false;
    $items = $items ?? collect();
    $mode = $mode ?? 'booking';

    // 2. Mapping Data Database berdasarkan Nama
    $units = $items->keyBy('name');
    $currentUserId = auth()->id();
@endphp

<div class="bg-white p-16 rounded-[3rem] shadow-xl border border-gray-100 min-h-[600px] flex flex-col items-center relative max-w-7xl mx-auto select-none">

    {{-- DEKORASI: LAYAR PROYEKTOR (ATAS) --}}
    <div class="w-full flex flex-col items-center mb-16">
        <div class="w-2/3 h-4 bg-gray-800 rounded-full shadow-lg mb-2 relative overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-r from-gray-700 via-gray-500 to-gray-700 opacity-50"></div>
        </div>
        <span class="text-xs font-bold text-gray-400 uppercase tracking-[0.3em]">Layar Proyektor / Papan Tulis</span>
        <div class="w-1/2 h-8 bg-blue-100 blur-xl opacity-20 rounded-full -mt-2"></div>
    </div>

    {{-- GRID DENAH HARDCODED (3 KOLOM) --}}
    {{-- Struktur ini akan selalu muncul meskipun database kosong --}}
    <div class="grid grid-cols-3 gap-x-24 gap-y-10 w-full justify-items-center">
        
        {{-- BARIS 1 --}}
        {{-- Perhatikan penambahan prop 'label' --}}
        <div><x-lab-seat :data="$units['Meja-03'] ?? null" label="Meja-03" :isAdmin="$isAdmin" :currentUserId="$currentUserId" :mode="$mode" /></div>
        <div><x-lab-seat :data="$units['Meja-01'] ?? null" label="Meja-01" :isAdmin="$isAdmin" :currentUserId="$currentUserId" :mode="$mode" /></div>
        <div><x-lab-seat :data="$units['AIO-01'] ?? null"  label="AIO-01"  :isAdmin="$isAdmin" :currentUserId="$currentUserId" :mode="$mode ?? 'booking'" /></div>

        {{-- BARIS 2 --}}
        <div><x-lab-seat :data="$units['AIO-10'] ?? null" label="AIO-10" :isAdmin="$isAdmin" :currentUserId="$currentUserId" :mode="$mode" /></div>
        {{-- Jalan Tengah --}}
        <div class="flex items-center justify-center opacity-10 text-6xl font-black text-gray-300 tracking-widest rotate-90 whitespace-nowrap pointer-events-none">:::</div>
        <div><x-lab-seat :data="$units['Meja-02'] ?? null" label="Meja-02" :isAdmin="$isAdmin" :currentUserId="$currentUserId" :mode="$mode" /></div>

        {{-- BARIS 3 --}}
        <div><x-lab-seat :data="$units['AIO-09'] ?? null" label="AIO-09" :isAdmin="$isAdmin" :currentUserId="$currentUserId" :mode="$mode" /></div>
        <div></div> {{-- Spasi Kosong --}}
        <div><x-lab-seat :data="$units['AIO-02'] ?? null" label="AIO-02" :isAdmin="$isAdmin" :currentUserId="$currentUserId" :mode="$mode" /></div>

        {{-- BARIS 4 --}}
        <div><x-lab-seat :data="$units['AIO-08'] ?? null" label="AIO-08" :isAdmin="$isAdmin" :currentUserId="$currentUserId" :mode="$mode" /></div>
        <div></div>
        <div><x-lab-seat :data="$units['AIO-03'] ?? null" label="AIO-03" :isAdmin="$isAdmin" :currentUserId="$currentUserId" :mode="$mode" /></div>

        {{-- BARIS 5 --}}
        <div><x-lab-seat :data="$units['AIO-07'] ?? null" label="AIO-07" :isAdmin="$isAdmin" :currentUserId="$currentUserId" :mode="$mode" /></div>
        <div></div>
        <div><x-lab-seat :data="$units['AIO-04'] ?? null" label="AIO-04" :isAdmin="$isAdmin" :currentUserId="$currentUserId" :mode="$mode" /></div>

        {{-- BARIS 6 --}}
        <div><x-lab-seat :data="$units['AIO-06'] ?? null" label="AIO-06" :isAdmin="$isAdmin" :currentUserId="$currentUserId" :mode="$mode" /></div>
        <div></div>
        <div><x-lab-seat :data="$units['AIO-05'] ?? null" label="AIO-05" :isAdmin="$isAdmin" :currentUserId="$currentUserId" :mode="$mode" /></div>
    </div>

    {{-- DEKORASI: PINTU (BAWAH) --}}
    <div class="mt-20 w-full flex flex-col items-center">
        <div class="relative flex flex-col items-center group cursor-default">
            <div class="w-48 h-2 bg-gray-200 rounded-full mb-1"></div>
            <div class="w-32 h-4 bg-gray-800 rounded-sm shadow-md flex items-center justify-center relative overflow-hidden">
                <div class="absolute right-4 w-4 h-1 bg-gray-500 rounded-full"></div>
            </div>
            <span class="text-xs font-bold text-gray-400 mt-2 uppercase tracking-widest">Pintu Masuk / Keluar</span>
        </div>
    </div>

</div>