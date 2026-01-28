@if($showFormModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            
            {{-- Backdrop Blur --}}
            <div class="fixed inset-0 bg-gray-900 bg-opacity-60 transition-opacity backdrop-blur-sm" wire:click="closeModal"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            {{-- Modal Panel --}}
            <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">
                <form wire:submit.prevent="store">
                    
                    {{-- Header Modal --}}
                    <div class="bg-white px-6 py-5 border-b border-gray-100 flex justify-between items-center">
                        <h3 class="text-lg leading-6 font-bold text-gray-900" id="modal-title">
                            {{ $isEdit ? 'Edit Cluster Lab' : 'Tambah Cluster Baru' }}
                        </h3>
                        <button type="button" wire:click="closeModal" class="text-gray-400 hover:text-gray-500 transition">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    {{-- Body Modal --}}
                    <div class="bg-white px-6 py-6 space-y-5">
                        
                        {{-- Nama Lab --}}
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Nama Lab / Cluster</label>
                            <input type="text" wire:model="form.name" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm transition placeholder-gray-300" placeholder="Contoh: Lab Jaringan Komputer">
                            @error('form.name') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        {{-- Grid: Tipe & Delay --}}
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Tipe Akses</label>
                                <select wire:model="form.type" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    <option value="public">Public (Mahasiswa)</option>
                                    <option value="private">Private (Dosen/Khusus)</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Delay (Menit)</label>
                                <div class="relative">
                                    <input type="number" wire:model="form.delay_time" min="1" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm pl-3 pr-8">
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <span class="text-gray-400 sm:text-xs">m</span>
                                    </div>
                                </div>
                                <p class="text-[10px] text-gray-400 mt-1">Auto-cancel jika telat.</p>
                                @error('form.delay_time') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        {{-- Deskripsi --}}
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Deskripsi Singkat</label>
                            <textarea wire:model="form.description" rows="3" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="Keterangan mengenai lab ini..."></textarea>
                        </div>

                        {{-- Upload Gambar --}}
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Gambar Denah</label>
                            <div class="flex items-start space-x-4 p-4 border border-dashed border-gray-300 rounded-xl bg-gray-50">
                                {{-- Preview Area --}}
                                <div class="shrink-0">
                                    @if ($image_upload)
                                        <img class="h-16 w-16 object-cover rounded-lg shadow-sm border border-indigo-200" src="{{ $image_upload->temporaryUrl() }}">
                                    @elseif ($isEdit && !empty($form['denah_image']))
                                        <img class="h-16 w-16 object-cover rounded-lg shadow-sm border border-gray-200" src="{{ Storage::url($form['denah_image']) }}">
                                    @else
                                        <div class="h-16 w-16 rounded-lg bg-white flex items-center justify-center text-gray-300 border border-gray-200 shadow-sm">
                                            <i class="fas fa-image text-xl"></i>
                                        </div>
                                    @endif
                                </div>

                                {{-- Input File --}}
                                <div class="flex-1">
                                    <input type="file" wire:model="image_upload" accept="image/*" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer">
                                    <p class="text-xs text-gray-400 mt-2">Format: PNG, JPG (Max. 2MB)</p>
                                    @error('image_upload') <span class="text-red-500 text-xs font-bold">{{ $message }}</span> @enderror
                                    
                                    <div wire:loading wire:target="image_upload" class="text-xs text-indigo-600 font-semibold mt-1">
                                        <i class="fas fa-spinner fa-spin mr-1"></i> Uploading...
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Active Switch --}}
                        <div class="flex items-center justify-between bg-gray-50 p-3 rounded-lg border border-gray-100">
                            <span class="flex-grow flex flex-col">
                                <span class="text-sm font-medium text-gray-900">Status Aktif</span>
                                <span class="text-xs text-gray-500">Jika mati, lab tidak bisa dibooking.</span>
                            </span>
                            <div class="relative inline-block w-10 mr-2 align-middle select-none transition duration-200 ease-in">
                                <input type="checkbox" wire:model="form.is_active" id="toggle" class="toggle-checkbox absolute block w-5 h-5 rounded-full bg-white border-4 appearance-none cursor-pointer checked:right-0 right-5"/>
                                <label for="toggle" class="toggle-label block overflow-hidden h-5 rounded-full bg-gray-300 cursor-pointer peer-checked:bg-indigo-500"></label>
                                {{-- Fallback Checkbox (Jika CSS Toggle Ribet) --}}
                                <input type="checkbox" wire:model="form.is_active" class="h-5 w-5 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded ml-2">
                            </div>
                        </div>

                    </div>

                    {{-- Footer --}}
                    <div class="bg-gray-50 px-6 py-4 flex flex-row-reverse gap-3">
                        <button type="submit" wire:loading.attr="disabled" class="inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none sm:text-sm disabled:opacity-50">
                            <span wire:loading.remove>{{ $isEdit ? 'Simpan Perubahan' : 'Buat Cluster' }}</span>
                            <span wire:loading><i class="fas fa-circle-notch fa-spin mr-2"></i>Loading...</span>
                        </button>
                        <button type="button" wire:click="closeModal" class="inline-flex justify-center rounded-lg border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:text-sm">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif