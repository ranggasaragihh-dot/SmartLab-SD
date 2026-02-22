@if($showFormModal)
    @teleport('body')
    <div class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6 sm:px-0">
        <div wire:click="closeModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-[2px] transition-opacity"></div>
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden transform transition-all scale-100 relative z-10 flex flex-col max-h-[90vh] border border-slate-100">
            <div class="px-6 py-5 border-b border-slate-100 flex justify-between items-start bg-white">
                <div>
                    <h3 class="text-lg font-bold text-slate-900 tracking-tight">
                        {{ $isEdit ? 'Edit Cluster Lab' : 'Tambah Cluster Baru' }}
                    </h3>
                    <p class="text-xs text-slate-500 mt-1">Kelola informasi laboratorium dan denah ruangan.</p>
                </div>
                <button wire:click="closeModal" class="text-slate-400 hover:text-slate-600 hover:bg-slate-100 p-2 rounded-full transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="flex-1 overflow-y-auto px-6 pb-6 pt-2">
                <form wire:submit.prevent="store" id="mainForm">
                    <div class="space-y-5">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Nama Lab / Cluster</label>
                            <input type="text" wire:model="form.name" class="w-full border-slate-300 rounded-lg focus:ring-1 focus:ring-amber-500 focus:border-amber-500 text-sm py-2.5 text-slate-700 placeholder-slate-400 transition-shadow font-medium" placeholder="Contoh: Lab Jaringan Komputer">
                            @error('form.name') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1.5">Tipe Akses</label>
                                <select wire:model="form.type" class="w-full border-slate-300 rounded-lg focus:ring-1 focus:ring-amber-500 focus:border-amber-500 text-sm py-2.5 text-slate-700">
                                    <option value="public">Public (Mahasiswa)</option>
                                    <option value="private">Private (Dosen/Khusus)</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1.5">Delay (Menit)</label>
                                <div class="relative">
                                    <input type="number" wire:model="form.delay_time" min="1" class="w-full border-slate-300 rounded-lg focus:ring-1 focus:ring-amber-500 focus:border-amber-500 text-sm py-2.5 pl-3 pr-8 text-slate-700">
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <span class="text-slate-400 text-xs font-bold">min</span>
                                    </div>
                                </div>
                                @error('form.delay_time') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1.5">Min. Durasi (Menit)</label>
                                <div class="relative">
                                    <input type="number" wire:model="form.min_duration_minutes" min="1" class="w-full border-slate-300 rounded-lg focus:ring-1 focus:ring-amber-500 focus:border-amber-500 text-sm py-2.5 pl-3 pr-8 text-slate-700">
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <span class="text-slate-400 text-xs font-bold">min</span>
                                    </div>
                                </div>
                                @error('form.min_duration_minutes') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1.5">Max. Durasi (Menit)</label>
                                <div class="relative">
                                    <input type="number" wire:model="form.max_duration_minutes" min="1" class="w-full border-slate-300 rounded-lg focus:ring-1 focus:ring-amber-500 focus:border-amber-500 text-sm py-2.5 pl-3 pr-8 text-slate-700">
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <span class="text-slate-400 text-xs font-bold">min</span>
                                    </div>
                                </div>
                                @error('form.max_duration_minutes') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Deskripsi Singkat</label>
                            <textarea wire:model="form.description" rows="3" class="w-full border-slate-300 rounded-lg focus:ring-1 focus:ring-amber-500 focus:border-amber-500 text-sm py-2 text-slate-700 placeholder-slate-400" placeholder="Keterangan mengenai fasilitas atau aturan lab ini..."></textarea>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Gambar Denah</label>
                            <div class="flex items-start space-x-4 p-4 border border-dashed border-slate-300 rounded-xl bg-slate-50 hover:bg-slate-100 transition-colors relative">
                                <div class="shrink-0 relative group">
                                    @if ($image_upload)
                                        <img class="h-16 w-16 object-cover rounded-lg shadow-sm border border-amber-200" src="{{ $image_upload->temporaryUrl() }}">
                                    @elseif ($isEdit && !empty($form['denah_image'])) 
                                        <img class="h-16 w-16 object-cover rounded-lg shadow-sm border border-slate-200" src="{{ Storage::url($form['denah_image']) }}">
                                    @else
                                        <div class="h-16 w-16 rounded-lg bg-white flex items-center justify-center text-slate-300 border border-slate-200 shadow-sm">
                                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                        </div>
                                    @endif
                                    
                                    <div wire:loading.flex wire:target="image_upload" class="absolute inset-0 bg-white/80 flex items-center justify-center rounded-lg">
                                        <svg class="animate-spin h-5 w-5 text-amber-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                    </div>
                                </div>

                                <div class="flex-1">
                                    <input type="file" wire:model="image_upload" accept="image/*" class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-bold file:bg-amber-50 file:text-amber-700 hover:file:bg-amber-100 cursor-pointer">
                                    <p class="text-[10px] text-slate-400 mt-2">Format: PNG, JPG (Max. 2MB). Gambar akan otomatis di-resize.</p>
                                    @error('image_upload') <span class="text-red-500 text-xs font-bold mt-1 block">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>

                        <div class="p-4 bg-slate-50 border border-slate-100 rounded-xl flex items-center justify-between">
                            <div>
                                <span class="block text-xs font-bold text-slate-900 uppercase tracking-wide">Status Operasional</span>
                                <span class="text-[10px] text-slate-500">Jika non-aktif, lab tidak muncul di menu booking.</span>
                            </div>
                            
                            <div class="flex items-center">
                                <select wire:model="form.is_active" class="border-slate-300 rounded-lg focus:ring-1 focus:ring-amber-500 focus:border-amber-500 text-xs py-1.5 font-bold {{ $form['is_active'] ? 'text-emerald-700 bg-emerald-50 border-emerald-200' : 'text-slate-500 bg-white' }}">
                                    <option value="1">✅ Aktif</option>
                                    <option value="0">⛔ Non-Aktif</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            
            <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex justify-end gap-3 rounded-b-2xl">
                <button type="button" wire:click="closeModal" class="px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-slate-600 hover:bg-slate-50 hover:text-slate-800 text-sm font-bold transition-all shadow-sm">
                    Batal
                </button>
                <button type="submit" form="mainForm" wire:loading.attr="disabled" class="px-6 py-2.5 bg-slate-900 text-white rounded-xl hover:bg-slate-800 hover:shadow-lg hover:shadow-slate-900/20 text-sm font-bold transition-all transform active:scale-95 flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                    <span wire:loading.remove wire:target="store">{{ $isEdit ? 'Simpan Perubahan' : 'Buat Cluster' }}</span>
                    <span wire:loading wire:target="store" class="flex items-center gap-2">
                        Menyimpan...
                    </span>
                </button>
            </div>
        </div>
    </div>
    @endteleport
@endif