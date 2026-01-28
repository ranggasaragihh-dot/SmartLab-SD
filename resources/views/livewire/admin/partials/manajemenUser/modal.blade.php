@if($showModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            
            {{-- Backdrop Blur Effect --}}
            <div wire:click="closeModal" class="fixed inset-0 bg-gray-900 bg-opacity-60 transition-opacity backdrop-blur-sm" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl w-full border border-gray-100">
                
                {{-- HEADER --}}
                <div class="px-6 pt-6 pb-4 flex justify-between items-center bg-white">
                    <div>
                        <h3 class="text-xl font-bold text-gray-900 tracking-tight">
                            {{ $isEditMode ? 'Edit Data User' : 'Tambah User Baru' }}
                        </h3>
                        <p class="text-xs text-gray-400 mt-1">Lengkapi informasi di bawah ini.</p>
                    </div>
                    <button wire:click="closeModal" class="text-gray-400 hover:text-gray-600 hover:bg-gray-100 p-2 rounded-full transition-all">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                {{-- MODERN SEGMENTED TABS --}}
                @if(!$isEditMode)
                    <div class="px-6 pb-2">
                        <div class="flex p-1 space-x-1 bg-gray-100/80 rounded-xl">
                            <button 
                                wire:click="$set('activeTab', 'manual')"
                                class="w-full py-2.5 text-sm font-semibold rounded-lg shadow-sm transition-all duration-200 {{ $activeTab === 'manual' ? 'bg-white text-indigo-600 ring-1 ring-black/5' : 'text-gray-500 hover:text-gray-700' }}">
                                <i class="fas fa-keyboard mr-2 text-xs"></i> Input Manual
                            </button>
                            <button 
                                wire:click="$set('activeTab', 'import')"
                                class="w-full py-2.5 text-sm font-semibold rounded-lg shadow-sm transition-all duration-200 {{ $activeTab === 'import' ? 'bg-white text-green-600 ring-1 ring-black/5' : 'text-gray-500 hover:text-gray-700' }}">
                                <i class="fas fa-file-excel mr-2 text-xs"></i> Import Excel
                            </button>
                        </div>
                    </div>
                @endif

                {{-- CONTENT --}}
                <div class="px-6 py-6 bg-white min-h-[300px]">
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