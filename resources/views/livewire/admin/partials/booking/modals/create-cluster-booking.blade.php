@if($showClusterBookingModal)
<div class="fixed inset-0 z-[60] flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4 animate-in fade-in zoom-in duration-200">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden border border-slate-100">
        
        {{-- Header Modal: UBAH JADI SLATE-900 (KONSISTEN ADMIN) --}}
        <div class="bg-slate-900 px-6 py-4 flex justify-between items-center">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-white/10 flex items-center justify-center text-amber-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                </div>
                <div>
                    <h3 class="text-white font-bold text-lg leading-none">Booking Ruangan: {{ $cluster->name }}</h3>
                    <p class="text-slate-400 text-xs mt-1.5 font-medium tracking-wide uppercase">Mode Master Key (Full Event)</p>
                </div>
            </div>
            <button wire:click="$set('showClusterBookingModal', false)" class="text-slate-400 hover:text-white p-1.5 rounded-full hover:bg-white/10 transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        <div class="px-6 py-2 bg-slate-50 border-b border-slate-100 flex items-center gap-2">
            <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            <span class="text-[11px] font-bold text-slate-600 uppercase">
                Booking untuk tanggal: {{ \Carbon\Carbon::parse($dateFilter)->isoFormat('D MMMM Y') }}
            </span>
        </div>

        {{-- Body Modal --}}
        <div class="p-6 space-y-5">
            {{-- Info Box: UBAH JADI AMBER (WARNA LOGO ATMA-LAB) --}}
            <div class="text-[11px] bg-amber-50 text-amber-800 p-4 rounded-xl border border-amber-100 leading-relaxed flex items-start gap-3 shadow-sm">
                <svg class="w-5 h-5 text-amber-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                <span>
                    <strong class="uppercase font-black block mb-0.5">Peringatan Mode Event:</strong>
                    Aksi ini akan mem-booking <strong>Satu Ruangan Penuh</strong> secara kolektif. 
                    Seluruh unit komputer akan otomatis <strong>UNLOCK</strong> saat QR Check-in dipindai.
                </span>
            </div>

            {{-- Input Jam: UBAH FOCUS RING JADI SLATE --}}
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1.5 block">Jam Mulai</label>
                    <input type="time" wire:model="inputStartTime" class="w-full border-slate-200 rounded-lg text-sm py-2.5 focus:ring-1 focus:ring-slate-900 focus:border-slate-900 font-bold text-slate-700 bg-slate-50">
                    @error('inputStartTime') <span class="text-red-500 text-[10px] mt-1 block font-bold italic">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1.5 block">Jam Selesai</label>
                    <input type="time" wire:model="inputEndTime" class="w-full border-slate-300 rounded-lg text-sm py-2.5 focus:ring-1 focus:ring-slate-900 focus:border-slate-900 font-bold text-slate-700 bg-slate-50">
                    @error('inputEndTime') <span class="text-red-500 text-[10px] mt-1 block font-bold italic">{{ $message }}</span> @enderror
                </div>
            </div>

            {{-- Input User (Penanggung Jawab) --}}
            <div class="relative">
                <label class="text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1.5 block">Penanggung Jawab (Dosen/Ketua)</label>
                
                @if($selectedTargetUserName)
                    <div class="flex items-center justify-between bg-slate-900 border border-slate-900 text-white px-4 py-2.5 rounded-xl shadow-lg animate-in slide-in-from-top-1">
                        <div class="flex items-center gap-3 overflow-hidden">
                            <div class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center text-sm font-black">
                                {{ substr($selectedTargetUserName, 0, 1) }}
                            </div>
                            <span class="text-sm font-bold truncate">{{ $selectedTargetUserName }}</span>
                        </div>
                        <button wire:click="resetSelectedUser" class="text-white/60 hover:text-white transition-all p-1">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>
                @else
                    <div class="relative group">
                        <input type="text" 
                            wire:model.live.debounce.300ms="userSearch" 
                            placeholder="Cari Nama Penanggung Jawab..." 
                            class="w-full pl-10 pr-4 py-2.5 border-slate-300 rounded-lg text-sm focus:ring-1 focus:ring-slate-900 focus:border-slate-900 placeholder-slate-400 font-medium bg-slate-50 group-hover:bg-white transition-all">
                        
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        </div>

                        {{-- Dropdown Search Results --}}
                        @if(!empty($usersList) && strlen($userSearch) > 1)
                            <div class="absolute z-10 w-full mt-2 bg-white border border-slate-200 rounded-xl shadow-2xl max-h-56 overflow-y-auto overflow-x-hidden">
                                <ul class="divide-y divide-slate-100">
                                    @foreach($usersList as $user)
                                    <li>
                                        <button wire:click="selectUser({{ $user->id }}, '{{ $user->name }}')" 
                                                class="w-full text-left px-4 py-3 hover:bg-slate-50 transition-all flex items-center gap-3 group">
                                            <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-xs font-bold text-slate-500 group-hover:bg-slate-900 group-hover:text-white">
                                                {{ substr($user->name, 0, 1) }}
                                            </div>
                                            <div class="flex flex-col">
                                                <span class="text-sm font-bold text-slate-700 group-hover:text-slate-900">{{ $user->name }}</span>
                                                <span class="text-[10px] text-slate-400">{{ $user->email }}</span>
                                            </div>
                                        </button>
                                    </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>
                @endif
                @error('selectedTargetUserId') <span class="text-red-500 text-[10px] block mt-1 font-bold italic">{{ $message }}</span> @enderror
            </div>

            {{-- Tujuan --}}
            <div>
                <label class="text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1.5 block">Tujuan / Agenda Acara</label>
                <input type="text" wire:model="purpose" placeholder="Contoh: Kuliah Pengganti, Sertifikasi BNSP, dsb." class="w-full border-slate-300 rounded-lg text-sm py-2.5 focus:ring-1 focus:ring-slate-900 focus:border-slate-900 font-medium bg-slate-50 placeholder-slate-300">
            </div>
        </div>

        {{-- Footer Modal --}}
        <div class="bg-slate-50 px-6 py-4 flex justify-end gap-3 border-t border-slate-200">
            <button wire:click="$set('showClusterBookingModal', false)" class="px-5 py-2.5 text-sm font-bold text-slate-500 hover:text-slate-900 hover:bg-white border border-transparent hover:border-slate-200 rounded-xl transition-all active:scale-95">Batal</button>
            <button wire:click="storeBooking" 
                class="px-6 py-2.5 text-sm font-bold text-white rounded-xl shadow-lg shadow-slate-200 transition-all bg-slate-900 hover:bg-slate-800 transform active:scale-95 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Simpan Reservasi Event
            </button>
        </div>
    </div>
</div>
@endif