<div class="flex flex-col h-screen bg-slate-50 font-sans text-slate-900 overflow-hidden">

    {{-- ====================================================================== --}}
    {{-- BAGIAN 2: MAIN CONTENT (UNIFIED CARD) --}}
    {{-- ====================================================================== --}}
    <div class="flex-1 px-6 pb-6 overflow-hidden flex flex-col"> 
        
        {{-- UNIFIED CARD CONTAINER --}}
        <div class="bg-white border border-slate-200 rounded-xl shadow-sm flex flex-col h-full overflow-hidden">
            
            {{-- A. CARD HEADER & TOOLBAR (Sticky di dalam Card) --}}
            <div class="p-4 border-b border-slate-200 bg-white z-20 flex flex-col lg:flex-row lg:items-center justify-between gap-4 shrink-0">
                
                {{-- KIRI: Identitas Cluster --}}
                <div class="flex items-center gap-4">
                    
                    {{-- TOMBOL KEMBALI (Menyatu dalam Header) --}}
                    <a href="{{ route('admin.booking.dashboard') }}" 
                       class="w-10 h-10 flex items-center justify-center rounded-full bg-slate-50 border border-slate-200 text-slate-500 hover:bg-blue-50 hover:text-blue-600 hover:border-blue-200 transition-all"
                       title="Kembali ke Dashboard">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    </a>

                    {{-- GARIS PEMISAH VERTIKAL --}}
                    <div class="h-8 w-px bg-slate-200 hidden sm:block"></div>

                    {{-- INFO CLUSTER --}}
                    <div>
                        <div class="flex items-center gap-3">
                            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Cluster {{ $cluster->name }}</h1>
                            <span class="px-2.5 py-0.5 rounded-full bg-slate-100 text-slate-600 text-xs font-bold border border-slate-200">
                                {{ $inventories->count() }} Unit
                            </span>
                        </div>
                        <p class="text-xs text-slate-500 mt-0.5 font-medium">
                            Jadwal: {{ \Carbon\Carbon::parse($dateFilter)->isoFormat('dddd, D MMMM Y') }}
                        </p>
                    </div>
                </div>

                {{-- KANAN: Filter Controls Group --}}
                <div class="flex flex-wrap items-center gap-2">
                    
                    {{-- Date Picker --}}
                    <div class="relative group">
                        <input type="date" wire:model.live="dateFilter" class="pl-3 pr-2 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm text-slate-700 focus:ring-2 focus:ring-blue-500 outline-none shadow-sm transition hover:border-blue-300 cursor-pointer font-medium w-auto">
                    </div>

                    {{-- Time Picker --}}
                    <div class="relative group">
                        <input type="time" wire:model.live="timeFilter" class="pl-3 pr-2 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm text-slate-700 focus:ring-2 focus:ring-blue-500 outline-none shadow-sm transition hover:border-blue-300 cursor-pointer font-medium w-auto">
                    </div>

                    {{-- Divider --}}
                    <div class="h-8 w-px bg-slate-200 mx-1 hidden sm:block"></div>

                    {{-- Status Dropdown --}}
                    <select wire:model.live="statusFilter" class="pl-3 pr-8 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm text-slate-700 focus:ring-2 focus:ring-blue-500 outline-none shadow-sm cursor-pointer font-medium">
                        <option value="">Semua Status</option>
                        <option value="available">Available</option>
                        <option value="booked">Booked</option>
                        <option value="broken">Rusak</option>
                        <option value="maintenance">Maintenance</option>
                    </select>

                    {{-- Primary Action --}}
                    <button wire:click="$set('showClusterBookingModal', true)" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-medium shadow-md shadow-indigo-100 transition flex items-center gap-2 ml-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                        <span class="hidden sm:inline">Book Event</span>
                    </button>
                </div>
            </div>

            {{-- B. CARD BODY (TABEL SCROLLABLE) --}}
            <div class="flex-1 overflow-auto bg-white relative">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-slate-50/80 sticky top-0 z-10 backdrop-blur-sm shadow-sm">
                        <tr class="text-xs uppercase tracking-wider text-slate-500 font-bold border-b border-slate-200">
                            <th class="px-6 py-4 w-20">Unit</th>
                            <th class="px-6 py-4">Info Spesifikasi</th>
                            <th class="px-6 py-4 w-32 text-center">Kondisi</th>
                            <th class="px-6 py-4 w-1/3">Status Jadwal</th>
                            <th class="px-6 py-4 text-right">Kontrol</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm">
                        @forelse($inventories as $pc)
                        
                        {{-- Logika Style Baris: Jika Booked -> Highlight Biru --}}
                        <tr class="transition-colors group {{ $pc->activeBooking ? 'bg-blue-50/40 border-l-4 border-l-blue-500' : 'hover:bg-slate-50 border-l-4 border-l-transparent' }}">
                            
                            {{-- Kolom 1: Unit ID --}}
                            <td class="px-6 py-4 align-middle">
                                <span class="w-10 h-10 rounded-lg {{ $pc->activeBooking ? 'bg-white border-blue-200 text-blue-700 shadow-sm' : 'bg-slate-100 text-slate-500' }} border flex items-center justify-center font-bold text-sm">
                                    {{ substr($pc->name, -2) }}
                                </span>
                            </td>

                            {{-- Kolom 2: Info --}}
                            <td class="px-6 py-4 align-middle">
                                <div class="font-bold text-slate-700">{{ $pc->name }}</div>
                                <div class="text-xs text-slate-400 font-mono mt-0.5">{{ $pc->specs ?? 'Standard Configuration' }}</div>
                            </td>

                            {{-- Kolom 3: Kondisi (Badge) --}}
                            <td class="px-6 py-4 align-middle text-center">
                                @if($pc->status == 'broken')
                                    <span class="inline-flex px-2 py-1 rounded bg-red-50 text-red-600 text-[10px] font-bold border border-red-100 uppercase tracking-wide">Rusak</span>
                                @elseif($pc->status == 'maintenance')
                                    <span class="inline-flex px-2 py-1 rounded bg-amber-50 text-amber-600 text-[10px] font-bold border border-amber-100 uppercase tracking-wide">Maint.</span>
                                @else
                                    <span class="text-slate-400 text-[10px] font-bold uppercase tracking-wide">Normal</span>
                                @endif
                            </td>

                            {{-- Kolom 4: Status Jadwal --}}
                            <td class="px-6 py-4 align-middle">
                                @if($pc->activeBooking)
                                    <div class="flex items-start gap-3">
                                        {{-- Indikator Warna Tipe Booking --}}
                                        <div class="w-1 h-8 rounded-full {{ $pc->activeBooking->type == 'event' ? 'bg-purple-500' : 'bg-blue-500' }}"></div>
                                        <div>
                                            <div class="font-bold {{ $pc->activeBooking->type == 'event' ? 'text-purple-800' : 'text-slate-800' }}">
                                                {{ $pc->activeBooking->user->name ?? 'User' }}
                                                @if($pc->activeBooking->type == 'event')
                                                    <span class="ml-1 text-[10px] bg-purple-100 text-purple-700 px-1.5 py-0.5 rounded uppercase tracking-wide">Event</span>
                                                @endif
                                            </div>
                                            <div class="text-xs text-slate-500 font-mono mt-0.5 flex items-center gap-1">
                                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                {{ \Carbon\Carbon::parse($pc->activeBooking->start_time)->format('H:i') }} - 
                                                {{ \Carbon\Carbon::parse($pc->activeBooking->end_time)->format('H:i') }}
                                            </div>
                                        </div>
                                    </div>
                                @elseif($pc->status == 'available')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 text-xs font-bold border border-emerald-100">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                        Available
                                    </span>
                                @else
                                    <span class="text-slate-300 text-xs italic">Off-limits</span>
                                @endif
                            </td>

                            {{-- Kolom 5: Kontrol --}}
                            <td class="px-6 py-4 align-middle text-right">
                                @if($pc->activeBooking)
                                    <div class="flex items-center justify-end gap-2">
                                        <button wire:click="openFinishModal({{ $pc->activeBooking->id }})" class="px-3 py-1.5 rounded bg-white border border-emerald-200 text-emerald-600 text-xs font-bold hover:bg-emerald-50 shadow-sm transition">
                                            Finish
                                        </button>
                                        <button wire:click="openCancelModal({{ $pc->activeBooking->id }})" class="px-3 py-1.5 rounded bg-white border border-red-200 text-red-600 text-xs font-bold hover:bg-red-50 shadow-sm transition">
                                            Cancel
                                        </button>
                                    </div>
                                @elseif($pc->status == 'available')
                                    <button wire:click="openCreateUnitModal({{ $pc->id }})" class="text-blue-600 hover:text-blue-800 font-medium text-xs border border-blue-200 hover:border-blue-400 bg-blue-50 hover:bg-white px-3 py-1.5 rounded transition shadow-sm">
                                        + Manual
                                    </button>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="py-12 text-center">
                                <div class="flex flex-col items-center justify-center text-slate-400">
                                    <svg class="w-12 h-12 mb-3 opacity-20" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    <span class="text-sm font-medium">Tidak ada unit yang sesuai filter.</span>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- C. FOOTER (PAGINATION) --}}
            <div class="p-4 border-t border-slate-200 bg-slate-50 text-xs text-slate-500 flex justify-between items-center shrink-0">
                <span>Menampilkan <strong>{{ $inventories->count() }}</strong> unit</span>
                {{-- Jika ada pagination link, render di sini --}}
            </div>

        </div>
        {{-- END UNIFIED CARD --}}

    </div>

    {{-- Modals --}}
    @include('livewire.admin.partials.booking.modals.create-manual')
    @include('livewire.admin.partials.booking.modals.create-cluster-booking')
    @include('livewire.admin.partials.booking.modals.cancel-booking')
    @include('livewire.admin.partials.booking.modals.finish-booking')

</div>