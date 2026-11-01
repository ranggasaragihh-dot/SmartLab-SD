@if($showCreateModal)
<div class="fixed inset-0 z-[60] flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4 animate-in fade-in zoom-in duration-200">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden border border-slate-100 flex flex-col max-h-[90vh]">
        <div class="bg-slate-900 px-5 py-4 flex justify-between items-center shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-white/10 flex items-center justify-center text-white">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                </div>
                <div>
                    <h3 class="text-white font-bold text-sm leading-none uppercase tracking-wider">Booking Manual</h3>
                    <p class="text-slate-400 text-[11px] mt-1.5 font-mono">Unit: {{ $selectedUnitName }}</p>
                </div>
            </div>
            <button wire:click="$set('showCreateModal', false)" class="text-slate-400 hover:text-white p-1.5 rounded-full hover:bg-white/10 transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        <div class="p-5 space-y-4 overflow-y-auto">
            <div class="flex items-center gap-2.5 text-[11px] text-slate-600 bg-slate-50 p-2.5 rounded-xl border border-slate-200">
                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                <span class="font-bold uppercase tracking-wide">Jadwal: {{ \Carbon\Carbon::parse($dateFilter)->isoFormat('dddd, D MMMM Y') }}</span>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1.5 block">Jam Mulai</label>
                    <input type="time" wire:model="inputStartTime" class="w-full border-slate-300 rounded-lg text-sm py-2 focus:ring-1 focus:ring-slate-900 focus:border-slate-900 font-bold text-slate-700 bg-slate-50">
                    @error('inputStartTime') <span class="text-red-500 text-[10px] mt-1 block font-bold">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1.5 block">Jam Selesai</label>
                    <input type="time" wire:model="inputEndTime" class="w-full border-slate-300 rounded-lg text-sm py-2 focus:ring-1 focus:ring-slate-900 focus:border-slate-900 font-bold text-slate-700 bg-slate-50">
                    @error('inputEndTime') <span class="text-red-500 text-[10px] mt-1 block font-bold">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="relative">
                <label class="text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1.5 block">Pilih Mahasiswa / User</label>
                
                @if($selectedTargetUserName)
                    <div class="flex items-center justify-between bg-slate-900 border border-slate-900 text-white px-3 py-2 rounded-xl shadow-lg animate-in slide-in-from-top-1">
                        <div class="flex items-center gap-2.5 overflow-hidden">
                            <div class="w-7 h-7 rounded-full bg-white/20 flex items-center justify-center text-xs font-black">
                                {{ substr($selectedTargetUserName, 0, 1) }}
                            </div>
                            <span class="text-xs font-bold truncate">{{ $selectedTargetUserName }}</span>
                        </div>
                        <button wire:click="resetSelectedUser" class="text-white/60 hover:text-white transition-all p-1">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>
                @else
                    <div class="relative group">
                        <input type="text" 
                            wire:model.live.debounce.300ms="userSearch" 
                            placeholder="Ketik Nama atau Email..." 
                            class="w-full pl-10 pr-4 py-2 border-slate-300 rounded-lg text-sm focus:ring-1 focus:ring-slate-900 focus:border-slate-900 placeholder-slate-400 font-medium bg-slate-50 group-hover:bg-white transition-all">
                        
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        </div>

                        <div wire:loading wire:target="userSearch" class="absolute inset-y-0 right-0 pr-3 flex items-center">
                            <svg class="animate-spin h-4 w-4 text-slate-900" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        </div>

                        @if(!empty($usersList) && strlen($userSearch) > 1)
                            <div class="absolute z-10 w-full mt-2 bg-white border border-slate-200 rounded-xl shadow-2xl max-h-48 overflow-y-auto">
                                <ul class="divide-y divide-slate-100">
                                    @foreach($usersList as $user)
                                    <li>
                                        <button wire:click="selectUser({{ $user->id }})"
                                                class="w-full text-left px-4 py-2.5 hover:bg-slate-50 transition-all flex flex-col group">
                                            <span class="text-xs font-bold text-slate-700 group-hover:text-slate-900">{{ $user->name }}</span>
                                            <span class="text-[10px] text-slate-400">{{ $user->email }}</span>
                                        </button>
                                    </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>
                @endif
                @error('selectedTargetUserId') <span class="text-red-500 text-[10px] block mt-1 font-bold">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="bg-slate-50 px-6 py-4 flex justify-end gap-3 border-t border-slate-100 shrink-0">
            <button wire:click="$set('showCreateModal', false)" class="px-5 py-2 text-xs font-bold text-slate-500 hover:text-slate-900 transition-all">Batal</button>
            <button wire:click="storeBooking" class="px-6 py-2 text-xs font-bold text-white bg-slate-900 hover:bg-slate-800 rounded-xl shadow-lg shadow-slate-200 transition-all transform active:scale-95">Simpan Booking</button>
        </div>
    </div>
</div>
@endif