{{-- MODAL FORM (CREATE / EDIT) --}}
@if($isOpen)
@teleport('body')
<div class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4 transition-opacity">
    
    {{-- CONTAINER: max-w-md (Ramping & Compact) --}}
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-md overflow-hidden transform transition-all scale-100">
        
        {{-- HEADER --}}
        <div class="bg-gray-50 px-5 py-3 border-b border-gray-100 flex justify-between items-center">
            <div>
                <h3 class="text-base font-bold text-gray-800">
                    {{ $isEdit ? 'Edit Data Station' : 'Tambah Station Baru' }}
                </h3>
            </div>
            <button wire:click="closeModal" class="text-gray-400 hover:text-gray-600 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        {{-- FORM BODY --}}
        <form wire:submit.prevent="store">
            <div class="p-5 space-y-4">

                {{-- 1. Baris Atas: Lab & Kode (Grid 2 Kolom) --}}
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wide mb-1">Lokasi Lab</label>
                        <select wire:model="form.cluster_id" class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm py-2">
                            <option value="">-- Pilih --</option>
                            @foreach($clusters as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                        @error('form.cluster_id') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wide mb-1">Kode Meja</label>
                        <input wire:model="form.name" type="text" placeholder="Cth: A-01" class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm py-2">
                        @error('form.name') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>
                </div>

                {{-- 2. MAC Address (Full Width) --}}
                <div>
                    <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wide mb-1">MAC Address (Opsional)</label>
                    <input wire:model="form.mac_address" type="text" placeholder="00:1B:44:11:3A:B7" class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm font-mono bg-gray-50 py-2">
                </div>

                <div class="border-t border-gray-100 my-1"></div>

                {{-- 3. Status & Override (Grid 2 Kolom) --}}
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wide mb-1">Status Fisik</label>
                        <select wire:model="form.status" class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm py-2">
                            <option value="available">🟢 Normal</option>
                            <option value="maintenance">🟠 Maintenance</option>
                            <option value="broken">🔴 Rusak</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wide mb-1">Override Sistem</label>
                        <select wire:model="form.admin_override_mode" class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm py-2">
                            <option value="none">🛡️ Otomatis</option>
                            <option value="force_open">🔓 Paksa Buka</option>
                            <option value="force_close">🔒 Paksa Tutup</option>
                        </select>
                    </div>
                </div>

                {{-- 4. TOGGLE BUKA BOOKING (INI YANG ANDA MINTA) --}}
                <div class="mt-4">
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-1">
                        Buka untuk Booking?
                    </label>
                    
                    <div class="relative">
                        <select 
                            wire:model="form.is_bookable" 
                            class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm py-2 pl-3 pr-10 appearance-none bg-white"
                        >
                            <option value="1">✅ Ya, Izinkan Mahasiswa Booking</option>
                            <option value="0">⛔ Tidak, Sembunyikan dari Mahasiswa</option>
                        </select>
                        
                        {{-- Ikon Panah (Hiasan agar terlihat modern) --}}
                        <!-- <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div> -->
                    </div>
                    
                    <p class="text-[10px] text-gray-400 mt-1">
                        Jika pilih "Tidak", unit ini tidak akan muncul di halaman peminjaman.
                    </p>
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