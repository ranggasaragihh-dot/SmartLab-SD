<div class="space-y-6">

    {{-- 1. HEADER PAGE --}}
    @include('livewire.admin.partials.header', [
        'title' => 'Manajemen Ruangan',
        'subtitle' => 'Kelola daftar lab dan aksesibilitas.',
        'button' => 'Tambah Lab Baru',
        'action' => 'create' 
    ])

    {{-- 2. FLASH MESSAGE --}}
    @if (session()->has('message'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded shadow-sm flex justify-between items-center">
            <span>{{ session('message') }}</span>
            <button onclick="this.parentElement.remove()" class="text-green-700 font-bold">&times;</button>
        </div>
    @endif

    {{-- 3. TABEL DATA --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-500">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 border-b">
                    <tr>
                        <th class="px-6 py-3">Nama Lab / Ruangan</th>
                        <th class="px-6 py-3">Jenis Akses</th>
                        <th class="px-6 py-3">Kapasitas</th>
                        <th class="px-6 py-3">Status Operasional</th>
                        <th class="px-6 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($clusters as $item)
                        <tr class="bg-white border-b hover:bg-gray-50 transition-colors">
                            
                            {{-- Nama Lab & Deskripsi --}}
                            <td class="px-6 py-4">
                                <div class="font-bold text-gray-800 text-base">{{ $item->name }}</div>
                                <div class="text-xs text-gray-500 line-clamp-1 mt-0.5">
                                    {{ $item->description ?? '-' }}
                                </div>
                            </td>

                            {{-- Jenis Akses --}}
                            <td class="px-6 py-4">
                                @if($item->type == 'public')
                                    <span class="inline-flex items-center gap-1 bg-green-50 text-green-700 text-xs font-bold px-2.5 py-1 rounded-full border border-green-100">
                                        🌍 Public
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 bg-purple-50 text-purple-700 text-xs font-bold px-2.5 py-1 rounded-full border border-purple-100">
                                        🔒 Private
                                    </span>
                                @endif
                            </td>

                            {{-- Kapasitas --}}
                            <td class="px-6 py-4">
                                <span class="flex items-center gap-2 text-gray-600 font-medium">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                    {{ $item->inventory()->count() }} Unit
                                </span>
                            </td>

                            {{-- Status Aktif --}}
                            <td class="px-6 py-4">
                                @if($item->is_active)
                                    <span class="inline-flex items-center bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded-full">
                                        <span class="w-2 h-2 mr-1 bg-blue-500 rounded-full"></span>
                                        Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center bg-gray-100 text-gray-500 text-xs font-medium px-2.5 py-0.5 rounded-full">
                                        <span class="w-2 h-2 mr-1 bg-gray-400 rounded-full"></span>
                                        Tutup
                                    </span>
                                @endif
                            </td>

                            {{-- Aksi --}}
                            <td class="px-6 py-4 text-center">
                                <div class="flex justify-center items-center gap-2">
                                    {{-- Tombol Lihat Denah --}}
                                    <!-- <a href="{{ route('admin.lab.manager', $item->id) }}" 
                                       class="p-2 text-white bg-slate-800 hover:bg-slate-700 rounded-lg transition-colors shadow-sm"
                                       title="Kelola Denah">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                                    </a> -->

                                    {{-- Edit --}}
                                    <button wire:click="edit({{ $item->id }})" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg border border-transparent hover:border-blue-100 transition-colors">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </button>

                                    {{-- Delete --}}
                                    <button wire:click="delete({{ $item->id }})" 
                                            wire:confirm="Yakin ingin menghapus Lab ini?"
                                            class="p-2 text-red-600 hover:bg-red-50 rounded-lg border border-transparent hover:border-red-100 transition-colors">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-12 text-gray-400">Belum ada data Lab.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>


    {{-- 4. MODAL FORM CLUSTER (LANGSUNG DI SINI) --}}
    @if($showFormModal)
    @teleport('body')
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4 transition-opacity">
        
        {{-- CONTAINER: max-w-md (Compact) --}}
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-md overflow-hidden transform transition-all scale-100">
            
            {{-- HEADER --}}
            <div class="bg-gray-50 px-5 py-3 border-b border-gray-100 flex justify-between items-center">
                <h3 class="text-base font-bold text-gray-800">
                    {{ $isEdit ? 'Edit Ruangan' : 'Buat Ruangan Baru' }}
                </h3>
                <button wire:click="closeModal" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            {{-- FORM BODY --}}
            <form wire:submit.prevent="store">
                <div class="p-5 space-y-4">
                    
                    {{-- Nama Lab --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-1">Nama Lab / Ruangan</label>
                        <input wire:model="form.name" type="text" placeholder="Contoh: Lab Jaringan" class="w-full border-gray-300 rounded-lg text-sm py-2 focus:ring-blue-500 focus:border-blue-500">
                        @error('form.name') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>

                    {{-- Jenis Akses (Dropdown) --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-1">Jenis Akses</label>
                        <select wire:model="form.type" class="w-full border-gray-300 rounded-lg text-sm py-2 bg-white">
                            <option value="public">🌍 Public (Semua Mahasiswa)</option>
                            <option value="private">🔒 Private (Dosen/Khusus)</option>
                        </select>
                    </div>

                    <div class="mt-2 p-2 bg-yellow-100 text-xs font-mono text-red-600 border border-yellow-300">
                        DEBUG: Nilai saat ini = {{ var_export($form['is_active']) }}
                    </div>

                    {{-- Deskripsi --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-1">Deskripsi</label>
                        <textarea wire:model="form.description" rows="2" class="w-full border-gray-300 rounded-lg text-sm py-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Keterangan singkat..."></textarea>
                    </div>

                    {{-- Status Operasional (DROPDOWN SEDERHANA) --}}
                    <div class="pt-2">
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-1">Status Operasional</label>
                        <div class="relative">
                            <select wire:model="form.is_active" class="w-full border-gray-300 rounded-lg text-sm py-2 pl-3 pr-10 appearance-none bg-white">
                                <option value="1">🔵 Aktif (Bisa Digunakan)</option>
                                <option value="0">⚫ Tutup Sementara</option>
                            </select>
                            {{-- Ikon Panah --}}
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </div>
                        </div>
                        <p class="text-[10px] text-gray-400 mt-1">Jika ditutup, lab ini tidak akan muncul di menu booking.</p>
                    </div>

                </div>

                {{-- FOOTER ACTION --}}
                <div class="bg-gray-50 px-5 py-3 flex justify-end gap-2 border-t border-gray-100">
                    <button type="button" wire:click="closeModal" class="px-3 py-2 bg-white border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-100 text-xs font-medium transition">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 shadow-md text-xs font-bold transition transform active:scale-95">
                        Simpan
                    </button>
                </div>
            </form>

        </div>
    </div>
    @endteleport
    @endif

</div>