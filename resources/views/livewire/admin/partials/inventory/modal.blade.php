<div>
    {{-- 1. MODAL FORM (INPUT / IMPORT) --}}
    @if($isModalOpen)
        @teleport('body')
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            
            <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                
                {{-- BACKDROP --}}
                <div wire:click="closeModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-[2px] transition-opacity" aria-hidden="true"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                {{-- MODAL CONTAINER --}}
                <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl w-full border border-slate-100 relative">
                    
                    {{-- HEADER --}}
                    <div class="px-6 pt-6 pb-4 flex justify-between items-start bg-white">
                        <div>
                            <h3 class="text-xl font-bold text-slate-900 tracking-tight">
                                {{ $itemId ? 'Edit Data Inventaris' : 'Tambah Inventaris Baru' }}
                            </h3>
                            <p class="text-xs text-slate-500 mt-1">
                                {{ $itemId ? 'Perbarui detail aset fisik.' : 'Lengkapi informasi aset di bawah ini.' }}
                            </p>
                        </div>
                        <button wire:click="closeModal" class="text-slate-400 hover:text-slate-600 hover:bg-slate-100 p-2 rounded-full transition-all">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>

                    {{-- TABS (Only on Create) --}}
                    @if(!$itemId)
                        <div class="px-6 pb-2">
                            <div class="flex p-1 space-x-1 bg-slate-100/80 rounded-xl border border-slate-200">
                                <button wire:click="$set('activeTab', 'manual')" 
                                    class="w-full py-2.5 text-sm font-bold rounded-lg transition-all duration-200 flex items-center justify-center gap-2 {{ $activeTab === 'manual' ? 'bg-white text-slate-900 shadow-sm ring-1 ring-black/5' : 'text-slate-500 hover:text-slate-700' }}">
                                    Input Manual
                                </button>
                                <button wire:click="$set('activeTab', 'import')" 
                                    class="w-full py-2.5 text-sm font-bold rounded-lg transition-all duration-200 flex items-center justify-center gap-2 {{ $activeTab === 'import' ? 'bg-white text-slate-900 shadow-sm ring-1 ring-black/5' : 'text-slate-500 hover:text-slate-700' }}">
                                    Import Excel
                                </button>
                            </div>
                        </div>
                    @endif

                    {{-- CONTENT AREA --}}
                    <div class="bg-white min-h-[300px] relative flex flex-col max-h-[75vh]">
                        
                        {{-- Loading Overlay --}}
                        <div wire:loading wire:target="activeTab, store, update, importExcel" class="absolute inset-0 z-50 flex flex-col items-center justify-center bg-white/80 backdrop-blur-[2px]">
                            <div class="relative flex items-center justify-center">
                                <div class="w-10 h-10 border-4 border-slate-200 rounded-full"></div>
                                <div class="absolute w-10 h-10 border-4 border-blue-600 rounded-full border-t-transparent animate-spin"></div>
                            </div>
                            <span class="mt-2 text-xs font-bold text-slate-500">Memproses...</span>
                        </div>

                        {{-- SCROLLABLE BODY --}}
                        <div class="flex-1 overflow-y-auto px-6 pb-6 pt-4 scrollbar-thin">
                            @if($itemId || $activeTab === 'manual')
                                @include('livewire.admin.partials.inventory.form-manual')
                            @elseif($activeTab === 'import')
                                @include('livewire.admin.partials.inventory.form-import')
                            @endif
                        </div>

                        {{-- FOOTER (Tombol Aksi) --}}
                        <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex justify-end gap-3 shrink-0">
                            <button type="button" wire:click="closeModal" class="px-4 py-2 bg-white border border-slate-300 rounded-lg text-slate-700 hover:bg-slate-50 text-sm font-bold shadow-sm transition-all">
                                Batal
                            </button>

                            @if($activeTab === 'manual' || $itemId)
                                <button type="submit" form="manualForm" class="px-5 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 shadow-md shadow-blue-200 text-sm font-bold flex items-center gap-2 transition-all">
                                    Simpan Data
                                </button>
                            @else
                                <button type="submit" form="importForm" 
                                        class="px-5 py-2 bg-slate-900 text-white rounded-lg hover:bg-slate-800 shadow-md shadow-slate-200 text-sm font-bold flex items-center gap-2 transition-all disabled:opacity-50 disabled:cursor-not-allowed"
                                        @if(!$excelFile) disabled @endif>
                                    Mulai Import
                                </button>
                            @endif
                        </div>

                    </div>
                </div>
            </div>
        </div>
        @endteleport
    @endif
</div>