<div>
    <x-slot name="header">Overview</x-slot>

    {{-- 1. STATS CARDS --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        
        {{-- Card 1: Booking Hari Ini --}}
        <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100 flex items-center">
            <div class="p-3 rounded-full bg-blue-50 text-blue-600 mr-4">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
            </div>
            <div>
                <p class="text-gray-500 text-sm font-medium">Booking Hari Ini</p>
                <h3 class="text-2xl font-bold text-gray-800">{{ $stats['today_bookings'] }}</h3>
            </div>
        </div>

        {{-- Card 2: Sedang Digunakan --}}
        <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100 flex items-center">
            <div class="p-3 rounded-full bg-emerald-50 text-emerald-600 mr-4">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <div>
                <p class="text-gray-500 text-sm font-medium">Sesi Aktif</p>
                <h3 class="text-2xl font-bold text-gray-800">{{ $stats['active_now'] }}</h3>
            </div>
        </div>

        {{-- Card 3: PC Maintenance --}}
        <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100 flex items-center">
            <div class="p-3 rounded-full bg-rose-50 text-rose-600 mr-4">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            </div>
            <div>
                <p class="text-gray-500 text-sm font-medium">PC Maintenance</p>
                <h3 class="text-2xl font-bold text-gray-800">{{ $stats['maintenance_pc'] }}</h3>
            </div>
        </div>

        {{-- Card 4: Total User --}}
        <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100 flex items-center">
            <div class="p-3 rounded-full bg-indigo-50 text-indigo-600 mr-4">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
            </div>
            <div>
                <p class="text-gray-500 text-sm font-medium">Total Booking</p>
                <h3 class="text-2xl font-bold text-gray-800">{{ $stats['total_bookings'] }}</h3>
            </div>
        </div>
    </div>

    {{-- 2. RECENT ACTIVITY TABLE --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
            <h3 class="font-bold text-gray-700">Aktivitas Terbaru</h3>
            <a href="#" class="text-sm text-blue-600 hover:underline">Lihat Semua</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-600">
                <thead class="text-xs text-gray-500 uppercase bg-gray-50">
                    <tr>
                        <th class="px-6 py-3">User</th>
                        <th class="px-6 py-3">Unit / PC</th>
                        <th class="px-6 py-3">Waktu</th>
                        <th class="px-6 py-3">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentBookings as $booking)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="px-6 py-4 font-medium text-gray-800">
                                {{ $booking->user->name ?? 'Unknown' }}
                            </td>
                            <td class="px-6 py-4">
                                {{ $booking->inventory->name ?? '-' }}
                            </td>
                            <td class="px-6 py-4">
                                {{ \Carbon\Carbon::parse($booking->start_time)->format('d M, H:i') }}
                            </td>
                            <td class="px-6 py-4">
                                @php
                                    $statusClass = match($booking->status) {
                                        'active' => 'bg-green-100 text-green-700',
                                        'booked' => 'bg-blue-100 text-blue-700',
                                        'cancelled' => 'bg-red-100 text-red-700',
                                        'finished' => 'bg-gray-100 text-gray-600',
                                        default => 'bg-gray-100 text-gray-600'
                                    };
                                @endphp
                                <span class="px-2 py-1 rounded-full text-xs font-bold uppercase {{ $statusClass }}">
                                    {{ $booking->status }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-10 text-center text-gray-400">
                                Belum ada booking terbaru.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>