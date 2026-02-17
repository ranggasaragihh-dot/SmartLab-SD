@if($showCancelModal)
<div class="fixed inset-0 z-[60] flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4 animate-in fade-in zoom-in duration-200">
    {{-- MODAL CONTAINER: Gunakan rounded-2xl dan border slate halus --}}
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm overflow-hidden border border-slate-100 flex flex-col">
        
        {{-- HEADER: Slate-900 dengan aksen ikon merah --}}
        <div class="bg-slate-900 px-5 py-4 flex justify-between items-center shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-red-500 flex items-center justify-center text-white shrink-0 shadow-lg shadow-red-900/20">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                </div>
                <h3 class="text-white font-bold text-sm uppercase tracking-wider">Batalkan Booking</h3>
            </div>
            <button wire:click="$set('showCancelModal', false)" class="text-slate-400 hover:text-white p-1 rounded-full hover:bg-white/10 transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        <div class="p-6">
            {{-- INFO BOX: Padat dan informatif --}}
            @if($selectedBookingData)
                <div class="bg-slate-50 border border-slate-200 p-4 rounded-xl space-y-2 mb-4 shadow-sm text-[11px]">
                    <div class="flex justify-between items-center">
                        <span class="text-slate-400 font-bold uppercase tracking-tight">Pengguna</span>
                        <span class="text-slate-900 font-bold">{{ $selectedBookingData->user->name ?? 'Unknown' }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-slate-400 font-bold uppercase tracking-tight">Waktu Sesi</span>
                        <span class="text-slate-900 font-mono font-bold">
                            {{ \Carbon\Carbon::parse($selectedBookingData->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($selectedBookingData->end_time)->format('H:i') }}
                        </span>
                    </div>
                </div>
            @endif

            <div>
                    <label for="cancelReason" class="block text-sm font-medium text-gray-700">
                        Alasan Pembatalan <span class="text-red-500">*</span>
                    </label>
                    <textarea 
                        wire:model="cancelReason" 
                        id="cancelReason" 
                        rows="3" 
                        class="shadow-sm focus:ring-red-500 focus:border-red-500 mt-1 block w-full sm:text-sm border border-gray-300 rounded-md p-2"
                        placeholder="Contoh: Mahasiswa tidak hadir / Lab sedang maintenance..."
                    ></textarea>
                    
                    @error('cancelReason') 
                        <span class="text-red-600 text-xs mt-1 block">{{ $message }}</span> 
                    @enderror
                </div>

            <p class="text-xs text-slate-500 leading-relaxed text-center px-2">
                Tindakan ini akan menghapus sesi pengguna secara paksa. Status unit akan segera berubah menjadi <span class="text-red-600 font-bold italic">Cancelled</span>.
            </p>
        </div>

        {{-- FOOTER: Tombol aksi dengan gaya Slate & Red --}}
        <div class="bg-slate-50 px-6 py-4 flex justify-end gap-3 border-t border-slate-100 shrink-0">
            <button wire:click="$set('showCancelModal', false)" 
                    class="px-5 py-2 text-xs font-bold text-slate-500 hover:text-slate-900 transition-all">
                Batal
            </button>
            <button wire:click="confirmCancel" 
                    class="px-6 py-2 text-xs font-bold text-white bg-red-600 rounded-xl hover:bg-red-700 shadow-lg shadow-red-200 transition-all transform active:scale-95 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                Ya, Batalkan
            </button>
        </div>
    </div>
</div>
@endif