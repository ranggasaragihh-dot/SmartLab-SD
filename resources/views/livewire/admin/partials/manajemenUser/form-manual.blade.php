<form wire:submit.prevent="{{ $isEditMode ? 'update' : 'store' }}" class="space-y-8">
    <div>
        <h4 class="text-sm font-bold text-slate-900 mb-4 flex items-center gap-2">
            <span class="w-1 h-4 bg-amber-500 rounded-full"></span>
            Informasi Pribadi
        </h4>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-5">
            <div class="col-span-2 md:col-span-1">
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wide mb-1.5">Nama Lengkap</label>
                <input type="text" wire:model="name" 
                    class="block w-full rounded-lg shadow-sm sm:text-sm px-4 py-2.5 transition-all placeholder-slate-300
                    @error('name') border-red-300 focus:border-red-500 focus:ring-red-500 bg-red-50/30 text-red-900 @else border-slate-200 focus:border-amber-500 focus:ring-amber-500 @enderror"
                    placeholder="Contoh: Budi Santoso">
                @error('name') 
                    <p class="mt-1 text-xs text-red-600 font-medium flex items-center gap-1">
                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        {{ $message }}
                    </p> 
                @enderror
            </div>

            <div class="col-span-2 md:col-span-1">
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wide mb-1.5">Nomor Induk</label>
                <input type="text" wire:model="nomor_induk" 
                    class="block w-full rounded-lg shadow-sm sm:text-sm px-4 py-2.5 transition-all font-mono placeholder-slate-300
                    @error('nomor_induk') border-red-300 focus:border-red-500 focus:ring-red-500 bg-red-50/30 text-red-900 @else border-slate-200 focus:border-amber-500 focus:ring-amber-500 @enderror"
                    placeholder="21071xxxx">
                @error('nomor_induk') <p class="mt-1 text-xs text-red-600 font-medium">{{ $message }}</p> @enderror
            </div>
        </div>
    </div>

    <div>
        <h4 class="text-sm font-bold text-slate-900 mb-4 flex items-center gap-2">
            <span class="w-1 h-4 bg-amber-500 rounded-full"></span>
            Akun & Akses
        </h4>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-5">
            <div class="col-span-2">
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wide mb-1.5">Alamat Email</label>
                <input type="email" wire:model="email" 
                    class="block w-full rounded-lg shadow-sm sm:text-sm px-4 py-2.5 transition-all placeholder-slate-300
                    @error('email') border-red-300 focus:border-red-500 focus:ring-red-500 bg-red-50/30 text-red-900 @else border-slate-200 focus:border-amber-500 focus:ring-amber-500 @enderror"
                    placeholder="nama@student.uajy.ac.id">
                @error('email') <p class="mt-1 text-xs text-red-600 font-medium">{{ $message }}</p> @enderror
            </div>

            <div class="col-span-2 md:col-span-1" x-data="{ show: false }">
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wide mb-1.5">
                    Password @if($isEditMode) <span class="text-slate-400 font-normal lowercase ml-1">(kosongkan jika tetap)</span> @endif
                </label>
                <div class="relative">
                    <input :type="show ? 'text' : 'password'" wire:model="password" 
                        class="block w-full rounded-lg shadow-sm sm:text-sm pl-4 pr-10 py-2.5 transition-all placeholder-slate-300
                        @error('password') border-red-300 focus:border-red-500 focus:ring-red-500 bg-red-50/30 @else border-slate-200 focus:border-amber-500 focus:ring-amber-500 @enderror"
                        placeholder="••••••••">
                    
                    <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 flex items-center px-3 text-slate-400 hover:text-slate-600 focus:outline-none">
                        <svg x-show="!show" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                        <svg x-show="show" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="display: none;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a10.05 10.05 0 011.563-3.026m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.542 7a10.057 10.057 0 01-1.563 3.026m-5.858-.908a3 3 0 11-4.243-4.243" /></svg>
                    </button>
                </div>
                @error('password') <p class="mt-1 text-xs text-red-600 font-medium">{{ $message }}</p> @enderror
            </div>

            <div class="col-span-2 md:col-span-1">
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wide mb-1.5">Status Tugas Akhir</label>
                <div class="relative">
                    <select wire:model="ta" class="block w-full rounded-lg border-slate-200 shadow-sm focus:border-amber-500 focus:ring-amber-500 sm:text-sm px-4 py-2.5 appearance-none bg-white transition-all cursor-pointer">
                        <option value="0">Belum Mengambil</option>
                        <option value="1">Sedang Tugas Akhir</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-slate-500">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </div>
                </div>
            </div>

            <div class="col-span-2">
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wide mb-2">Role Pengguna</label>
                <div class="flex flex-wrap gap-2">
                    @foreach($allRoles as $role)
                        <label class="cursor-pointer relative group">
                            <input type="checkbox" value="{{ $role->id }}" wire:model="selectedRoles" class="peer sr-only">
                            <div class="px-4 py-2 rounded-lg border-2 text-sm font-bold transition-all duration-200 select-none flex items-center gap-2
                                bg-white border-slate-200 text-slate-500 hover:border-slate-300 hover:bg-slate-50
                                peer-checked:bg-amber-400 peer-checked:border-amber-500 peer-checked:text-slate-900 peer-checked:shadow-sm">
                                <span>{{ ucfirst($role->role) }}</span>
                                <svg class="w-4 h-4 opacity-0 peer-checked:opacity-100 transition-all duration-200 text-slate-900 transform scale-50 peer-checked:scale-100" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                                </svg>
                            </div>
                        </label>
                    @endforeach
                </div>
                @error('selectedRoles') <p class="mt-1 text-xs text-red-600 font-medium">{{ $message }}</p> @enderror
            </div>
        </div>
    </div>

    <div class="flex items-center justify-end gap-3 pt-6 border-t border-slate-100">
        <button type="button" wire:click="closeModal" 
            class="px-5 py-2.5 rounded-lg text-sm font-bold text-slate-600 bg-white border border-slate-200 hover:bg-slate-50 hover:text-slate-800 transition-all">
            Batal
        </button>
        <button type="submit" 
            class="px-5 py-2.5 rounded-lg text-sm font-bold text-white bg-slate-900 hover:bg-slate-800 shadow-lg shadow-slate-900/10 hover:shadow-slate-900/20 transition-all flex items-center gap-2">
            <span wire:loading.remove wire:target="store, update">
                {{ $isEditMode ? 'Simpan Perubahan' : 'Simpan Data' }}
            </span>
            <span wire:loading wire:target="store, update" class="flex items-center gap-2">
                Menyimpan...
            </span>
        </button>
    </div>
</form>