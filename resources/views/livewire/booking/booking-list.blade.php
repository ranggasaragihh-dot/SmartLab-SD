<div class="flex-1 overflow-y-auto p-4 md:p-8 space-y-6 pb-24"> 
    @if (session('success'))
        <div role="status" class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div role="alert" class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700">
            {{ session('error') }}
        </div>
    @endif

    @if(!$cluster)
        <div class="flex flex-col items-center justify-center h-64 text-center">
            <div class="bg-slate-100 p-4 rounded-full mb-4">
                <svg class="w-12 h-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.384-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path>
                </svg>
            </div>
            <h3 class="text-lg font-bold text-slate-700">Belum Ada Lab Tersedia</h3>
            <p class="text-slate-500 max-w-xs mx-auto">
                Admin belum menambahkan data Lab atau Ruangan ke dalam sistem.
            </p>
        </div>
    @else
        <div class="flex min-h-full flex-col bg-slate-50 font-sans text-slate-900 md:h-full md:min-h-0 md:overflow-hidden">
            <div class="flex-1 overflow-y-auto p-4 md:p-8 space-y-6 pb-24"> 
                @if($isRoomLockedByAdmin)
                    <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-r-lg shadow-sm flex items-start gap-3">
                        <div class="text-red-500">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-red-800 font-bold">Akses Cluster Terbatas</h3>
                            <p class="text-red-700 text-sm mt-1">{{ $lockReason }}</p>
                        </div>
                    </div>
                @endif

                @if($roomEvents->isNotEmpty())
                    <div role="status" class="bg-amber-50 border-l-4 border-amber-500 p-4 rounded-r-lg shadow-sm">
                        <h3 class="text-amber-800 font-bold">Ruangan dipakai untuk event</h3>
                        <ul class="text-amber-700 text-sm mt-1 space-y-0.5">
                            @foreach($roomEvents as $event)
                                <li>
                                    <span class="font-bold">{{ $event->start_time->format('H:i') }} - {{ $event->end_time->format('H:i') }}</span>
                                    : {{ $event->purpose ?: 'Kegiatan khusus' }}
                                </li>
                            @endforeach
                        </ul>
                        <p class="text-amber-700 text-xs mt-1">PC di ruangan ini tidak dapat dibooking pada jam tersebut.</p>
                    </div>
                @endif

                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                    <div class="flex items-center gap-3 mb-2">
                        <span class="px-3 py-1 rounded-md text-xs font-bold bg-green-100 text-green-700 border border-green-200">
                            CLUSTER {{ $cluster->name ?? 'CL-01' }}
                            <p class="text-slate-500 text-sm italic">{{ $cluster->description ?? '' }}</p>
                        </span>
                        <span class="text-sm text-slate-500 font-medium">| Total {{ $inventories->count() }} Unit</span>
                    </div>
                    <h2 class="text-2xl font-bold text-slate-800 leading-none">
                        {{ $cluster->name }}
                    </h2>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
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
                                    @php
                                        $bookingData = $bookingsMap->get($pc->id, collect());
                                        $isLocked = in_array($pc->id, $lockedInventories);

                                        $myBookingDetail = $bookingData->firstWhere('user_id', $currentUserId);
                                        $isMine = $myBookingDetail !== null;
                                        $isCheckedIn = false;
                                        $isAwaitingCheckIn = false;
                                        $isExpired = false;

                                        if ($myBookingDetail) {
                                            $now = \Carbon\Carbon::now();
                                            $startTime = \Carbon\Carbon::parse($myBookingDetail->start_time);
                                            $endTime = \Carbon\Carbon::parse($myBookingDetail->end_time);

                                            $isCheckedIn = $myBookingDetail->status === 'active' && $myBookingDetail->check_in_time !== null;
                                            $isAwaitingCheckIn = $myBookingDetail->status === 'booked' && $now->gte($startTime);
                                            $isExpired = $now->gt($endTime);
                                        }
                                    @endphp

                                    <tr class="hover:bg-slate-50 transition-colors duration-200">
                                        <td class="px-6 py-4 align-middle font-medium text-slate-700">{{ $pc->name }}</td>
                                        <td class="px-6 py-4 align-middle text-slate-600">
                                            @if($pc->item && is_array($pc->item->specifications))
                                                <div class="flex flex-col gap-0.5 text-xs">
                                                    <div><span class="font-bold text-slate-700">CPU:</span> {{ $pc->item->specifications['cpu'] ?? '-' }}</div>
                                                    <div><span class="font-bold text-slate-700">RAM:</span> {{ $pc->item->specifications['ram'] ?? '-' }}</div>
                                                    <div><span class="font-bold text-slate-700">Storage:</span> {{ $pc->item->specifications['storage'] ?? '-' }}</div>
                                                    <div><span class="font-bold text-slate-700">VGA:</span> {{ $pc->item->specifications['gpu'] ?? '-' }}</div>
                                                </div>
                                            @else
                                                <span class="text-xs italic text-slate-400">Spesifikasi tidak tersedia</span>
                                            @endif
                                        </td>

                                        <td class="px-6 py-4 align-middle text-center">
                                            @if($pc->status == 'broken')
                                                <span class="inline-block px-3 py-1 rounded-md bg-red-50 text-red-600 text-xs font-bold border border-red-100">Rusak</span>
                                            @elseif($pc->status == 'maintenance')
                                                <span class="inline-block px-3 py-1 rounded-md bg-orange-50 text-orange-600 text-xs font-bold border border-orange-100">Perbaikan</span>
                                            @else
                                                <span class="text-slate-500 text-xs font-bold">Normal</span>
                                            @endif
                                        </td>

                                        <td class="px-6 py-4 align-middle text-center">
                                            @if($bookingData->isNotEmpty())
                                                <div class="flex flex-col items-center">
                                                    @foreach($bookingData as $scheduledBooking)
                                                        <span class="inline-flex items-center px-3 py-1 rounded-md text-xs font-bold {{ $scheduledBooking->user_id == $currentUserId ? 'bg-amber-100 text-amber-600 border border-amber-200' : 'bg-indigo-50 text-indigo-600 border border-indigo-100' }}">
                                                            {{ $scheduledBooking->user_id == $currentUserId ? 'Milik Saya' : 'Terbooking' }}
                                                        </span>
                                                        <span class="text-[10px] text-slate-500 mt-1 mb-1 font-medium">
                                                            {{ \Carbon\Carbon::parse($scheduledBooking->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($scheduledBooking->end_time)->format('H:i') }}
                                                        </span>
                                                    @endforeach
                                                </div>
                                            
                                            @elseif($isLocked)
                                                <span class="text-xs font-bold text-red-400 uppercase">Locked</span>

                                            @elseif($pc->status != 'available')
                                                <span class="inline-flex items-center px-3 py-1 rounded-md text-xs font-bold bg-slate-100 text-slate-500 border border-slate-200">
                                                    Unavailable
                                                </span>
                                            
                                            @else
                                                <span class="inline-flex items-center px-3 py-1 rounded-md text-xs font-bold bg-green-100 text-green-700 border border-green-200">
                                                    Tersedia
                                                </span>
                                            @endif
                                        </td>

                                        <td class="px-6 py-4 align-middle text-right">
                                            @if($isMine && !$isExpired)
                                                @if($isCheckedIn)
                                                    <button wire:click="clickSeat({{ $pc->id }})" class="bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold px-5 py-2.5 rounded-md shadow-sm transition-transform active:scale-95 ml-auto">
                                                        Selesai
                                                    </button>
                                                @elseif($isAwaitingCheckIn)
                                                    <div class="flex items-center justify-end gap-2">
                                                        <span class="inline-flex rounded-md bg-amber-50 px-3 py-2 text-xs font-bold text-amber-700" title="Scan QR di meja untuk check-in">
                                                            Scan QR
                                                        </span>
                                                        <button wire:click="clickSeat({{ $pc->id }})" class="bg-red-500 hover:bg-red-600 text-white text-xs font-bold px-4 py-2.5 rounded-md shadow-sm">
                                                            Batal
                                                        </button>
                                                    </div>
                                                @else
                                                    <button wire:click="clickSeat({{ $pc->id }})" class="bg-red-500 hover:bg-red-600 text-white text-xs font-bold px-5 py-2.5 rounded-md shadow-sm transition-transform active:scale-95 ml-auto">
                                                        Batal
                                                    </button>
                                                @endif
                                            @elseif($isLocked)
                                                <button disabled class="bg-slate-100 text-slate-400 border border-slate-200 text-xs font-bold px-5 py-2.5 rounded-md cursor-not-allowed ml-auto">
                                                    Event Room
                                                </button>
                                            @elseif($pc->status == 'available')
                                                <button wire:click="clickSeat({{ $pc->id }})" class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold px-5 py-2.5 rounded-md shadow-sm transition-transform active:scale-95 ml-auto">
                                                    Booking
                                                </button>
                                            @else
                                                <button disabled class="text-slate-300 font-bold text-xs cursor-not-allowed px-2 ml-auto">
                                                    {{ $pc->status == 'maintenance' ? 'MT' : 'Off' }}
                                                </button>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="md:hidden bg-slate-50 border-t border-slate-100 p-4 space-y-3">
                        @foreach($inventories as $pc)
                            @php
                                $bookingData = $bookingsMap->get($pc->id, collect());
                                $isLocked = in_array($pc->id, $lockedInventories);

                                $myBookingDetail = $bookingData->firstWhere('user_id', $currentUserId);
                                $isMine = $myBookingDetail !== null;
                                $isCheckedIn = false;
                                $isAwaitingCheckIn = false;
                                $isExpired = false;

                                if ($myBookingDetail) {
                                    $now = \Carbon\Carbon::now();
                                    $startTime = \Carbon\Carbon::parse($myBookingDetail->start_time);
                                    $endTime = \Carbon\Carbon::parse($myBookingDetail->end_time);

                                    $isCheckedIn = $myBookingDetail->status === 'active' && $myBookingDetail->check_in_time !== null;
                                    $isAwaitingCheckIn = $myBookingDetail->status === 'booked' && $now->gte($startTime);
                                    $isExpired = $now->gt($endTime);
                                }
                            @endphp
                        
                        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 flex items-center justify-between gap-3 {{ ($bookingData->isNotEmpty() || $pc->status != 'available') ? 'bg-slate-50' : '' }}">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 mb-1">
                                    <h3 class="font-bold text-slate-800 text-lg leading-none">{{ $pc->name }}</h3>
                                    @if($pc->status == 'broken')
                                        <span class="w-2 h-2 rounded-full bg-red-500"></span>
                                    @elseif($pc->status == 'maintenance')
                                        <span class="w-2 h-2 rounded-full bg-orange-500"></span>
                                    @endif
                                </div>

                                @php $specs = ($pc->item && is_array($pc->item->specifications)) ? $pc->item->specifications : []; @endphp
                                <p class="text-[10px] text-slate-500 font-medium truncate">
                                    {{ collect([$specs['cpu'] ?? null, $specs['ram'] ?? null, $specs['gpu'] ?? null])->filter()->implode(' / ') ?: 'Spesifikasi tidak tersedia' }}
                                </p>

                                @if($isMine)
                                    @foreach($bookingData->where('user_id', $currentUserId) as $mySchedule)
                                        <p class="mt-1 text-[10px] font-bold text-amber-600">
                                            Milik Saya: {{ \Carbon\Carbon::parse($mySchedule->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($mySchedule->end_time)->format('H:i') }}
                                        </p>
                                    @endforeach
                                @elseif($bookingData->isNotEmpty())
                                    @foreach($bookingData as $scheduledBooking)
                                        <p class="text-[10px] text-indigo-600 font-bold mt-1 truncate">
                                            Terbooking {{ \Carbon\Carbon::parse($scheduledBooking->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($scheduledBooking->end_time)->format('H:i') }}
                                        </p>
                                    @endforeach
                                @elseif($isLocked)
                                    <p class="text-[10px] text-red-500 font-bold mt-1 uppercase">LOCKED</p>
                                @elseif($pc->status == 'available')
                                    <p class="text-[10px] text-green-600 font-bold mt-1">Available</p>
                                @else
                                    <p class="text-[10px] text-slate-400 font-bold mt-1 uppercase">{{ $pc->status }}</p>
                                @endif
                            </div>
                            
                            <div class="shrink-0">
                                @if($isMine && !$isExpired)
                                    @if($isCheckedIn)
                                        <button wire:click="clickSeat({{ $pc->id }})" class="bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold px-5 py-2.5 rounded-md shadow-sm">
                                            Selesai
                                        </button>
                                    @elseif($isAwaitingCheckIn)
                                        <div class="flex flex-col items-end gap-1">
                                            <span class="text-[10px] font-bold text-amber-700">Scan QR</span>
                                            <button wire:click="clickSeat({{ $pc->id }})" class="bg-red-500 hover:bg-red-600 text-white text-[10px] font-bold px-3 py-1.5 rounded-md shadow-sm">
                                                Batal
                                            </button>
                                        </div>
                                    @else
                                        <button wire:click="clickSeat({{ $pc->id }})" class="bg-red-500 hover:bg-red-600 text-white text-xs font-bold px-5 py-2.5 rounded-md shadow-sm">
                                            Batal
                                        </button>
                                    @endif
                                @elseif($isLocked)
                                    <button disabled class="bg-slate-100 text-slate-400 border border-slate-200 text-[10px] font-bold px-3 py-2 rounded-md cursor-not-allowed">
                                        Event
                                    </button>
                                @elseif($pc->status == 'available')
                                    <button wire:click="clickSeat({{ $pc->id }})" class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold px-5 py-2.5 rounded-md shadow-sm">
                                        Booking
                                    </button>
                                @else
                                    <button disabled class="text-slate-300 font-bold text-xs cursor-not-allowed px-2">
                                        Off
                                    </button>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                <div class="pt-2"></div>
            </div>
        </div>
    @endif
    
    <script>
    window.addEventListener('debug-log', event => {
        console.table(event.detail);
    });
    </script>
</div>