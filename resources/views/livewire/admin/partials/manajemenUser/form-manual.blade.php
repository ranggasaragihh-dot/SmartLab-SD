<form wire:submit.prevent="{{ $isEditMode ? 'update' : 'store' }}">
    
    {{-- Grid System --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        
        {{-- KOLOM KIRI --}}
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Nama Lengkap</label>
                <input type="text" wire:model="name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Email</label>
                <input type="email" wire:model="email" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                @error('email') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">
                    Password @if($isEditMode) <span class="text-gray-400 font-normal text-xs">(Opsional)</span> @endif
                </label>
                <input type="password" wire:model="password" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                @error('password') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
        </div>

        {{-- KOLOM KANAN --}}
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Nomor Induk</label>
                <input type="text" wire:model="nomor_induk" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                @error('nomor_induk') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700">Status Tugas Akhir</label>
                <select wire:model="ta" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    <option value="0">Tidak Mengambil</option>
                    <option value="1">Sedang Mengambil (Ya)</option>
                </select>
            </div>

            {{-- Roles Checkbox --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Roles</label>
                <div class="bg-gray-50 p-3 rounded-md border border-gray-200 h-32 overflow-y-auto">
                    @foreach($allRoles as $role)
                        <div class="flex items-center mb-2">
                            <input type="checkbox" value="{{ $role->id }}" wire:model="selectedRoles" class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                            <label class="ml-2 text-sm text-gray-900">{{ ucfirst($role->role) }}</label>
                        </div>
                    @endforeach
                </div>
                @error('selectedRoles') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
        </div>
    </div>

    {{-- FOOTER BUTTONS --}}
    <div class="mt-6 flex flex-row-reverse gap-2">
        <button type="submit" wire:loading.attr="disabled" class="inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none sm:text-sm disabled:opacity-50">
            <span wire:loading.remove wire:target="store, update">{{ $isEditMode ? 'Simpan Perubahan' : 'Buat User' }}</span>
            <span wire:loading wire:target="store, update">Menyimpan...</span>
        </button>
        <button type="button" wire:click="closeModal" class="inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:text-sm">
            Batal
        </button>
    </div>
</form>