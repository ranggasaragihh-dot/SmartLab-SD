<div wire:poll.30s class="flex flex-col h-screen bg-slate-50 font-sans text-slate-900 overflow-hidden">
    <div class="flex-1 px-6 pb-6 overflow-hidden flex flex-col"> 
        <div class="bg-white border border-slate-200 rounded-xl shadow-sm flex flex-col h-full overflow-hidden">
            <div class="p-4 border-b border-slate-200 bg-white z-20 flex flex-col lg:flex-row lg:items-center justify-between gap-4 shrink-0">
                <div class="flex items-center gap-4">
                    <a href="{{ route('admin.booking.dashboard') }}" 
                       class="w-10 h-10 flex items-center justify-center rounded-full bg-slate-50 border border-slate-200 text-slate-500 hover:bg-blue-50 hover:text-blue-600 hover:border-blue-200 transition-all"
                       title="Kembali ke Dashboard">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    </a>

                    <div class="h-8 w-px bg-slate-200 hidden sm:block"></div>

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

                <div class="flex flex-wrap items-center gap-2">
                    <div class="relative group">
                        <input type="date" wire:model.live="dateFilter" class="pl-3 pr-2 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm text-slate-700 focus:ring-1 focus:ring-slate-900 outline-none shadow-sm transition hover:border-slate-300 font-medium w-auto">
                    </div>

                    <div class="relative group">
                        <select wire:model.live="sessionFilter" 
                                class="pl-3 pr-8 py-2 bg-white border border-slate-200 rounded-lg text-sm text-slate-700 focus:ring-1 focus:ring-slate-900 outline-none shadow-sm transition hover:border-slate-300 font-medium">
                            <option value="">🕒 Jam Sekarang</option>
                            <option value="1">Sesi 1 (08:00 - 10:00)</option>
                            <option value="2">Sesi 2 (10:00 - 12:00)</option>
                            <option value="3">Sesi 3 (13:00 - 15:00)</option>
                        </select>
                    </div>

                    <div class="h-8 w-px bg-slate-200 mx-1 hidden sm:block"></div>

                    <select wire:model.live="statusFilter" class="pl-3 pr-8 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm text-slate-700 focus:ring-2 focus:ring-blue-500 outline-none shadow-sm cursor-pointer font-medium">
                        <option value="">Semua Status</option>
                        <option value="available">Available</option>
                        <option value="booked">Booked</option>
                        <option value="broken">Rusak</option>
                        <option value="maintenance">Maintenance</option>
                    </select>

                    <button wire:click="$set('showClusterBookingModal', true)" 
                        class="bg-slate-900 hover:bg-slate-800 text-white px-4 py-2 rounded-lg text-sm font-bold shadow-md shadow-slate-200 transition-all flex items-center gap-2 ml-2 transform active:scale-95">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        <span class="hidden sm:inline">Book 1 Ruangan (Event)</span>
                    </button>
                </div>
            </div>

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
                        <tr class="transition-colors group {{ $pc->activeBooking ? 'bg-slate-50/80 border-l-4 border-l-slate-900' : 'hover:bg-slate-50/50 border-l-4 border-l-transparent' }}">
                            <td class="px-6 py-4 align-middle">
                                <span class="w-10 h-10 rounded-lg {{ $pc->activeBooking ? 'bg-slate-900 text-white shadow-md' : 'bg-white border-slate-200 text-slate-500' }} border flex items-center justify-center font-mono font-bold text-sm transition-all">
                                    {{ substr($pc->name, -2) }}
                                </span>
                            </td>

                            <td class="px-6 py-4 align-middle">
                                <div class="font-bold text-slate-700">{{ $pc->name }}</div>
                                <div class="text-xs text-slate-400 font-mono mt-0.5">{{ $pc->specs ?? 'Standard Configuration' }}</div>
                                <div class="mt-2">
                                    @if($pc->last_seen_at && $pc->last_seen_at->greaterThanOrEqualTo(now()->subSeconds(30)))
                                        <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-bold text-emerald-700">
                                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                            Online
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-bold text-slate-600">
                                            <span class="h-1.5 w-1.5 rounded-full bg-slate-400"></span>
                                            Offline
                                        </span>
                                        <div class="mt-0.5 text-[10px] text-slate-400">
                                            Terakhir terlihat:
                                            {{ $pc->last_seen_at?->format('d/m/Y H:i:s') ?? 'Belum pernah terhubung' }}
                                        </div>
                                    @endif
                                </div>
                            </td>

                            <td class="px-6 py-4 align-middle text-center">
                                @if($pc->status == 'broken')
                                    <span class="inline-flex px-2 py-1 rounded bg-red-50 text-red-600 text-[10px] font-bold border border-red-100 uppercase tracking-wide">Rusak</span>
                                @elseif($pc->status == 'maintenance')
                                    <span class="inline-flex px-2 py-1 rounded bg-amber-50 text-amber-600 text-[10px] font-bold border border-amber-100 uppercase tracking-wide">Maint.</span>
                                @else
                                    <span class="text-slate-400 text-[10px] font-bold uppercase tracking-wide">Normal</span>
                                @endif
                            </td>

                            <td class="px-6 py-4 align-middle">
                                @if($pc->activeBooking)
                                    <div class="flex items-start gap-3">
                                        <div class="w-1 h-8 rounded-full {{ $pc->activeBooking->type == 'event' ? 'bg-purple-500' : 'bg-slate-400' }}"></div>
                                        <div>
                                            <div class="font-bold text-slate-800 flex items-center gap-2 {{ $pc->activeBooking->type == 'event' ? 'text-purple-800' : 'text-slate-800' }}">
                                                @if($pc->activeBooking->type == 'event')
                                                    <span class="text-[9px] bg-amber-100 text-amber-700 px-1.5 py-0.5 rounded font-black uppercase tracking-tighter">EVENT RUANGAN</span>
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

                            <td class="px-6 py-4 align-middle text-right">
                                @if($pc->activeBooking)
                                    <div class="flex items-center justify-end gap-1.5">
                                        <button wire:click="openFinishModal({{ $pc->activeBooking->id }})" class="px-3 py-1.5 rounded-lg bg-white border border-slate-200 text-slate-700 text-xs font-bold hover:bg-emerald-50 hover:text-emerald-600 hover:border-emerald-200 transition-all shadow-sm">
                                            Finish
                                        </button>
                                        <button wire:click="openCancelModal({{ $pc->activeBooking->id }})" class="px-3 py-1.5 rounded-lg bg-white border border-slate-200 text-slate-700 text-xs font-bold hover:bg-red-50 hover:text-red-600 hover:border-red-200 transition-all shadow-sm">
                                            Cancel
                                        </button>
                                    </div>
                                @elseif($pc->status == 'available')
                                    <button wire:click="openCreateUnitModal({{ $pc->id }})" class="text-slate-900 hover:bg-slate-900 hover:text-white font-bold text-xs border border-slate-900 bg-white px-4 py-1.5 rounded-lg transition-all shadow-sm transform active:scale-95">
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

            <div class="p-4 border-t border-slate-200 bg-slate-50 text-xs text-slate-500 flex justify-between items-center shrink-0">
                <span>Menampilkan <strong>{{ $inventories->count() }}</strong> unit</span>
            </div>
        </div>
    </div>

    @include('livewire.admin.partials.booking.modals.create-manual')
    @include('livewire.admin.partials.booking.modals.create-cluster-booking')
    @include('livewire.admin.partials.booking.modals.cancel-booking')
    @include('livewire.admin.partials.booking.modals.finish-booking')
</div>