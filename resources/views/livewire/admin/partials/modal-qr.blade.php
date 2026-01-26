@if($showQrModal)
<div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-60 backdrop-blur-sm p-4 transition-opacity">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm overflow-hidden transform transition-all scale-100">
        
        {{-- Header --}}
        <div class="bg-slate-900 px-6 py-4 flex justify-between items-center">
            <h3 class="text-lg font-bold text-white flex items-center gap-2">
                <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                QR Code Station
            </h3>
            <button wire:click="closeQrModal" class="text-gray-400 hover:text-white transition-colors">✕</button>
        </div>

        {{-- Body: QR Image --}}
        <div class="p-8 flex flex-col items-center justify-center bg-gray-50">
            <div class="bg-white p-3 rounded-xl shadow-md border border-gray-200 mb-4">
                <img src="{{ $qrUrl }}" alt="QR Code {{ $qrName }}" class="w-48 h-48 object-contain">
            </div>
            
            <h2 class="text-xl font-bold text-gray-800">{{ $qrName }}</h2>
            <p class="text-sm text-gray-500 text-center mt-1">Scan kode ini untuk melakukan Check-in</p>
        </div>

        {{-- Footer: Action Buttons --}}
        <div class="bg-white px-6 py-4 border-t border-gray-100 flex flex-col gap-2">
            
            {{-- Tombol Download (Menggunakan Magic JS Alpine untuk Download Gambar) --}}
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
                class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-lg shadow-lg shadow-blue-500/30 flex items-center justify-center gap-2 transition-transform active:scale-95"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Download QR Image
            </button>

            <button wire:click="closeQrModal" class="w-full bg-white border border-gray-300 text-gray-700 font-medium py-3 px-4 rounded-lg hover:bg-gray-50 transition-colors">
                Tutup
            </button>
        </div>
    </div>
</div>
@endif