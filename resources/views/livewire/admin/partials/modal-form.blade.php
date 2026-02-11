{{-- MODAL FORM (CREATE / EDIT) --}}
@if($isOpen)
@teleport('body')
<div class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6 sm:px-0">
    
    {{-- BACKDROP --}}
    <div wire:click="closeModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-[2px] transition-opacity"></div>

    {{-- MODAL CARD --}}
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden transform transition-all scale-100 relative z-10 border border-slate-100">
        
        {{-- HEADER --}}
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

        {{-- FORM BODY --}}
        <form wire:submit.prevent="store">
            <div class="p-6 space-y-5">

                {{-- 1. Baris Atas: Lab & Kode (Grid) --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    {{-- Lokasi Lab --}}
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

                    {{-- Kode Meja --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Kode Meja</label>
                        <input wire:model="form.name" type="text" placeholder="Cth: B-01" class="w-full border-slate-300 rounded-lg focus:ring-1 focus:ring-amber-500 focus:border-amber-500 text-sm py-2.5 text-slate-700 placeholder-slate-400 transition-shadow font-medium">
                        @error('form.name') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>

                {{-- 2. MAC Address (Highlighted Box) --}}
                <!-- <div class="bg-slate-50 p-3 rounded-lg border border-slate-100">
                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wide mb-1">MAC Address (Opsional)</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                        </div>
                        <input wire:model="form.mac_address" type="text" placeholder="00:1B:44:11:3A:B7" class="w-full pl-9 border-slate-300 rounded-md focus:ring-1 focus:ring-amber-500 focus:border-amber-500 text-sm font-mono bg-white py-2">
                    </div>
                </div> -->

                <div class="border-t border-slate-100"></div>

                {{-- 3. Status & Override --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    {{-- Status Fisik --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Status Fisik</label>
                        <select wire:model="form.status" class="w-full border-slate-300 rounded-lg focus:ring-1 focus:ring-amber-500 focus:border-amber-500 text-sm py-2.5 text-slate-700">
                            <option value="available">🟢 Normal (Available)</option>
                            <option value="maintenance">🟠 Perbaikan (Maintenance)</option>
                            <option value="broken">🔴 Rusak (Broken)</option>
                        </select>
                    </div>

                    {{-- Override Sistem --}}
                    <!-- <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Mode Sistem</label>
                        <select wire:model="form.admin_override_mode" class="w-full border-slate-300 rounded-lg focus:ring-1 focus:ring-amber-500 focus:border-amber-500 text-sm py-2.5 text-slate-700">
                            <option value="none">🛡️ Otomatis (System)</option>
                            <option value="force_open">🔓 Paksa Buka (Force Open)</option>
                            <option value="force_close">🔒 Paksa Tutup (Force Close)</option>
                        </select>
                    </div> -->
                </div>

                {{-- 4. TOGGLE BUKA BOOKING --}}
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

            {{-- FOOTER ACTION --}}
            <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex justify-end gap-3 rounded-b-2xl">
                <button type="button" wire:click="closeModal" class="px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-slate-600 hover:bg-slate-50 hover:text-slate-800 text-sm font-bold transition-all shadow-sm">
                    Batal
                </button>
                <button type="submit" class="px-6 py-2.5 bg-slate-900 text-white rounded-xl hover:bg-slate-800 hover:shadow-lg hover:shadow-slate-900/20 text-sm font-bold transition-all transform active:scale-95 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    Simpan Data
                </button>
            </div>
        </form>

    </div>
</div>
@endteleport
@endif