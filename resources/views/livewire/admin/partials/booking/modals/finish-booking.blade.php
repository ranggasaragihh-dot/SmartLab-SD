@if($showFinishModal)
<div class="fixed inset-0 z-[60] flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4 animate-in fade-in zoom-in duration-200">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-sm overflow-hidden border-t-4 border-emerald-500">
        
        <div class="p-6 text-center">
            {{-- Icon Success Check --}}
            <div class="w-12 h-12 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center mx-auto mb-4">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            </div>

            <h3 class="text-lg font-bold text-slate-800 mb-2">Selesaikan Sesi?</h3>
            
            @if($selectedBookingData)
                <div class="bg-slate-50 p-3 rounded text-left text-xs text-slate-600 mb-4 border border-slate-200">
                    <p><strong>User:</strong> {{ $selectedBookingData->user->name ?? 'Unknown' }}</p>
                    <p><strong>Info:</strong> Sesi akan ditandai selesai sekarang.</p>
                </div>
            @endif

            <p class="text-sm text-slate-500">
                Unit akan langsung menjadi <strong>Available</strong> untuk pengguna lain setelah Anda menekan tombol ini.
            </p>
        </div>

        <div class="bg-slate-50 px-5 py-3 flex justify-end gap-2 border-t border-slate-100">
            <button wire:click="$set('showFinishModal', false)" class="px-4 py-2 text-sm font-bold text-slate-600 hover:bg-slate-200 rounded transition-colors">Batal</button>
            <button wire:click="confirmFinish" class="px-4 py-2 text-sm font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded shadow-md transition-colors">Ya, Selesaikan</button>
        </div>
    </div>
</div>
@endif