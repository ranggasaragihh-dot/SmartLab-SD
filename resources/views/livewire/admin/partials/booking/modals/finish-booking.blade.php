@if($showFinishModal)
<div class="fixed inset-0 z-[60] flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4 animate-in fade-in zoom-in duration-200">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm overflow-hidden border border-slate-100 flex flex-col">
        <div class="bg-slate-900 px-5 py-4 flex justify-between items-center shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-emerald-500 flex items-center justify-center text-white shrink-0 shadow-lg shadow-emerald-900/20">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                </div>
                <h3 class="text-white font-bold text-sm uppercase tracking-wider">Selesaikan Sesi</h3>
            </div>
            <button wire:click="$set('showFinishModal', false)" class="text-slate-400 hover:text-white p-1 rounded-full hover:bg-white/10 transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        <div class="p-6">
            @if($selectedBookingData)
                <div class="bg-slate-50 border border-slate-200 p-4 rounded-xl space-y-2 mb-4 shadow-sm">
                    <div class="flex justify-between items-center text-[11px]">
                        <span class="text-slate-400 font-bold uppercase tracking-tight">Pengguna</span>
                        <span class="text-slate-900 font-bold">{{ $selectedBookingData->user->name ?? 'Unknown' }}</span>
                    </div>
                    <div class="flex justify-between items-center text-[11px]">
                        <span class="text-slate-400 font-bold uppercase tracking-tight">Status Akhir</span>
                        <span class="text-emerald-600 font-bold uppercase">Selesai Sekarang</span>
                    </div>
                </div>
            @endif

            <p class="text-xs text-slate-500 leading-relaxed text-center px-2">
                Unit akan langsung kembali menjadi <span class="text-slate-900 font-bold italic">Available</span> untuk pengguna lain setelah Anda mengonfirmasi penyelesaian ini.
            </p>
        </div>

        <div class="bg-slate-50 px-6 py-4 flex justify-end gap-3 border-t border-slate-100 shrink-0">
            <button wire:click="$set('showFinishModal', false)" 
                    class="px-5 py-2 text-xs font-bold text-slate-500 hover:text-slate-900 transition-all">
                Batal
            </button>
            <button wire:click="confirmFinish" 
                    class="px-6 py-2 text-xs font-bold text-white bg-emerald-600 rounded-xl hover:bg-emerald-700 shadow-lg shadow-emerald-200 transition-all transform active:scale-95 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                Ya, Selesaikan
            </button>
        </div>
    </div>
</div>
@endif