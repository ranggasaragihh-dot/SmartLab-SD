<div>
    @if($isModalOpen)
        @teleport('body')
        {{-- WRAPPER UTAMA: Fixed Full Screen --}}
        <div class="relative z-[9999]" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            
            {{-- 1. BACKDROP (Latar Buram) --}}
            {{-- Fixed inset-0 agar memenuhi layar --}}
            <div wire:click="closeModal" 
                 class="fixed inset-0 bg-slate-900/60 backdrop-blur-[2px] transition-opacity" 
                 aria-hidden="true"></div>

            {{-- 2. POSISITING CONTAINER (Flexbox Centering) --}}
            {{-- pointer-events-none agar klik tembus ke backdrop jika klik di luar modal --}}
            <div class="fixed inset-0 z-10 w-screen overflow-y-auto pointer-events-none">
                <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                    
                    {{-- 3. MODAL CARD (Konten Asli) --}}
                    {{-- pointer-events-auto agar modal bisa diklik --}}
                    <div class="pointer-events-auto relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-4xl border border-slate-100 flex flex-col max-h-[90vh]">
                        
                        {{-- A. HEADER --}}
                        <div class="px-5 py-3 flex justify-between items-center bg-white border-b border-slate-100 shrink-0">
                            <div>
                                <h3 class="text-lg font-bold text-slate-900 tracking-tight">
                                    {{ $itemId ? 'Edit Data Inventaris' : 'Tambah Inventaris' }}
                                </h3>
                            </div>
                            <button wire:click="closeModal" class="text-slate-400 hover:text-slate-600 hover:bg-slate-100 p-1.5 rounded-full transition-all">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                            </button>
                        </div>

                        {{-- B. TABS --}}
                        @if(!$itemId)
                            <div class="px-5 py-2 bg-slate-50 border-b border-slate-100 shrink-0">
                                <div class="flex p-0.5 space-x-1 bg-slate-200/50 rounded-lg border border-slate-200">
                                    <button wire:click="$set('activeTab', 'manual')" 
                                        class="w-full py-1.5 text-xs font-bold rounded-md transition-all duration-200 flex items-center justify-center gap-2 {{ $activeTab === 'manual' ? 'bg-white text-slate-900 shadow-sm ring-1 ring-black/5' : 'text-slate-500 hover:text-slate-700' }}">
                                        Input Manual
                                    </button>
                                    <button wire:click="$set('activeTab', 'import')" 
                                        class="w-full py-1.5 text-xs font-bold rounded-md transition-all duration-200 flex items-center justify-center gap-2 {{ $activeTab === 'import' ? 'bg-white text-slate-900 shadow-sm ring-1 ring-black/5' : 'text-slate-500 hover:text-slate-700' }}">
                                        Import Excel
                                    </button>
                                </div>
                            </div>
                        @endif

                        {{-- C. CONTENT AREA --}}
                        <div class="bg-white relative flex-1 overflow-hidden flex flex-col min-h-[200px]">
                            
                            {{-- Loading Overlay --}}
                            <div wire:loading wire:target="activeTab, store, update, importExcel" class="absolute inset-0 z-50 flex flex-col items-center justify-center bg-white/80 backdrop-blur-[2px]">
                                <div class="relative flex items-center justify-center">
                                    <div class="w-8 h-8 border-4 border-slate-200 rounded-full"></div>
                                    <div class="absolute w-8 h-8 border-4 border-slate-900 rounded-full border-t-transparent animate-spin"></div>
                                </div>
                            </div>

                            {{-- SCROLLABLE BODY --}}
                            <div class="flex-1 overflow-y-auto px-5 pt-3 pb-5 scrollbar-thin">
                                @if($itemId || $activeTab === 'manual')
                                    @include('livewire.admin.partials.inventory.form-manual')
                                @elseif($activeTab === 'import')
                                    @include('livewire.admin.partials.inventory.form-import')
                                @endif
                            </div>
                        </div>

                        {{-- D. FOOTER --}}
                        <div class="px-5 py-3 bg-slate-50 border-t border-slate-100 flex justify-end gap-3 shrink-0">
                            <button type="button" wire:click="closeModal" class="px-4 py-2 bg-white border border-slate-300 rounded-lg text-slate-700 hover:bg-slate-50 hover:text-slate-900 text-xs font-bold transition-all shadow-sm active:scale-95">
                                Batal
                            </button>

                            @if($activeTab === 'manual' || $itemId)
                                <button type="submit" form="manualForm" class="px-5 py-2 bg-slate-900 text-white rounded-lg hover:bg-slate-800 hover:shadow-lg hover:shadow-slate-900/20 text-xs font-bold transition-all transform active:scale-95 flex items-center gap-2">
                                    Simpan Data
                                </button>
                            @else
                                <button type="submit" form="importForm" 
                                        class="px-5 py-2 bg-slate-900 text-white rounded-lg hover:bg-slate-800 hover:shadow-lg hover:shadow-slate-900/20 text-xs font-bold transition-all transform active:scale-95 flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
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