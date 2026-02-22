<div class="flex flex-col h-screen bg-slate-50 font-sans text-slate-900 overflow-hidden">
    <div class="bg-white border-b border-slate-200 px-8 py-5 shrink-0 z-20">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold text-slate-800">Manajemen Booking</h1>
                <p class="text-sm text-slate-500">Kelola ketersediaan unit dan jadwal ruangan.</p>
            </div>

            <div class="flex flex-col md:flex-row gap-3">
                <div class="relative">
                    <input type="date" 
                           wire:model.live="dateFilter"
                           class="pl-3 pr-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 shadow-sm text-slate-600">
                </div>

                <select wire:model.live="statusFilter" 
                        class="pl-3 pr-8 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 shadow-sm text-slate-600">
                    <option value="">Semua Status</option>
                    <option value="available">Tersedia</option>
                    <option value="booked">Terbooking</option>
                    <option value="broken">Rusak</option>
                    <option value="maintenance">Maintenance</option>
                </select>

                <div class="hidden md:block w-px h-10 bg-slate-200 mx-1"></div>

                <button wire:click="$set('showClusterBookingModal', true)"
                        class="flex items-center justify-center gap-2 bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg text-sm font-bold shadow-md shadow-purple-200 transition-all active:scale-95">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2-2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    Book Satu Ruangan
                </button>
            </div>
        </div>
    </div>

    <div class="flex-1 overflow-y-auto p-4 md:p-8 space-y-6 pb-24 relative"> 
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 flex justify-between items-center">
            <div>
                <div class="flex items-center gap-3 mb-2">
                    <span class="px-3 py-1 rounded-md text-xs font-bold bg-blue-100 text-blue-700 border border-blue-200">
                        ADMIN MODE
                    </span>
                    <span class="text-sm text-slate-500 font-medium">| {{ \Carbon\Carbon::parse($dateFilter)->isoFormat('dddd, D MMMM Y') }}</span>
                </div>
                <h2 class="text-2xl font-bold text-slate-800 leading-none">
                    Cluster {{ $cluster->name ?? 'CL-01' }}
                </h2>
            </div>
            <div class="text-right">
                <span class="block text-3xl font-bold text-slate-800">{{ $inventories->count() }}</span>
                <span class="text-xs text-slate-500 font-bold uppercase tracking-wider">Total Unit</span>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <table class="w-full text-sm text-left">
                <thead class="text-sm font-bold text-slate-800 border-b border-slate-200 bg-slate-50">
                    <tr>
                        <th class="px-6 py-4">Unit</th>
                        <th class="px-6 py-4 text-center">Status Fisik</th>
                        <th class="px-6 py-4">Detail Booking Saat Ini</th>
                        <th class="px-6 py-4 text-right">Admin Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($inventories as $pc)
                    <tr class="hover:bg-slate-50 transition-colors duration-200">
                        <td class="px-6 py-4 align-middle">
                            <div class="font-bold text-slate-700 text-base">{{ $pc->name }}</div>
                            <div class="text-xs text-slate-400">ID: #{{ $pc->id }}</div>
                        </td>

                        <td class="px-6 py-4 align-middle text-center">
                            @if($pc->status == 'broken')
                                <span class="px-2 py-1 bg-red-100 text-red-700 rounded text-xs font-bold">Rusak</span>
                            @elseif($pc->status == 'maintenance')
                                <span class="px-2 py-1 bg-orange-100 text-orange-700 rounded text-xs font-bold">Maintenance</span>
                            @else
                                <span class="px-2 py-1 bg-green-100 text-green-700 rounded text-xs font-bold">Normal</span>
                            @endif
                        </td>

                        <td class="px-6 py-4 align-middle">
                            @if($pc->activeBooking)
                                <div class="flex items-start gap-3">
                                    <div class="bg-indigo-50 p-2 rounded text-indigo-600">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                    </div>
                                    <div>
                                        <div class="font-bold text-slate-700 text-xs">
                                            {{ $pc->activeBooking->user->name ?? 'Unknown User' }}
                                        </div>
                                        <div class="text-[10px] text-slate-500 mt-0.5">
                                            {{ \Carbon\Carbon::parse($pc->activeBooking->start_time)->format('H:i') }} - 
                                            {{ \Carbon\Carbon::parse($pc->activeBooking->end_time)->format('H:i') }}
                                        </div>
                                        <div class="text-[10px] text-indigo-500 font-medium mt-1">
                                            {{ $pc->activeBooking->description ?? 'Booking reguler' }}
                                        </div>
                                    </div>
                                </div>
                            @else
                                <span class="text-slate-400 text-xs italic">Tidak ada jadwal aktif</span>
                            @endif
                        </td>

                        <td class="px-6 py-4 align-middle text-right">
                            @if($pc->activeBooking)
                                <button wire:click="adminCancelBooking({{ $pc->activeBooking->id }})" 
                                        wire:confirm="ADMIN ACTION: Batalkan paksa booking user ini?"
                                        class="text-red-600 hover:text-red-800 text-xs font-bold border border-red-200 hover:bg-red-50 px-3 py-1.5 rounded transition-colors">
                                    Force Cancel
                                </button>
                            @elseif($pc->status == 'available')
                                <button wire:click="openManualBookingModal({{ $pc->id }})" 
                                        class="text-blue-600 hover:text-blue-800 text-xs font-bold border border-blue-200 hover:bg-blue-50 px-3 py-1.5 rounded transition-colors">
                                    + Admin Book
                                </button>
                            @else
                                <span class="text-xs text-slate-300 font-bold cursor-not-allowed">Unavailable</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="p-8 text-center text-slate-500">
                            Tidak ada unit yang sesuai dengan filter.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($showClusterBookingModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-sm p-4">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg overflow-hidden animate-in fade-in zoom-in duration-200">
            <div class="bg-purple-600 px-6 py-4 flex justify-between items-center">
                <h3 class="text-white font-bold text-lg">Booking Satu Cluster ({{ $cluster->name }})</h3>
                <button wire:click="$set('showClusterBookingModal', false)" class="text-purple-200 hover:text-white">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <div class="p-6 space-y-4">
                <div class="bg-purple-50 border border-purple-100 p-3 rounded-lg text-sm text-purple-700">
                    <span class="font-bold">Info:</span> Aksi ini akan membooking <strong>SEMUA</strong> unit yang berstatus 'Available' di cluster ini untuk keperluan kelas/event.
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Tanggal</label>
                    <input type="text" value="{{ \Carbon\Carbon::parse($dateFilter)->isoFormat('dddd, D MMMM Y') }}" readonly class="w-full bg-slate-100 border border-slate-300 rounded-lg px-3 py-2 text-sm text-slate-500 cursor-not-allowed">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Jam Mulai</label>
                        <input type="time" wire:model="clusterStartTime" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-purple-500 focus:border-purple-500">
                        @error('clusterStartTime') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Jam Selesai</label>
                        <input type="time" wire:model="clusterEndTime" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-purple-500 focus:border-purple-500">
                        @error('clusterEndTime') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nama Kelas / Event</label>
                    <input type="text" wire:model="clusterEventName" placeholder="Contoh: Praktikum Basis Data A" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-purple-500 focus:border-purple-500">
                    @error('clusterEventName') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="bg-slate-50 px-6 py-4 flex justify-end gap-3 border-t border-slate-100">
                <button wire:click="$set('showClusterBookingModal', false)" class="px-4 py-2 text-sm font-bold text-slate-600 hover:text-slate-800 hover:bg-slate-200 rounded-lg transition-colors">
                    Batal
                </button>
                <button wire:click="bookEntireCluster" class="px-4 py-2 text-sm font-bold text-white bg-purple-600 hover:bg-purple-700 rounded-lg shadow-md hover:shadow-lg transition-all">
                    Konfirmasi Booking Cluster
                </button>
            </div>
        </div>
    </div>
    @endif
</div>