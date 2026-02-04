<div class="flex flex-col h-screen bg-slate-50 font-sans text-slate-900 overflow-hidden">

    {{-- ====================================================================== --}}
    {{-- BAGIAN 1: STICKY HEADER (CLEAN & COMPACT) --}}
    {{-- Berisi: Navigasi Kembali & Semua Alat Kontrol (Filter/Action) --}}
    {{-- ====================================================================== --}}
    <div class="sticky top-0 z-30 bg-white border-b border-slate-200 shadow-sm shrink-0">
        <div class="flex flex-col md:flex-row md:items-center justify-between px-6 py-4 gap-4">
            
            {{-- KIRI: Navigasi & Judul Konteks --}}
            <div class="flex items-center gap-4">
                {{-- Tombol Kembali --}}
                <a href="{{ route('admin.booking.dashboard') }}" class="group flex items-center gap-2 text-slate-500 hover:text-blue-700 transition-colors pr-4 border-r border-slate-200">
                    <div class="p-1.5 rounded-md group-hover:bg-blue-50 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    </div>
                    <span class="text-sm font-bold hidden md:inline">Dashboard</span>
                </a>

                {{-- Judul Halaman --}}
                <div>
                    <div class="flex items-center gap-2">
                        <h1 class="text-xl font-bold text-slate-800 tracking-tight">Cluster {{ $cluster->name }}</h1>
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-500 border border-slate-200">
                            {{ $inventories->count() }} Unit
                        </span>
                    </div>
                    {{-- Subjudul Tanggal Aktif --}}
                    <p class="text-xs text-blue-600 font-medium mt-0.5">
                        Jadwal: {{ \Carbon\Carbon::parse($dateFilter)->isoFormat('dddd, D MMMM Y') }}
                    </p>
                </div>
            </div>

            {{-- KANAN: Filter & Action (Compact Row) --}}
            <div class="flex items-center gap-2 md:gap-3">
                
                {{-- 1. Filter Tanggal --}}
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="w-4 h-4 text-slate-400 group-focus-within:text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    </div>
                    <input type="date" 
                           wire:model.live="dateFilter"
                           class="pl-9 pr-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm font-medium text-slate-700 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:bg-white transition-all w-36 md:w-auto">
                </div>

                <div class="relative group" title="Filter jam spesifik untuk cek ketersediaan">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <input type="time" wire:model.live="timeFilter" class="pl-9 pr-2 w-28 bg-slate-50 border border-slate-200 rounded-lg text-sm px-3 py-2 font-medium text-slate-700 focus:ring-blue-500 placeholder-slate-400">
                </div>

                {{-- 2. Filter Status --}}
                <select wire:model.live="statusFilter" 
                        class="pl-3 pr-8 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm font-medium text-slate-700 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:bg-white transition-all cursor-pointer">
                    <option value="">Semua Status</option>
                    <option value="available">Available</option>
                    <option value="booked">Booked</option>
                    <option value="broken">Rusak</option>
                    <option value="maintenance">Maintenance</option>
                </select>

                {{-- 3. Tombol Booking Cluster (Ungu) --}}
                <button wire:click="$set('showClusterBookingModal', true)"
                        class="flex items-center gap-2 bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg text-sm font-bold shadow-sm hover:shadow-md transition-all active:scale-95 ml-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                    <span class="hidden md:inline">Book Event</span>
                    <span class="md:hidden">Book</span>
                </button>
            </div>
        </div>
    </div>

    {{-- ====================================================================== --}}
    {{-- BAGIAN 2: CONTENT AREA (DATA TABLE) --}}
    {{-- ====================================================================== --}}
    <div class="flex-1 overflow-y-auto p-4 md:p-6 pb-24"> 

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">

            {{-- 1. AVAILABLE (SIAP PAKAI) --}}
            {{-- Logic: Status fisik 'available' DAN tidak ada activeBooking --}}
            <div class="bg-white p-3 rounded-xl border border-slate-200 shadow-sm flex items-center justify-between">
                <span class="text-xs font-bold text-slate-400 uppercase">Available</span>
                <span class="text-lg font-bold text-green-600">
                    {{ $inventories->where('status', 'available')->filter(fn($pc) => !$pc->activeBooking)->count() }}
                </span>
            </div>

            {{-- 2. BOOKED (SEDANG DIPAKAI) --}}
            {{-- Logic: Hitung inventory yang punya activeBooking --}}
            <div class="bg-white p-3 rounded-xl border border-slate-200 shadow-sm flex items-center justify-between">
                <span class="text-xs font-bold text-slate-400 uppercase">Booked</span>
                <span class="text-lg font-bold text-blue-600">
                    {{ $inventories->filter(fn($pc) => $pc->activeBooking)->count() }}
                </span>
            </div>

            {{-- 3. RUSAK --}}
            <div class="bg-white p-3 rounded-xl border border-slate-200 shadow-sm flex items-center justify-between">
                <span class="text-xs font-bold text-slate-400 uppercase">Rusak</span>
                <span class="text-lg font-bold text-red-500">
                    {{ $inventories->where('status', 'broken')->count() }}
                </span>
            </div>

            {{-- 4. MAINTENANCE --}}
            <div class="bg-white p-3 rounded-xl border border-slate-200 shadow-sm flex items-center justify-between">
                <span class="text-xs font-bold text-slate-400 uppercase">Maint.</span>
                <span class="text-lg font-bold text-amber-500">
                    {{ $inventories->where('status', 'maintenance')->count() }}
                </span>
            </div>

        </div>

        {{-- Tabel Inventory --}}
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="text-xs font-bold text-slate-500 uppercase bg-slate-50 border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-3 w-1/4">Unit PC</th>
                            <th class="px-6 py-3 w-1/6 text-center">Kondisi</th>
                            <th class="px-6 py-3 w-1/3">Status Jadwal</th>
                            <th class="px-6 py-3 text-right">Kontrol</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($inventories as $pc)
                        <tr class="hover:bg-slate-50/80 transition-colors group">
                            
                            {{-- Info Unit --}}
                            <td class="px-6 py-3 align-middle">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded bg-slate-100 text-slate-500 flex items-center justify-center font-bold text-xs">
                                        {{ substr($pc->name, -2) }} {{-- Ambil 2 digit terakhir nama --}}
                                    </div>
                                    <div>
                                        <div class="font-bold text-slate-700">{{ $pc->name }}</div>
                                        <div class="text-[10px] text-slate-400 font-mono">{{ $pc->specs ?? 'Standard Spec' }}</div>
                                    </div>
                                </div>
                            </td>

                            {{-- Kondisi Fisik --}}
                            <td class="px-6 py-3 align-middle text-center">
                                @if($pc->status == 'broken')
                                    <span class="inline-flex px-2 py-1 rounded bg-red-50 text-red-600 text-[10px] font-bold border border-red-100 uppercase tracking-wide">Rusak</span>
                                @elseif($pc->status == 'maintenance')
                                    <span class="inline-flex px-2 py-1 rounded bg-amber-50 text-amber-600 text-[10px] font-bold border border-amber-100 uppercase tracking-wide">Maint.</span>
                                @else
                                    <span class="text-slate-400 text-[10px] font-bold uppercase tracking-wide">Normal</span>
                                @endif
                            </td>

                            {{-- Status Jadwal --}}
                            <td class="px-6 py-3 align-middle">
                                @if($pc->activeBooking)
                                    <div class="flex items-center gap-3">
                                        <div class="w-1 h-8 rounded-full {{ $pc->activeBooking->type == 'event' ? 'bg-purple-500' : 'bg-blue-500' }}"></div>
                                        <div>
                                            <div class="text-xs font-bold {{ $pc->activeBooking->type == 'event' ? 'text-purple-700' : 'text-slate-700' }}">
                                                {{ $pc->activeBooking->user->name ?? 'User' }}
                                            </div>
                                            <div class="text-[10px] text-slate-500">
                                                {{ \Carbon\Carbon::parse($pc->activeBooking->start_time)->format('H:i') }} - 
                                                {{ \Carbon\Carbon::parse($pc->activeBooking->end_time)->format('H:i') }}
                                                @if($pc->activeBooking->type == 'event')
                                                    <span class="text-purple-500 font-bold ml-1">(Event)</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @elseif($pc->status == 'available')
                                    <span class="text-green-600 text-xs font-bold flex items-center gap-1.5">
                                        <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> Available
                                    </span>
                                @else
                                    <span class="text-slate-300 text-xs italic">Off-limits</span>
                                @endif
                            </td>

                            {{-- Aksi --}}
                            <td class="px-6 py-4 align-middle text-right">
                                @if($pc->activeBooking)
                                    {{-- Tombol FINISH (Hijau/Biru) --}}
                                    <button wire:click="openFinishModal({{ $pc->activeBooking->id }})" 
                                            class="text-emerald-600 hover:bg-emerald-50 border border-emerald-200 text-[10px] font-bold px-2 py-1 rounded mr-1">
                                        Finish
                                    </button>

                                    {{-- Tombol CANCEL (Merah) --}}
                                    <button wire:click="openCancelModal({{ $pc->activeBooking->id }})" 
                                            class="text-red-600 hover:bg-red-50 border border-red-200 text-[10px] font-bold px-2 py-1 rounded">
                                        Cancel
                                    </button>
                                @elseif($pc->status == 'available')
                                    {{-- Tombol CREATE (Biru) --}}
                                    <button wire:click="openCreateModal({{ $pc->id }})" 
                                            class="text-blue-600 hover:bg-blue-50 border border-blue-200 text-[10px] font-bold px-2 py-1 rounded">
                                        + Manual
                                    </button>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="p-8 text-center text-slate-400 text-sm">
                                Tidak ada data yang sesuai filter.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    @include('livewire.admin.partials.booking.modals.create-manual')
    @include('livewire.admin.partials.booking.modals.create-cluster-booking')
    @include('livewire.admin.partials.booking.modals.cancel-booking')
    @include('livewire.admin.partials.booking.modals.finish-booking')

</div>