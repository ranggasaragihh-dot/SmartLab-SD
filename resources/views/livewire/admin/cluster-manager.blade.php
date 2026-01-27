<div class="p-6">
    {{-- Header & Tombol Tambah --}}
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Manajemen Cluster Lab</h2>
        <button wire:click="create" 
            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg shadow-md flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
            </svg>
            Tambah Cluster
        </button>
    </div>

    {{-- Flash Message --}}
    @if (session()->has('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
            <p>{{ session('success') }}</p>
        </div>
    @endif
    @if (session()->has('error'))
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
            <p>{{ session('error') }}</p>
        </div>
    @endif

    {{-- Tabel Cluster --}}
    <div class="bg-white rounded-xl shadow-sm overflow-hidden border border-gray-100">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Lab</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipe</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Config (Delay)</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($clusters as $cluster)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                @if($cluster->denah_image)
                                    <img class="h-10 w-10 rounded-lg object-cover mr-3 border" 
                                         src="{{ Storage::url($cluster->denah_image) }}" 
                                         alt="Denah">
                                @else
                                    <div class="h-10 w-10 rounded-lg bg-gray-200 mr-3 flex items-center justify-center text-gray-400">
                                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                @endif
                                <div>
                                    <div class="text-sm font-medium text-gray-900">{{ $cluster->name }}</div>
                                    <div class="text-xs text-gray-500">{{ Str::limit($cluster->description, 30) }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $cluster->type === 'public' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800' }}">
                                {{ ucfirst($cluster->type) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            {{ $cluster->delay_time ?? 15 }} Menit
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $cluster->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $cluster->is_active ? 'Active' : 'Maintenance' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <button wire:click="edit({{ $cluster->id }})" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</button>
                            <button wire:click="delete({{ $cluster->id }})" 
                                    wire:confirm="Yakin ingin menghapus Lab ini? Data PC di dalamnya mungkin akan bermasalah."
                                    class="text-red-600 hover:text-red-900">Hapus</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-10 text-center text-gray-500 italic">
                            Belum ada data cluster. Silakan tambah baru.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- MODAL FORM (Create / Edit) --}}
    @if($showFormModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                
                {{-- Backdrop --}}
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closeModal"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                {{-- Modal Panel --}}
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <form wire:submit.prevent="store">
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <div class="mb-4">
                                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                    {{ $isEdit ? 'Edit Cluster' : 'Tambah Cluster Baru' }}
                                </h3>
                            </div>

                            <div class="space-y-4">
                                {{-- Nama Lab --}}
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Nama Lab / Cluster</label>
                                    <input type="text" wire:model="form.name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm border p-2">
                                    @error('form.name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>

                                {{-- Tipe & Delay Time (Grid 2 Kolom) --}}
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Tipe Akses</label>
                                        <select wire:model="form.type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm border p-2">
                                            <option value="public">Public (Mahasiswa)</option>
                                            <option value="private">Private (Dosen/Khusus)</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Waktu Delay (Menit)</label>
                                        <input type="number" wire:model="form.delay_time" min="1" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm border p-2">
                                        <p class="text-xs text-gray-400 mt-1">Batas waktu sebelum auto-cancel.</p>
                                        @error('form.delay_time') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>
                                </div>

                                {{-- Deskripsi --}}
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Deskripsi</label>
                                    <textarea wire:model="form.description" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm border p-2"></textarea>
                                </div>

                                {{-- UPLOAD GAMBAR DENAH --}}
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Gambar Denah</label>
                                    
                                    <div class="flex items-center space-x-4">
                                        {{-- Preview Logic --}}
                                        <div class="shrink-0">
                                            @if ($image_upload)
                                                {{-- Preview Gambar Baru yang Dipilih --}}
                                                <img class="h-16 w-16 object-cover rounded-md border border-blue-300" 
                                                     src="{{ $image_upload->temporaryUrl() }}" alt="New Preview">
                                            @elseif ($isEdit && !empty($form['denah_image']))
                                                {{-- Preview Gambar Lama dari Database --}}
                                                <img class="h-16 w-16 object-cover rounded-md border border-gray-300" 
                                                     src="{{ Storage::url($form['denah_image']) }}" alt="Old Preview">
                                            @else
                                                <div class="h-16 w-16 rounded-md bg-gray-100 flex items-center justify-center text-gray-400 border border-dashed border-gray-300">
                                                    <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                    </svg>
                                                </div>
                                            @endif
                                        </div>

                                        {{-- Input File --}}
                                        <div class="flex-1">
                                            <input type="file" wire:model="image_upload" accept="image/*"
                                                class="block w-full text-sm text-gray-500
                                                file:mr-4 file:py-2 file:px-4
                                                file:rounded-full file:border-0
                                                file:text-sm file:font-semibold
                                                file:bg-blue-50 file:text-blue-700
                                                hover:file:bg-blue-100">
                                            <p class="text-xs text-gray-500 mt-1">PNG, JPG up to 2MB.</p>
                                            @error('image_upload') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    
                                    {{-- Loading State saat upload --}}
                                    <div wire:loading wire:target="image_upload" class="text-xs text-blue-500 mt-2 font-semibold">
                                        Mengupload gambar...
                                    </div>
                                </div>

                                {{-- Toggle Active Status --}}
                                <div class="flex items-center">
                                    <input id="is_active" type="checkbox" wire:model="form.is_active" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                    <label for="is_active" class="ml-2 block text-sm text-gray-900">
                                        Lab Aktif (Bisa dibooking)
                                    </label>
                                </div>
                            </div>
                        </div>

                        {{-- Footer Modal --}}
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="submit" 
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm"
                                wire:loading.attr="disabled">
                                <span wire:loading.remove>Simpan</span>
                                <span wire:loading>Menyimpan...</span>
                            </button>
                            <button type="button" wire:click="closeModal" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                Batal
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>