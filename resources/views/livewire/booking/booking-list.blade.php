{{-- WRAPPER UTAMA: Kunci Tinggi Layar (h-screen) & Matikan Scroll Body (overflow-hidden) --}}
<div class="flex flex-col h-screen bg-slate-50 font-sans text-slate-900 overflow-hidden">

    {{-- ====================================================================== --}}
    {{-- BAGIAN 2: SCROLLABLE CONTENT (Isi Halaman) --}}
    {{-- ====================================================================== --}}
    <div class="flex-1 overflow-y-auto p-4 md:p-8 space-y-6 pb-24"> 
        {{-- pb-24 agar konten paling bawah tidak tertutup --}}

        {{-- INFO CLUSTER --}}
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
            <div class="flex items-center gap-3 mb-2">
                <span class="px-3 py-1 rounded-md text-xs font-bold bg-green-100 text-green-700 border border-green-200">
                    CLUSTER {{ $cluster->name ?? 'CL-01' }}
                </span>
                <span class="text-sm text-slate-500 font-medium">| Total {{ $inventories->count() }} Unit</span>
            </div>
            <h2 class="text-2xl font-bold text-slate-800 leading-none">
                {{ $cluster->name }}
            </h2>
        </div>

        {{-- CONTAINER DATA --}}
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            
            {{-- A. TABEL DESKTOP --}}
            <div class="hidden md:block">
                <table class="w-full text-sm text-left">
                    <thead class="text-sm font-bold text-slate-800 border-b border-slate-200 bg-white">
                        <tr>
                            <th class="px-6 py-4">Kode Unit</th>
                            <th class="px-6 py-4">Spesifikasi</th>
                            <th class="px-6 py-4 text-center">Kondisi Fisik</th>
                            <th class="px-6 py-4 text-center">Status Jadwal</th>
                            <th class="px-6 py-4 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($inventories as $pc)
                        <tr class="hover:bg-slate-50 transition-colors duration-200">
                            
                            {{-- Kode & Specs --}}
                            <td class="px-6 py-4 align-middle font-medium text-slate-700">{{ $pc->name }}</td>
                            <td class="px-6 py-4 align-middle text-slate-600">RTX 3060 / 16GB RAM</td>

                            {{-- Kondisi Fisik --}}
                            <td class="px-6 py-4 align-middle text-center">
                                @if($pc->status == 'broken')
                                    <span class="inline-block px-3 py-1 rounded-md bg-red-50 text-red-600 text-xs font-bold border border-red-100">Rusak</span>
                                @elseif($pc->status == 'maintenance')
                                    <span class="inline-block px-3 py-1 rounded-md bg-orange-50 text-orange-600 text-xs font-bold border border-orange-100">Perbaikan</span>
                                @else
                                    <span class="text-slate-500 text-xs font-bold">Normal</span>
                                @endif
                            </td>

                            {{-- Status Jadwal --}}
                            <td class="px-6 py-4 align-middle text-center">
                            @if($pc->activeBooking && $pc->activeBooking->status == 'booked')
                                    <div class="flex flex-col items-center">
                                        <span class="inline-flex items-center px-3 py-1 rounded-md text-xs font-bold bg-indigo-50 text-indigo-600 border border-indigo-100">
                                            Terbooking
                                        </span>
                                        <span class="text-[10px] text-slate-500 mt-1 font-medium">
                                        {{ \Carbon\Carbon::parse($pc->activeBooking->start_time)->format('H:i') }}
                                        - 
                                        {{ \Carbon\Carbon::parse($pc->activeBooking->end_time)->format('H:i') }}
                                    </span>
                                </div>
                            @elseif($pc->status != 'available')
                                    <span class="inline-flex items-center px-3 py-1 rounded-md text-xs font-bold bg-slate-100 text-slate-500 border border-slate-200">
                                        Unavailable
                                    </span>
                            @else
                                {{-- Style Hijau Pastel (Mirip 'Bimbingan Selesai') --}}
                                <span class="inline-flex items-center px-3 py-1 rounded-md text-xs font-bold bg-green-100 text-green-700 border border-green-200">
                                    Tersedia
                                </span>
                                @endif
                            </td>

                            {{-- Aksi --}}
                            <td class="px-6 py-4 align-middle text-right">
                                @php
                                    // Helper variables agar logika blade lebih bersih
                                    $myBooking = $pc->activeBooking && $pc->activeBooking->user_id == auth()->id();
                                    $now = \Carbon\Carbon::now();
                                    $startTime = $pc->activeBooking ? \Carbon\Carbon::parse($pc->activeBooking->start_time) : null;
                                @endphp

                                {{-- KONDISI 1: JIKA INI BOOKINGAN SAYA --}}
                                @if($myBooking)
                                    
                                    {{-- Skenario A: Waktu Belum Mulai DAN Status masih 'booked' (Belum check-in/scan) --}}
                                    {{-- User bisa Cancel --}}
                                    @if($now->lt($startTime) && $pc->activeBooking->status == 'booked')
                                        <button wire:click="cancelBooking({{ $pc->activeBooking->id }})" 
                                                wire:confirm="Yakin ingin membatalkan booking ini?"
                                                class="bg-red-500 hover:bg-red-600 text-white text-xs font-bold px-5 py-2.5 rounded-md shadow-sm transition-transform active:scale-95 ml-auto">
                                            Batalkan
                                        </button>

                                    {{-- Skenario B: Waktu Sudah Mulai ATAU Status sudah 'active' (Sudah scan) --}}
                                    {{-- User bisa Selesai --}}
                                    @else
                                        <button wire:click="finishBooking({{ $pc->activeBooking->id }})" 
                                                wire:confirm="Apakah Anda sudah selesai menggunakan PC ini?"
                                                class="bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold px-5 py-2.5 rounded-md shadow-sm transition-transform active:scale-95 ml-auto">
                                            Selesai
                                        </button>
                                    @endif

                                {{-- KONDISI 2: JIKA PC KOSONG & AVAILABLE --}}
                                @elseif(!$pc->activeBooking && $pc->status == 'available')
                                    
                                    <button wire:click="clickSeat({{ $pc->id }})" 
                                            class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold px-5 py-2.5 rounded-md shadow-sm transition-transform active:scale-95 ml-auto">
                                        Booking
                                    </button>

                                {{-- KONDISI 3: BOOKINGAN ORANG LAIN / RUSAK / MAINTENANCE --}}
                                @else
                                    
                                    <button disabled class="text-slate-300 font-bold text-xs cursor-not-allowed px-2">
                                        @if($pc->activeBooking)
                                            Full {{-- Dibooking orang lain --}}
                                        @else
                                            {{ $pc->status == 'maintenance' ? 'MT' : 'Off' }}
                                        @endif
                                    </button>

                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- B. LIST CARD MOBILE (Optimized) --}}
            <div class="md:hidden bg-slate-50 border-t border-slate-100 p-4 space-y-3">
                @foreach($inventories as $pc)
                
                {{-- Container Kartu: Flex Row untuk Desktop-like feel di Mobile --}}
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 flex items-center justify-between gap-3 {{ $pc->status != 'available' ? 'bg-slate-50' : '' }}">
                    
                    {{-- BAGIAN KIRI: Info PC (Nama, Specs, Status Teks) --}}
                    <div class="flex-1 min-w-0"> {{-- min-w-0 agar truncate berfungsi --}}
                        <div class="flex items-center gap-2 mb-1">
                            <h3 class="font-bold text-slate-800 text-lg leading-none">{{ $pc->name }}</h3>
                            
                            {{-- Badge Status Mini --}}
                            @if($pc->status == 'broken')
                                <span class="w-2 h-2 rounded-full bg-red-500"></span>
                            @elseif($pc->status == 'maintenance')
                                <span class="w-2 h-2 rounded-full bg-orange-500"></span>
                            @endif
                        </div>

                        {{-- Specs --}}
                        <p class="text-[10px] text-slate-500 font-medium truncate">RTX 3060 / 16GB RAM</p>

                        {{-- Status Booking Teks --}}
                        @php $activeBookingMobile = $pc->bookings->where('status', 'booked')->first(); @endphp
                        @if($activeBookingMobile)
                            <p class="text-[10px] text-indigo-600 font-bold mt-1 truncate">
                                Booked s.d {{ \Carbon\Carbon::parse($activeBookingMobile->end_time)->format('H:i') }}
                            </p>
                        @elseif($pc->status == 'available')
                            <p class="text-[10px] text-green-600 font-bold mt-1">Available</p>
                        @else
                            <p class="text-[10px] text-slate-400 font-bold mt-1 uppercase">{{ $pc->status }}</p>
                        @endif
                    </div>
                    
                    {{-- BAGIAN KANAN: Tombol (Kecil tapi Jelas) --}}
                    <div class="shrink-0">
                        @php
                            // Helper variables agar logika blade lebih bersih
                            $myBooking = $pc->activeBooking && $pc->activeBooking->user_id == auth()->id();
                            $now = \Carbon\Carbon::now();
                            $startTime = $pc->activeBooking ? \Carbon\Carbon::parse($pc->activeBooking->start_time) : null;
                        @endphp

                        {{-- KONDISI 1: JIKA INI BOOKINGAN SAYA --}}
                        @if($myBooking)
                                    
                            {{-- Skenario A: Waktu Belum Mulai DAN Status masih 'booked' (Belum check-in/scan) --}}
                            {{-- User bisa Cancel --}}
                            @if($now->lt($startTime) && $pc->activeBooking->status == 'booked')
                                <button wire:click="cancelBooking({{ $pc->activeBooking->id }})" 
                                        wire:confirm="Yakin ingin membatalkan booking ini?"
                                        class="bg-red-500 hover:bg-red-600 text-white text-xs font-bold px-5 py-2.5 rounded-md shadow-sm transition-transform active:scale-95 ml-auto">
                                    Batalkan
                                </button>

                            {{-- Skenario B: Waktu Sudah Mulai ATAU Status sudah 'active' (Sudah scan) --}}
                            {{-- User bisa Selesai --}}
                            @else
                                <button wire:click="finishBooking({{ $pc->activeBooking->id }})" 
                                        wire:confirm="Apakah Anda sudah selesai menggunakan PC ini?"
                                        class="bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold px-5 py-2.5 rounded-md shadow-sm transition-transform active:scale-95 ml-auto">
                                    Selesai
                                </button>
                            @endif

                        {{-- KONDISI 2: JIKA PC KOSONG & AVAILABLE --}}
                        @elseif(!$pc->activeBooking && $pc->status == 'available')
                                    
                            <button wire:click="clickSeat({{ $pc->id }})" 
                                    class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold px-5 py-2.5 rounded-md shadow-sm transition-transform active:scale-95 ml-auto">
                                Booking
                            </button>

                        {{-- KONDISI 3: BOOKINGAN ORANG LAIN / RUSAK / MAINTENANCE --}}
                        @else
                                    
                            <button disabled class="text-slate-300 font-bold text-xs cursor-not-allowed px-2">
                                @if($pc->activeBooking)
                                    Full {{-- Dibooking orang lain --}}
                                @else
                                    {{ $pc->status == 'maintenance' ? 'MT' : 'Off' }}
                                @endif
                            </button>

                        @endif
                    </div>

                </div>
                @endforeach
            </div>

        </div>

        {{-- Pagination --}}
        <div class="pt-2">
             
        </div>

    </div>
</div>