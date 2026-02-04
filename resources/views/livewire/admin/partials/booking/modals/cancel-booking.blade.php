@if($showCancelModal)
<div class="fixed inset-0 z-[60] flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4 animate-in fade-in zoom-in duration-200">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-sm overflow-hidden border-t-4 border-red-500">
        
        <div class="p-6 text-center">
            {{-- Icon Warning --}}
            <div class="w-12 h-12 rounded-full bg-red-100 text-red-500 flex items-center justify-center mx-auto mb-4">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            </div>

            <h3 class="text-lg font-bold text-slate-800 mb-2">Batalkan Booking?</h3>
            
            @if($selectedBookingData)
                <div class="bg-slate-50 p-3 rounded text-left text-xs text-slate-600 mb-4 border border-slate-200">
                    <p><strong>User:</strong> {{ $selectedBookingData->user->name ?? 'Unknown' }}</p>
                    <p><strong>Waktu:</strong> {{ \Carbon\Carbon::parse($selectedBookingData->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($selectedBookingData->end_time)->format('H:i') }}</p>
                </div>
            @endif

            <p class="text-sm text-slate-500">
                Tindakan ini akan menghapus sesi pengguna secara paksa. Status akan berubah menjadi <strong>Cancelled</strong>.
            </p>
        </div>

        <div class="bg-slate-50 px-5 py-3 flex justify-end gap-2 border-t border-slate-100">
            <button wire:click="$set('showCancelModal', false)" class="px-4 py-2 text-sm font-bold text-slate-600 hover:bg-slate-200 rounded transition-colors">Batal</button>
            <button wire:click="confirmCancel" class="px-4 py-2 text-sm font-bold text-white bg-red-600 hover:bg-red-700 rounded shadow-md transition-colors">Ya, Batalkan</button>
        </div>
    </div>
</div>
@endif