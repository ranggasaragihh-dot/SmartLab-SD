<div class="p-6 bg-white rounded-lg shadow">
    <h2 class="text-xl font-bold mb-4">Manajemen User</h2>
    <div class="space-x-2">
        {{-- Tombol Import --}}
        <button wire:click="openImportModal" class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 text-sm font-bold">
            <i class="fas fa-file-excel mr-1"></i> Import Excel
        </button>

        {{-- Tombol Tambah Manual --}}
        <button wire:click="create" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 text-sm font-bold">
            + Tambah User
        </button>
    </div>

    @if (session()->has('message'))
        <div class="bg-green-100 text-green-700 p-3 rounded mb-4">{{ session('message') }}</div>
    @endif
    @if (session()->has('error'))
        <div class="bg-red-100 text-red-700 p-3 rounded mb-4">{{ session('error') }}</div>
    @endif

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nomor Induk</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($users as $user)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 h-10 w-10">
                                @if($user->photo)
                                    <img class="h-10 w-10 rounded-full object-cover" src="{{ asset('storage/'.$user->photo) }}">
                                @else
                                    <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center text-gray-600 font-bold">
                                        {{ substr($user->name, 0, 1) }}
                                    </div>
                                @endif
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
                                <div class="text-sm text-gray-500">{{ $user->email }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex flex-wrap gap-1"> 
                            {{-- Kita panggil Accessor 'role_badges' yang kita buat di Model --}}
                            @forelse($user->role_badges as $badge)
                                <span class="px-2 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full border {{ $badge['class'] }}">
                                    {{ $badge['label'] }}
                                </span>
                            @empty
                                <span class="px-2 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-50 text-red-600 border border-red-200">
                                    No Role
                                </span>
                            @endforelse
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $user->nomor_induk ?? '-' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        {{-- Tombol Edit (Bisa untuk diri sendiri & orang lain) --}}
                        <button 
                            wire:click="edit({{ $user->id }})" 
                            class="text-indigo-600 hover:text-indigo-900 font-bold mr-3">
                            Edit
                        </button>

                        {{-- Logika Tombol Hapus --}}
                        @if($user->id === auth()->id())
                            {{-- Jika diri sendiri: Matikan tombol, beri visual disabled --}}
                            <span class="text-gray-300 cursor-not-allowed font-bold" title="Dilarang menghapus akun yang sedang login">
                                Hapus
                            </span>
                        @else
                            {{-- Jika orang lain: Tampilkan tombol hapus normal --}}
                            <button 
                                wire:click="delete({{ $user->id }})" 
                                wire:confirm="Yakin ingin menghapus user {{ $user->name }}?" 
                                class="text-red-600 hover:text-red-900 font-bold">
                                Hapus
                            </button>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $users->links() }}
    </div>

    {{-- MODAL START --}}
    @if($showModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                
                {{-- Background Overlay (Klik untuk tutup) --}}
                <div 
                    wire:click="closeModal"
                    class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" 
                    aria-hidden="true">
                </div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                {{-- Modal Panel --}}
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">
                    
                    <form wire:submit.prevent="{{ $isEditMode ? 'update' : 'store' }}">
                        
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4" id="modal-title">
                                {{ $isEditMode ? 'Edit User' : 'Tambah User' }}
                            </h3>

                            <div class="space-y-4">
                                {{-- 1. Input Nama --}}
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Nama Lengkap</label>
                                    <input type="text" wire:model="name" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>

                                {{-- 2. Input Email --}}
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Email</label>
                                    <input type="email" wire:model="email" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    @error('email') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>

                                {{-- 3. Input Nomor Induk --}}
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Nomor Induk</label>
                                    <input type="text" wire:model="nomor_induk" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    @error('nomor_induk') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>

                                {{-- 4. Input Password (Kondisional) --}}
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">
                                        Password 
                                        @if($isEditMode) <span class="text-gray-400 text-xs font-normal">(Kosongkan jika tidak ingin mengubah)</span> @endif
                                    </label>
                                    <input type="password" wire:model="password" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    @error('password') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>

                                {{-- 5. Input Roles (Checkbox Loop) --}}
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Roles (Wajib Pilih Minimal 1)</label>
                                    <div class="grid grid-cols-2 gap-2 bg-gray-50 p-3 rounded-md border border-gray-200">
                                        @foreach($allRoles as $role)
                                            <div class="flex items-center">
                                                <input 
                                                    id="role-{{ $role->id }}" 
                                                    type="checkbox" 
                                                    value="{{ $role->id }}" 
                                                    wire:model="selectedRoles"
                                                    class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                                                <label for="role-{{ $role->id }}" class="ml-2 block text-sm text-gray-900 cursor-pointer">
                                                    {{ ucfirst($role->role) }}
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                    @error('selectedRoles') <span class="text-red-500 text-xs block mt-1">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Footer Buttons --}}
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button 
                                type="submit" 
                                wire:loading.attr="disabled"
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50">
                                <span wire:loading.remove wire:target="update">Simpan Perubahan</span>
                                <span wire:loading wire:target="update">Menyimpan...</span>
                            </button>
                            <button 
                                type="button" 
                                wire:click="closeModal"
                                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                Batal
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    @endif
    {{-- MODAL END --}}

    {{-- MODAL IMPORT START --}}
@if($showImportModal)
<div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="$set('showImportModal', false)"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Import User via Excel</h3>
                
                <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-4">
                    <p class="text-sm text-blue-700">
                        Format Excel wajib memiliki header: <strong>name, email, nomor_induk</strong>.
                        <br>Password default: <strong>12345678</strong>
                        <br>Role default: <strong>Mahasiswa</strong>
                    </p>
                </div>

                <div class="mt-2">
                    <input type="file" wire:model="importFile" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                    @error('importFile') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                
                {{-- Loading Indicator --}}
                <div wire:loading wire:target="import" class="text-center w-full mt-2 text-indigo-600 font-bold">
                    Sedang memproses data... Jangan tutup halaman!
                </div>
            </div>

            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button wire:click="import" wire:loading.attr="disabled" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50">
                    Upload & Import
                </button>
                <button wire:click="$set('showImportModal', false)" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                    Batal
                </button>
            </div>
        </div>
    </div>
</div>
@endif
{{-- MODAL IMPORT END --}}
</div>