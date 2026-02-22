@if($isOpen)
@teleport('body')
<div class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6 sm:px-0">
    <div wire:click="closeModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-[2px] transition-opacity"></div>
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden transform transition-all scale-100 relative z-10 border border-slate-100">
        <div class="px-6 py-5 border-b border-slate-100 flex justify-between items-start bg-white">
            <div>
                <h3 class="text-lg font-bold text-slate-900 tracking-tight">
                    {{ $isEdit ? 'Edit Data Station' : 'Tambah Station Baru' }}
                </h3>
                <p class="text-xs text-slate-500 mt-1">Isi informasi detail unit komputer di bawah ini.</p>
            </div>
            <button wire:click="closeModal" class="text-slate-400 hover:text-slate-600 hover:bg-slate-100 p-2 rounded-full transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <form wire:submit.prevent="store">
            <div class="p-6 space-y-5">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Lokasi Lab</label>
                        <select wire:model="form.cluster_id" class="w-full border-slate-300 rounded-lg focus:ring-1 focus:ring-amber-500 focus:border-amber-500 text-sm py-2.5 text-slate-700 transition-shadow">
                            <option value="">-- Pilih Lokasi --</option>
                            @foreach($clusters as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                        @error('form.cluster_id') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Kode Meja</label>
                        <input wire:model="form.name" type="text" placeholder="Cth: B-01" class="w-full border-slate-300 rounded-lg focus:ring-1 focus:ring-amber-500 focus:border-amber-500 text-sm py-2.5 text-slate-700 placeholder-slate-400 transition-shadow font-medium">
                        @error('form.name') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="border-t border-slate-100"></div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Status Fisik</label>
                        <select wire:model="form.status" class="w-full border-slate-300 rounded-lg focus:ring-1 focus:ring-amber-500 focus:border-amber-500 text-sm py-2.5 text-slate-700">
                            <option value="available">🟢 Normal (Available)</option>
                            <option value="maintenance">🟠 Perbaikan (Maintenance)</option>
                            <option value="broken">🔴 Rusak (Broken)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Spesifikasi</label>
                        <div x-data="{ open: false }" class="relative">
                            <div class="relative">
                                <input 
                                    type="text" 
                                    wire:model.live.debounce.300ms="itemSearch"
                                    @focus="open = true" 
                                    @click.away="open = false"
                                    placeholder="Ketik Nama Barang / Kode Aset..."
                                    class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                >
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                </div>
                            </div>

                            <input type="hidden" wire:model="form.selected_item_id">

                            <div x-show="open" 
                                x-transition 
                                class="absolute z-50 w-full mt-1 bg-white border border-gray-200 rounded-md shadow-lg max-h-60 overflow-y-auto">
                                
                                <ul>
                                    <li @click="open = false; $wire.selectItem('', '')" 
                                        class="px-4 py-3 hover:bg-red-50 cursor-pointer border-b border-gray-100 text-red-600 font-bold">
                                        🚫 Lepas Aset (Kosongkan Meja)
                                    </li>

                                    @forelse($availableItems as $item)
                                        @php
                                            $isSelected = $form->selected_item_id == $item->id;
                                            $cpu = $item->specifications['cpu'] ?? '-';
                                            $ram = $item->specifications['ram'] ?? '-';
                                        @endphp

                                        <li wire:click="selectItem({{ $item->id }}, '{{ addslashes($item->item_name) }}')"
                                            @click="open = false"
                                            class="px-4 py-2 cursor-pointer hover:bg-blue-50 border-b border-gray-100 {{ $isSelected ? 'bg-blue-100' : '' }}">
                                            
                                            <div class="flex justify-between items-center">
                                                <div>
                                                    <div class="font-bold text-gray-800 text-sm">
                                                        {{ $item->item_name }}
                                                    </div>
                                                    <div class="text-xs text-gray-500">
                                                        Kode: {{ $item->asset_code }} | {{ $cpu }} / {{ $ram }}
                                                    </div>
                                                </div>
                                                
                                                @if($isSelected)
                                                    <span class="text-blue-600 font-bold">✓</span>
                                                @endif
                                            </div>
                                        </li>
                                    @empty
                                        <li class="px-4 py-3 text-sm text-gray-500 text-center">
                                            Tidak ada barang ditemukan dengan kata kunci "{{ $itemSearch }}"
                                        </li>
                                    @endforelse
                                </ul>
                            </div>
                        </div>

                        @if($form->selected_item_id)
                            <p class="text-xs text-green-600 mt-2 flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                Aset Terpilih: <b>{{ $itemSearch }}</b>
                            </p>
                        @else
                            <p class="text-xs text-gray-400 mt-2">Belum ada aset dipilih.</p>
                        @endif
                    </div>
                </div>

                <div class="p-4 bg-amber-50 border border-amber-100 rounded-xl">
                    <div class="flex items-center justify-between mb-2">
                        <label class="text-xs font-bold text-amber-900 uppercase tracking-wide">
                            Ketersediaan Booking
                        </label>
                        @if($form->is_bookable)
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-green-100 text-green-700">Aktif</span>
                        @else
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-red-100 text-red-700">Non-Aktif</span>
                        @endif
                    </div>
                    
                    <select wire:model.live="form.is_bookable" class="w-full border-amber-200 rounded-lg focus:ring-1 focus:ring-amber-500 focus:border-amber-500 text-sm py-2 bg-white text-slate-700 cursor-pointer hover:border-amber-400 transition-colors">
                        <option value="1">✅ Tampilkan & Izinkan Booking</option>
                        <option value="0">⛔ Sembunyikan dari Mahasiswa</option>
                    </select>
                    <p class="text-[10px] text-amber-700/70 mt-2">
                        *Jika diset "Sembunyikan", unit ini tidak akan muncul di menu pencarian mahasiswa.
                    </p>
                </div>

            </div>

            <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex justify-end gap-3 rounded-b-2xl">
                <button type="button" wire:click="closeModal" class="px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-slate-600 hover:bg-slate-50 hover:text-slate-800 text-sm font-bold transition-all shadow-sm">
                    Batal
                </button>
                <button type="submit" class="px-6 py-2.5 bg-slate-900 text-white rounded-xl hover:bg-slate-800 hover:shadow-lg hover:shadow-slate-900/20 text-sm font-bold transition-all transform active:scale-95 flex items-center gap-2">
                    Simpan Data
                </button>
            </div>
        </form>
    </div>
</div>
@endteleport
@endif