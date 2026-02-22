<div>
    @if($showModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div wire:click="closeModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-[2px] transition-opacity" aria-hidden="true"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl w-full border border-slate-100 relative">
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

                    <div class="px-6 pb-6 pt-4 bg-white min-h-[300px] relative">
                        <div wire:loading wire:target="activeTab, store, update, import" 
                            class="absolute inset-0 z-50 bg-white/40 backdrop-blur-sm transition-all duration-200 rounded-b-2xl">
                        </div>

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

    @if($showDeleteModal)
        @include('livewire.admin.partials.manajemenUser.delete-modal')
    @endif
</div>