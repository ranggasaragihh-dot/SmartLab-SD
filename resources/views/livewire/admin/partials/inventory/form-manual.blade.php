<form id="manualForm" wire:submit.prevent="store" class="animate-fade-in">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        {{-- KOLOM KIRI --}}
        <div class="space-y-5">
            <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
                <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4">Identitas Aset</h4>
                
                <div class="mb-4">
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Kode Aset <span class="text-red-500">*</span></label>
                    <input type="text" wire:model="asset_code" class="w-full border-slate-300 rounded-lg text-sm py-2 px-3 font-mono" placeholder="INV-2024-001">
                    @error('asset_code') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Kategori</label>
                        <select wire:model="category_id" class="w-full border-slate-300 rounded-lg text-sm py-2 px-3">
                            <option value="">Pilih...</option>
                            @foreach($categories as $cat) <option value="{{ $cat->id }}">{{ $cat->name }}</option> @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Merk</label>
                        <select wire:model="brand_id" class="w-full border-slate-300 rounded-lg text-sm py-2 px-3">
                            <option value="">Pilih...</option>
                            @foreach($brands as $brand) <option value="{{ $brand->id }}">{{ $brand->name }}</option> @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-blue-700 mb-1.5">Lokasi Penempatan</label>
                    <select wire:model="inventory_id" class="w-full border-blue-200 bg-blue-50 rounded-lg text-sm py-2 px-3 text-slate-700 font-medium">
                        <option value="">🏠 Di Gudang (Belum Terpasang)</option>
                        @foreach($inventories as $inv)
                            <option value="{{ $inv->id }}">🖥️ {{ $inv->name }} ({{ $inv->cluster->name ?? 'Unknown' }})</option>
                        @endforeach
                    </select>
                </div>
            </div>
            
            {{-- Administrasi --}}
            <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
                 <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4">Administrasi</h4>
                 <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Harga Beli</label>
                        <input type="number" wire:model="purchase_price" class="w-full border-slate-300 rounded-lg text-sm py-2 px-3">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Tgl Terima</label>
                        <input type="date" wire:model="purchase_date" class="w-full border-slate-300 rounded-lg text-sm py-2 px-3">
                    </div>
                 </div>
            </div>
        </div>

        {{-- KOLOM KANAN --}}
        <div class="space-y-5">
            <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm h-full">
                <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4">Spesifikasi Teknis</h4>

                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Processor (CPU)</label>
                        <input type="text" wire:model="spec_cpu" class="w-full border-slate-300 rounded-lg text-sm py-2 px-3">
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">RAM</label>
                            <input type="text" wire:model="spec_ram" class="w-full border-slate-300 rounded-lg text-sm py-2 px-3">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Storage</label>
                            <input type="text" wire:model="spec_storage" class="w-full border-slate-300 rounded-lg text-sm py-2 px-3">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Kartu Grafis (VGA)</label>
                        <input type="text" wire:model="spec_gpu" class="w-full border-slate-300 rounded-lg text-sm py-2 px-3">
                    </div>

                    <div class="pt-4 border-t border-slate-100">
                        <label class="block text-xs font-bold text-slate-700 mb-2">Kondisi Fisik</label>
                        <div class="flex gap-4">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" wire:model="condition" value="good" class="text-emerald-600 focus:ring-emerald-500">
                                <span class="text-sm font-medium text-slate-700">Baik</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" wire:model="condition" value="maintenance" class="text-amber-600 focus:ring-amber-500">
                                <span class="text-sm font-medium text-slate-700">Perbaikan</span>
                            </label>
                             <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" wire:model="condition" value="broken" class="text-red-600 focus:ring-red-500">
                                <span class="text-sm font-medium text-slate-700">Rusak</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</form>