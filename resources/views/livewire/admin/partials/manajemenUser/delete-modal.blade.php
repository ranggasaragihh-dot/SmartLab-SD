{{-- File: resources/views/livewire/admin/partials/manajemenUser/delete.blade.php --}}

<div class="fixed inset-0 z-[60] overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    
    {{-- Backdrop (Lebih Gelap untuk Danger) --}}
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div wire:click="closeDeleteModal" class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm transition-opacity" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        {{-- Modal Card (Delete) --}}
        <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md w-full relative">
            
            <div class="p-6 text-center">
                {{-- Icon Danger --}}
                <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-50 mb-6 relative">
                    <span class="absolute inline-flex h-full w-full rounded-full bg-red-200 opacity-75 animate-ping duration-1000"></span>
                    <div class="relative flex items-center justify-center h-16 w-16 rounded-full bg-red-100 text-red-600">
                        <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    </div>
                </div>

                {{-- Text Confirmation --}}
                <h3 class="text-xl font-bold text-slate-900 mb-2">Hapus User Ini?</h3>
                <p class="text-sm text-slate-500 px-4 mb-4">
                    Apakah Anda yakin ingin menghapus user <br>
                    <span class="font-bold text-slate-800 bg-slate-100 px-2 py-0.5 rounded border border-slate-200 break-words">
                        {{ $name ?? 'Pengguna Terpilih' }}
                    </span>?
                </p>
                <div class="inline-block px-3 py-1 rounded-full bg-red-50 border border-red-100 text-xs font-bold text-red-600">
                    ⚠️ Data yang dihapus tidak dapat dikembalikan.
                </div>

                {{-- Action Buttons --}}
                <div class="mt-8 flex gap-3 justify-center">
                    <button wire:click="closeDeleteModal" 
                            class="w-full px-5 py-2.5 rounded-xl text-sm font-bold text-slate-600 bg-white border border-slate-200 hover:bg-slate-50 transition-all">
                        Batal
                    </button>
                    <button wire:click="delete({{ $deleteId }})" 
                            class="w-full px-5 py-2.5 rounded-xl text-sm font-bold text-white bg-red-600 hover:bg-red-700 shadow-lg shadow-red-600/20 transition-all flex items-center justify-center gap-2">
                        <span wire:loading.remove wire:target="delete">Ya, Hapus</span>
                        <span wire:loading wire:target="delete" class="flex items-center gap-2">
                            <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            ...
                        </span>
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>