@if($showDeleteModal)
@teleport('body')
<div class="fixed inset-0 z-[60] overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    
    {{-- 1. Backdrop (Gelap & Blur) --}}
    <div wire:click="closeDeleteModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-[2px] transition-opacity"></div>

    {{-- 2. Modal Wrapper --}}
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        {{-- 3. Modal Card --}}
        <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md w-full relative border border-slate-100">
            
            <div class="p-6 text-center">
                {{-- Ikon Tong Sampah (Animasi Pulse Merah) --}}
                <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-50 mb-6 relative group">
                    <span class="absolute inline-flex h-full w-full rounded-full bg-red-200 opacity-75 animate-ping duration-1000"></span>
                    <div class="relative flex items-center justify-center h-16 w-16 rounded-full bg-red-100 text-red-600">
                        <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    </div>
                </div>

                {{-- Judul & Konteks --}}
                <h3 class="text-xl font-bold text-slate-900 mb-2 tracking-tight">Hapus Cluster Lab?</h3>
                <p class="text-sm text-slate-500 px-4 mb-5 leading-relaxed">
                    Anda akan menghapus data laboratorium: <br>
                    <span class="font-bold text-slate-800 bg-slate-100 px-2 py-0.5 rounded border border-slate-200 break-words mt-1 inline-block">
                        {{ $deleteName ?? 'Cluster Terpilih' }}
                    </span>
                </p>

                {{-- Warning Box --}}
                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-red-50 border border-red-100 mb-6">
                    <svg class="w-3.5 h-3.5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    <span class="text-xs font-bold text-red-700">Semua data PC di dalamnya akan terhapus.</span>
                </div>

                {{-- Tombol Aksi --}}
                <div class="flex gap-3 justify-center mt-2">
                    {{-- Tombol Batal --}}
                    <button wire:click="closeDeleteModal" 
                            class="w-full px-5 py-2.5 rounded-xl text-sm font-bold text-slate-600 bg-white border border-slate-200 hover:bg-slate-50 transition-all focus:ring-2 focus:ring-slate-200">
                        Batal
                    </button>

                    {{-- Tombol Hapus --}}
                    <button wire:click="destroy" 
                            class="w-full px-5 py-2.5 rounded-xl text-sm font-bold text-white bg-red-600 hover:bg-red-700 shadow-lg shadow-red-600/20 transition-all flex items-center justify-center gap-2 transform active:scale-95">
                        
                        <span wire:loading.remove wire:target="destroy">Ya, Hapus</span>
                        
                        <span wire:loading wire:target="destroy" class="flex items-center gap-2">
                            <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            Menghapus...
                        </span>
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>
@endteleport
@endif