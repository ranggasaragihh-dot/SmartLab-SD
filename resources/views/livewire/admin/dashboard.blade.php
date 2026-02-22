<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-black text-slate-900 tracking-tight">Dashboard Overview</h1>
        <div class="text-sm font-medium text-slate-500 bg-white px-4 py-2 rounded-lg border border-slate-200 shadow-sm">
            {{ \Carbon\Carbon::now()->isoFormat('dddd, D MMMM Y') }}
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100 flex items-center gap-4">
            <div class="p-3.5 rounded-xl bg-slate-900 text-white shadow-md">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
            </div>
            <div>
                <p class="text-slate-400 text-[10px] font-bold uppercase tracking-wider">Booking Hari Ini</p>
                <div class="flex items-baseline gap-2 mt-0.5">
                    <h3 class="text-2xl font-black text-slate-900">{{ $stats['today_bookings'] }}</h3>
                    <span class="text-[10px] font-medium text-slate-500">Total Sesi</span>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100 flex items-center gap-4">
            <div class="p-3.5 rounded-xl bg-emerald-50 text-emerald-600 border border-emerald-100">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <div>
                <p class="text-slate-400 text-[10px] font-bold uppercase tracking-wider">Sesi Aktif</p>
                <div class="flex items-baseline gap-2 mt-0.5">
                    <h3 class="text-2xl font-black text-slate-900">{{ $stats['active_now'] }}</h3>
                    <span class="text-[10px] font-medium text-emerald-600">Sedang Digunakan</span>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100 flex items-center gap-4">
            <div class="p-3.5 rounded-xl {{ $stats['maintenance_pc'] > 0 ? 'bg-amber-50 text-amber-600 border-amber-100' : 'bg-slate-50 text-slate-400 border-slate-100' }} border">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            </div>
            <div>
                <p class="text-slate-400 text-[10px] font-bold uppercase tracking-wider">PC Maintenance</p>
                <div class="flex items-baseline gap-2 mt-0.5">
                    <h3 class="text-2xl font-black text-slate-900">{{ $stats['maintenance_pc'] }}</h3>
                    <span class="text-[10px] font-medium {{ $stats['maintenance_pc'] > 0 ? 'text-amber-600' : 'text-emerald-500' }}">
                        {{ $stats['maintenance_pc'] > 0 ? 'Perlu Perbaikan' : 'Semua Normal' }}
                    </span>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100 flex items-center gap-4">
            <div class="p-3.5 rounded-xl bg-slate-50 text-slate-600 border border-slate-200">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
            </div>
            <div>
                <p class="text-slate-400 text-[10px] font-bold uppercase tracking-wider">Total Reservasi</p>
                <h3 class="text-2xl font-black text-slate-900 mt-0.5">{{ $stats['total_bookings'] }}</h3>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-white">
            <h3 class="font-black text-slate-800 tracking-tight">Aktivitas Terbaru</h3>
            <a href="{{ route('admin.booking.history')}}" 
            wire:navigate 
            class="text-xs font-bold text-slate-500 hover:text-slate-900 hover:bg-slate-100 px-3 py-1.5 rounded-lg transition-all">
            Lihat Semua Data &rarr;
            </a>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-slate-50 border-b border-slate-100 text-[10px] text-slate-500 uppercase tracking-widest font-bold">
                    <tr>
                        <th class="px-6 py-3">User</th>
                        <th class="px-6 py-3">Unit / Ruang</th>
                        <th class="px-6 py-3">Waktu Transaksi</th>
                        <th class="px-6 py-3 text-right">Status</th>
                    </tr>
                </thead>
                <tbody class="text-sm divide-y divide-slate-50">
                    @forelse($recentBookings as $booking)
                        <tr wire:click="openDetailSidebar({{ $booking->id }})" 
                            class="hover:bg-slate-50 cursor-pointer transition-colors group">
                            
                            <td class="px-6 py-3.5 font-bold text-slate-800 group-hover:text-slate-900">
                                <div class="flex items-center gap-3">
                                    <div class="w-7 h-7 rounded-full bg-slate-100 text-slate-500 flex items-center justify-center text-xs">
                                        {{ substr($booking->user->name ?? 'U', 0, 1) }}
                                    </div>
                                    {{ $booking->user->name ?? 'Unknown' }}
                                </div>
                            </td>
                            
                            <td class="px-6 py-3.5">
                                @if($booking->type === 'event' || !$booking->inventory_id)
                                    <span class="inline-flex px-2 py-0.5 rounded bg-amber-50 text-amber-700 text-[10px] font-black uppercase tracking-wider border border-amber-100">
                                        Event Ruangan
                                    </span>
                                @else
                                    <span class="font-mono font-bold text-slate-600">{{ $booking->inventory->name ?? 'Unknown PC' }}</span>
                                @endif
                            </td>
                            
                            <td class="px-6 py-3.5 text-slate-500 text-xs font-medium">
                                {{ \Carbon\Carbon::parse($booking->start_time)->format('d M Y, H:i') }}
                            </td>
                            
                            <td class="px-6 py-3.5 text-right">
                                @php
                                    $statusClass = match($booking->status) {
                                        'active' => 'bg-emerald-50 text-emerald-700 border-emerald-100',
                                        'booked' => 'bg-slate-100 text-slate-700 border-slate-200',
                                        'cancelled' => 'bg-red-50 text-red-700 border-red-100',
                                        'finished' => 'bg-slate-50 text-slate-400 border-slate-100',
                                        default => 'bg-slate-50 text-slate-500 border-slate-200'
                                    };
                                @endphp
                                <span class="px-2.5 py-1 rounded-md text-[10px] font-black uppercase tracking-wider border {{ $statusClass }}">
                                    {{ $booking->status }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center">
                                <p class="text-slate-400 font-medium text-sm">Belum ada aktivitas transaksi.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @include('livewire.admin.partials.booking.booking-details-sidebar')
</div>