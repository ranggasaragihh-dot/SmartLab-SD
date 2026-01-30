{{-- File: resources/views/livewire/admin/partials/manajemenUser/modal.blade.php --}}

<div>
    {{-- 1. MODAL FORM (INPUT / IMPORT) --}}
    @if($showModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            
            <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div wire:click="closeModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-[2px] transition-opacity" aria-hidden="true"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl w-full border border-slate-100 relative">
                    
                    {{-- HEADER --}}
                    <div class="px-6 pt-6 pb-4 flex justify-between items-start bg-white">
                        <div>
                            <h3 class="text-xl font-bold text-slate-900 tracking-tight">
                                {{ $isEditMode ? 'Edit Data User' : 'Tambah User Baru' }}
                            </h3>
                            <p class="text-xs text-slate-500 mt-1">
                                {{ $isEditMode ? 'Perbarui informasi pengguna.' : 'Lengkapi informasi di bawah ini.' }}
                            </p>
                        </div>
                        <button wire:click="closeModal" class="text-slate-400 hover:text-slate-600 hover:bg-slate-100 p-2 rounded-full transition-all">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>

                    {{-- TABS (Only on Create) --}}
                    @if(!$isEditMode)
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
                    <div class="px-6 pb-6 pt-4 bg-white min-h-[300px] relative">
                        <div wire:loading wire:target="activeTab, store, update, import" class="absolute inset-0 z-50 flex flex-col items-center justify-center bg-white/80 backdrop-blur-[2px] rounded-b-2xl">
                            <div class="relative flex items-center justify-center">
                                <div class="w-10 h-10 border-4 border-slate-200 rounded-full"></div>
                                <div class="absolute w-10 h-10 border-4 border-amber-500 rounded-full border-t-transparent animate-spin"></div>
                            </div>
                        </div>

                        {{-- INCLUDE FILES --}}
                        @if($isEditMode || $activeTab === 'manual')
                            @include('livewire.admin.partials.manajemenUser.form-manual')
                        @elseif($activeTab === 'import')
                            @include('livewire.admin.partials.manajemenUser.form-import')
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- 2. MODAL DELETE --}}
    @if($showDeleteModal)
        {{-- SEKARANG KITA PANGGIL FILE DELETE DISINI --}}
        @include('livewire.admin.partials.manajemenUser.delete-modal')
    @endif
</div>