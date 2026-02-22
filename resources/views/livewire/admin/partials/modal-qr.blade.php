@if($showQrModal)
<div class="fixed inset-0 z-[60] overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div wire:click="closeQrModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-[2px] transition-opacity" aria-hidden="true"></div>
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-sm w-full relative border border-slate-100">
            <div class="bg-white px-6 py-4 flex justify-between items-center border-b border-slate-50">
                <h3 class="text-lg font-bold text-slate-900 flex items-center gap-2 tracking-tight">
                    <div class="p-1.5 bg-amber-50 rounded text-amber-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                    </div>
                    QR Code Station
                </h3>
                <button wire:click="closeQrModal" class="text-slate-400 hover:text-slate-600 hover:bg-slate-100 p-2 rounded-full transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="p-8 flex flex-col items-center justify-center bg-slate-50/50">
                <div class="bg-white p-4 rounded-2xl shadow-[0_4px_20px_-4px_rgba(0,0,0,0.1)] border border-slate-200 mb-5 relative group">
                    <div class="absolute inset-0 bg-gradient-to-tr from-amber-100/50 to-transparent rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <img src="{{ $qrUrl }}" alt="QR Code {{ $qrName }}" class="w-48 h-48 object-contain relative z-10 mix-blend-multiply">
                </div>
                
                <h2 class="text-2xl font-bold text-slate-900">{{ $qrName }}</h2>
                <div class="inline-flex items-center gap-1.5 mt-2 px-3 py-1 rounded-full bg-slate-100 border border-slate-200">
                    <svg class="w-3 h-3 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span class="text-xs font-medium text-slate-600">Scan untuk Check-in</span>
                </div>
            </div>

            <div class="bg-white px-6 py-5 border-t border-slate-100 flex flex-col gap-3">
                <button 
                    x-data 
                    @click="
                        fetch('{{ $qrUrl }}')
                        .then(response => response.blob())
                        .then(blob => {
                            const url = window.URL.createObjectURL(blob);
                            const a = document.createElement('a');
                            a.style.display = 'none';
                            a.href = url;
                            a.download = 'QR-{{ $qrName }}.png';
                            document.body.appendChild(a);
                            a.click();
                            window.URL.revokeObjectURL(url);
                        })"
                    class="w-full bg-slate-900 hover:bg-slate-800 text-white font-bold py-3 px-4 rounded-xl shadow-lg shadow-slate-900/20 flex items-center justify-center gap-2 transition-all transform active:scale-[0.98]"
                >
                    <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    <span>Simpan Gambar QR</span>
                </button>

                <button wire:click="closeQrModal" class="w-full bg-white border border-slate-300 text-slate-600 font-bold py-3 px-4 rounded-xl hover:bg-slate-50 hover:text-slate-800 transition-colors focus:ring-2 focus:ring-slate-200">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>
@endif