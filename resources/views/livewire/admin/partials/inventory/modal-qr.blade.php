@if($showQrModal)
    <div class="fixed inset-0 z-[60] overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        
        {{-- 1. Backdrop Blur --}}
        <div wire:click="closeQrModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-[2px] transition-opacity" aria-hidden="true"></div>

        {{-- 2. Modal Wrapper --}}
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            {{-- 3. Modal Card --}}
            <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-sm w-full relative border border-slate-100">
                
                {{-- Header (Clean Style) --}}
                <div class="bg-white px-6 py-4 flex justify-between items-center border-b border-slate-50">
                    <h3 class="text-lg font-bold text-slate-900 flex items-center gap-2 tracking-tight">
                        {{-- Ikon QR Kecil --}}
                        <div class="p-1.5 bg-blue-50 rounded text-blue-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                        </div>
                        Label Aset Fisik
                    </h3>
                    <button wire:click="closeQrModal" class="text-slate-400 hover:text-slate-600 hover:bg-slate-100 p-2 rounded-full transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                {{-- Body: QR Image Display --}}
                <div class="p-8 flex flex-col items-center justify-center bg-slate-50/50">
                    {{-- Frame QR --}}
                    <div class="bg-white p-4 rounded-2xl shadow-[0_4px_20px_-4px_rgba(0,0,0,0.1)] border border-slate-200 mb-5 relative group">
                        <div class="absolute inset-0 bg-gradient-to-tr from-blue-100/50 to-transparent rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity"></div>
                        
                        {{-- GAMBAR QR DARI API --}}
                        <img src="{{ $qrCodeUrl }}" alt="QR Code {{ $qrCodeTarget }}" class="w-48 h-48 object-contain relative z-10 mix-blend-multiply">
                    </div>
                    
                    {{-- Info Text (Nama Barang) --}}
                    <h2 class="text-xl font-bold text-slate-900 text-center leading-tight mb-1">
                        {{ $qrItemName }}
                    </h2>
                    
                    {{-- Kode Aset (Highlight) --}}
                    <div class="inline-flex items-center gap-1.5 mt-2 px-3 py-1 rounded-full bg-blue-100 border border-blue-200">
                        <svg class="w-3 h-3 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
                        <span class="text-xs font-bold text-blue-800 tracking-wide font-mono">
                            {{ $qrCodeTarget }}
                        </span>
                    </div>

                    <p class="text-xs text-slate-400 mt-4 italic text-center max-w-[200px]">
                        Tempelkan stiker ini pada fisik barang untuk keperluan audit.
                    </p>
                </div>

                {{-- Footer: Actions --}}
                <div class="bg-white px-6 py-5 border-t border-slate-100 flex flex-col gap-3">
                    
                    {{-- TOMBOL DOWNLOAD (Satu-satunya Aksi Utama) --}}
                    <button 
                        wire:click="downloadQrImage"
                        wire:loading.attr="disabled"
                        class="relative w-full bg-slate-900 hover:bg-slate-800 text-white font-bold py-3.5 px-4 rounded-xl shadow-lg shadow-slate-900/20 flex items-center justify-center gap-3 transition-all transform active:scale-[0.98] disabled:opacity-70 disabled:cursor-not-allowed group"
                    >
                        {{-- Icon Download (Default) --}}
                        <svg wire:loading.remove target="downloadQrImage" class="w-5 h-5 text-blue-400 group-hover:text-blue-300 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        
                        {{-- Icon Loading (Muncul saat proses download) --}}
                        <svg wire:loading target="downloadQrImage" class="animate-spin h-5 w-5 text-blue-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        
                        <span wire:loading.remove target="downloadQrImage">Simpan Gambar QR (.PNG)</span>
                        <span wire:loading target="downloadQrImage">Sedang Mengunduh...</span>
                    </button>

                    {{-- Tombol Tutup --}}
                    <button wire:click="closeQrModal" class="w-full bg-white border border-slate-200 text-slate-500 font-medium py-3 px-4 rounded-xl hover:bg-slate-50 hover:text-slate-700 transition-colors focus:ring-2 focus:ring-slate-100">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif