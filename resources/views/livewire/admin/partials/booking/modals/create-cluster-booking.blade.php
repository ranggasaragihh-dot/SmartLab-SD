@if($showClusterBookingModal)
<div class="fixed inset-0 z-[60] flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4 animate-in fade-in zoom-in duration-200">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg overflow-hidden">
        
        {{-- Header Modal --}}
        <div class="bg-purple-600 px-5 py-3 flex justify-between items-center">
            <div>
                <h3 class="text-white font-bold text-lg">Booking Event: {{ $cluster->name }}</h3>
                <p class="text-purple-200 text-xs mt-0.5">Booking Full Satu Ruangan</p>
            </div>
            <button wire:click="$set('showClusterBookingModal', false)" class="text-purple-200 hover:text-white bg-purple-500/30 p-1 rounded-md transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        {{-- Body Modal --}}
        <div class="p-6 space-y-5">
            {{-- Info Box --}}
            <div class="text-xs bg-purple-50 text-purple-800 p-3 rounded-lg border border-purple-100 leading-relaxed flex items-start gap-2">
                <svg class="w-4 h-4 text-purple-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span>
                    <strong>Mode Master Key:</strong> Aksi ini akan membooking <u>SATU RUANGAN PENUH</u>. 
                    Seluruh PC di ruangan ini akan otomatis <strong>UNLOCK</strong> saat Penanggung Jawab melakukan Scan QR Check-in.
                </span>
            </div>

            {{-- Input Jam --}}
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="text-xs font-bold text-slate-600 mb-1 block">Jam Mulai</label>
                    {{-- PERBAIKAN: Gunakan inputStartTime agar sinkron dengan storeBooking --}}
                    <input type="time" wire:model="inputStartTime" class="w-full border-slate-300 rounded-lg text-sm focus:ring-purple-500 focus:border-purple-500">
                    @error('inputStartTime') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="text-xs font-bold text-slate-600 mb-1 block">Jam Selesai</label>
                    {{-- PERBAIKAN: Gunakan inputEndTime agar sinkron dengan storeBooking --}}
                    <input type="time" wire:model="inputEndTime" class="w-full border-slate-300 rounded-lg text-sm focus:ring-purple-500 focus:border-purple-500">
                    @error('inputEndTime') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>
            </div>

            {{-- Input User --}}
            <div class="relative">
                <label class="text-xs font-bold text-slate-600 mb-1 block">Pilih Penanggung Jawab (Dosen/Ketua)</label>
                
                @if($selectedTargetUserName)
                    {{-- TAMPILAN JIKA USER SUDAH DIPILIH --}}
                    <div class="flex items-center justify-between bg-blue-50 border border-blue-200 text-blue-700 px-3 py-2 rounded-lg">
                        <div class="flex items-center gap-2 overflow-hidden">
                            <div class="w-6 h-6 rounded-full bg-blue-200 flex items-center justify-center text-xs font-bold">
                                {{ substr($selectedTargetUserName, 0, 1) }}
                            </div>
                            <span class="text-sm font-bold truncate">{{ $selectedTargetUserName }}</span>
                        </div>
                        <button wire:click="resetSelectedUser" class="text-blue-400 hover:text-red-500 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>
                    <input type="hidden" wire:model="selectedTargetUserId">
                @else
                    {{-- TAMPILAN INPUT PENCARIAN --}}
                    <div class="relative">
                        <input type="text" 
                            wire:model.live.debounce.300ms="userSearch" 
                            placeholder="Ketik Nama Dosen..." 
                            class="w-full pl-9 pr-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-purple-500 focus:border-purple-500 placeholder-slate-400">
                        
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        </div>

                        <div wire:loading wire:target="userSearch" class="absolute inset-y-0 right-0 pr-3 flex items-center">
                            <svg class="animate-spin h-4 w-4 text-purple-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        </div>
                    </div>

                    {{-- DROPDOWN HASIL PENCARIAN --}}
                    @if(!empty($usersList) && strlen($userSearch) > 1)
                        <div class="absolute z-10 w-full mt-1 bg-white border border-slate-200 rounded-lg shadow-lg max-h-48 overflow-y-auto">
                            <ul class="divide-y divide-slate-100">
                                @foreach($usersList as $user)
                                <li>
                                    <button wire:click="selectUser({{ $user->id }}, '{{ $user->name }}')" 
                                            class="w-full text-left px-4 py-2 hover:bg-purple-50 transition-colors flex flex-col">
                                        <span class="text-sm font-bold text-slate-700">{{ $user->name }}</span>
                                        <span class="text-xs text-slate-500">{{ $user->email }}</span>
                                    </button>
                                </li>
                                @endforeach
                            </ul>
                        </div>
                    @elseif(strlen($userSearch) > 1 && empty($usersList))
                        <div class="absolute z-10 w-full mt-1 bg-white border border-slate-200 rounded-lg shadow-lg p-3 text-center text-xs text-slate-500">
                            User tidak ditemukan.
                        </div>
                    @endif
                @endif

                @error('selectedTargetUserId') <span class="text-red-500 text-xs block mt-1">{{ $message }}</span> @enderror
            </div>
            <div>
                <label class="text-xs font-bold text-slate-600 mb-1 block">Tujuan</label>
                <input type="text" wire:model="purpose" placeholder="Contoh: Izin Khusus, Skripsi, dll" class="w-full border-slate-300 rounded-lg text-sm focus:ring-blue-500">
            </div>
        </div>

        {{-- Footer Modal --}}
        <div class="bg-slate-50 px-6 py-4 flex justify-end gap-3 border-t border-slate-100">
            <button wire:click="openCreateRoomModal" class="px-4 py-2 text-sm font-bold text-slate-500 hover:text-slate-700 hover:bg-slate-200 rounded-lg transition-colors">Batal</button>
            <button wire:click="storeBooking" 
                class="px-4 py-2 text-xs font-bold text-white rounded shadow-sm transition-all bg-purple-600 hover:bg-purple-700">
                Simpan Event Ruangan
            </button>
        </div>
    </div>
</div>
@endif