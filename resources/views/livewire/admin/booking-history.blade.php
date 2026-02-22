<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center gap-5">
        <a href="{{ route('admin.dashboard') }}" 
        wire:navigate
        class="group flex items-center justify-center w-10 h-10 rounded-full border border-slate-200 bg-white shadow-sm hover:bg-slate-50 hover:border-slate-300 transition-all active:scale-90">
            <svg class="w-5 h-5 text-slate-500 group-hover:text-slate-900 transition-colors" 
                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
            </svg>
        </a>

        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight flex items-center gap-3">
                Riwayat Transaksi
                <span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-500 text-[10px] font-bold uppercase tracking-widest border border-slate-200">
                    Audit Log
                </span>
            </h1>
            <p class="text-sm text-slate-500 font-medium mt-0.5">Kelola dan audit seluruh data reservasi ATMA-LAB.</p>
        </div>
    </div>

    <div class="bg-white p-4 rounded-2xl shadow-sm border border-slate-100 flex flex-col lg:flex-row gap-4 items-center">
        <div class="relative flex-1 w-full">
            <input type="text" wire:model.live.debounce.500ms="search" placeholder="Cari Nama User, Email, atau ID Unit..." 
                   class="w-full pl-10 pr-4 py-2 border-slate-200 rounded-xl text-sm focus:ring-1 focus:ring-slate-900 focus:border-slate-900 bg-slate-50 font-medium transition-all">
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>
        </div>

        <div class="flex flex-col sm:flex-row gap-3 w-full lg:w-auto">
            <select wire:model.live="filterStatus" class="border-slate-200 rounded-xl text-sm py-2 pl-3 pr-8 focus:ring-1 focus:ring-slate-900 bg-slate-50 font-bold text-slate-600 cursor-pointer">
                <option value="">Semua Status</option>
                <option value="active">🟢 Active</option>
                <option value="booked">🔵 Booked</option>
                <option value="finished">⚪ Finished</option>
                <option value="cancelled">🔴 Cancelled</option>
            </select>
            <input type="date" wire:model.live="filterDate" class="border-slate-200 rounded-xl text-sm py-2 px-3 focus:ring-1 focus:ring-slate-900 bg-slate-50 font-bold text-slate-600 cursor-pointer">
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden flex flex-col">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-slate-900 border-b border-slate-800 text-[10px] text-slate-400 uppercase tracking-[0.2em] font-black">
                    <tr>
                        <th class="px-6 py-4 rounded-tl-xl">Ref ID</th>
                        <th class="px-6 py-4 text-white">Identitas User</th>
                        <th class="px-6 py-4 text-white">Aset / Kategori</th>
                        <th class="px-6 py-4 text-white">Slot Waktu</th>
                        <th class="px-6 py-4 text-right rounded-tr-xl text-white">Status Akhir</th>
                    </tr>
                </thead>
                <tbody class="text-sm divide-y divide-slate-50">
                    @forelse($bookings as $booking)
                        <tr wire:click="openDetailSidebar({{ $booking->id }})" 
                            class="hover:bg-slate-50 cursor-pointer transition-colors group {{ $selectedBooking && $selectedBooking->id === $booking->id ? 'bg-slate-100/80 border-l-4 border-slate-900' : 'border-l-4 border-transparent' }}">
                            <td class="px-6 py-4 font-mono text-xs font-bold">
                                <span class="text-slate-300">#</span><span class="text-slate-900">{{ str_pad($booking->id, 4, '0', STR_PAD_LEFT) }}</span>
                            </td>
                            
                            <td class="px-6 py-4 group-hover:text-slate-900">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-slate-900 text-white flex items-center justify-center text-[10px] font-black shadow-sm">
                                        {{ strtoupper(substr($booking->user->name ?? 'U', 0, 1)) }}
                                    </div>
                                    <div class="flex flex-col">
                                        <span class="font-bold text-slate-800 text-xs">{{ $booking->user->name ?? 'Unknown' }}</span>
                                        <span class="text-[10px] text-slate-400 font-medium tracking-tight">
                                            {{ $booking->user->nomor_induk ?? $booking->user->email }}
                                        </span>
                                    </div>
                                </div>
                            </td>
                            
                            <td class="px-6 py-4">
                                @if($booking->type === 'event' || !$booking->inventory_id)
                                    <span class="inline-flex px-2 py-0.5 rounded bg-amber-50 text-amber-700 text-[10px] font-black uppercase tracking-wider border border-amber-100">
                                        Event Full Ruangan
                                    </span>
                                @else
                                    <div class="flex flex-col">
                                        <span class="font-mono font-bold text-slate-700">{{ $booking->inventory->name ?? 'Unknown' }}</span>
                                        <div class="flex items-center gap-1.5 mt-0.5">
                                            <span class="text-[10px] text-blue-600 font-bold bg-blue-50 px-1.5 rounded border border-blue-100 uppercase tracking-tighter">
                                                {{ $booking->inventory->item->category->name ?? 'Aset' }}
                                            </span>
                                            <span class="text-[10px] text-slate-400">
                                                {{ $booking->inventory->cluster->name ?? 'Tanpa Cluster' }}
                                            </span>
                                        </div>
                                    </div>
                                @endif
                            </td>
                            
                            <td class="px-6 py-4 text-slate-600 text-xs">
                                <span class="font-bold">{{ \Carbon\Carbon::parse($booking->start_time)->format('d M Y') }}</span>
                                <span class="text-slate-400 ml-1">({{ \Carbon\Carbon::parse($booking->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($booking->end_time)->format('H:i') }})</span>
                            </td>
                            
                            <td class="px-6 py-4 text-right">
                                @php
                                    $statusClass = match($booking->status) {
                                        'active'    => 'bg-emerald-50 text-emerald-700 border-emerald-200 shadow-sm shadow-emerald-100/50',
                                        'booked'    => 'bg-blue-50 text-blue-700 border-blue-200',
                                        'cancelled' => 'bg-rose-50 text-rose-700 border-rose-200 animate-pulse-subtle', // Sedikit sentuhan perhatian
                                        'finished'  => 'bg-slate-50 text-slate-400 border-slate-200 opacity-75',
                                        default     => 'bg-white text-slate-500 border-slate-200'
                                    };
                                @endphp
                                <span class="px-2.5 py-1 rounded-md text-[10px] font-black uppercase tracking-wider border {{ $statusClass }}">
                                    {{ $booking->status }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-20 text-center">
                                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-slate-50 mb-4">
                                    <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                </div>
                                <p class="text-slate-900 font-black text-base italic">"Data Tidak Ditemukan"</p>
                                <p class="text-slate-500 text-xs mt-1 max-w-xs mx-auto">Kami tidak menemukan hasil untuk <span class="font-bold text-slate-900">"{{ $search }}"</span>. Pastikan NIM atau ID Unit sudah benar.</p>
                                <button wire:click="$set('search', '')" class="mt-4 text-xs font-bold text-blue-600 hover:text-blue-800 underline">Bersihkan Pencarian</button>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="p-4 border-t border-slate-100 bg-slate-50 shrink-0">
            {{ $bookings->links() }}
        </div>
    </div>

    @include('livewire.admin.partials.booking.booking-details-sidebar')
</div>