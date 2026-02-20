<form id="manualForm" wire:submit.prevent="store" class="animate-fade-in">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        {{-- KOLOM KIRI --}}
        <div class="space-y-5">
            
            {{-- IDENTITAS --}}
            <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
                <h4 class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-3 flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/></svg>
                    Identitas Aset
                </h4>

                {{-- Nama Barang --}}
                <div class="mb-4">
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Nama Barang <span class="text-slate-400">*</span></label>
                    <input type="text" wire:model="item_name"
                        class="w-full border-slate-300 rounded-lg focus:ring-1 focus:ring-slate-900 focus:border-slate-900 text-sm py-2 px-3 placeholder-slate-400 transition-colors"
                        placeholder="Contoh: Laptop Asus ROG Strix G15">
                    @error('item_name') <span class="text-xs font-bold text-slate-500 mt-1 block">! {{ $message }}</span> @enderror
                </div>

                <div class="grid grid-cols-2 gap-4 mb-4">
    
                    {{-- BLOK 1: KATEGORI --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Kategori</label>
                        
                        @if(!$isNewCategory)
                            {{-- Mode Dropdown (Default) --}}
                            <select wire:model.live="category_id" class="w-full border-slate-300 rounded-lg focus:ring-1 focus:ring-slate-900 focus:border-slate-900 text-sm py-2 px-3 text-slate-700 cursor-pointer transition-colors">
                                <option value="">Pilih Kategori...</option>
                                @foreach($categories as $cat) 
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option> 
                                @endforeach
                                <option disabled>──────────</option>
                                <option value="NEW" class="font-bold text-slate-900 bg-slate-100">+ Tambah Kategori Baru...</option>
                            </select>
                        @else
                            {{-- Mode Input Teks Baru --}}
                            <div class="relative flex items-center">
                                <input type="text" wire:model="new_category_name" placeholder="Ketik nama kategori baru..." class="w-full border-slate-900 ring-1 ring-slate-900 rounded-lg focus:ring-2 focus:ring-slate-900 focus:border-slate-900 text-sm py-2 pl-3 pr-10 text-slate-900 bg-slate-50 transition-all">
                                <button wire:click="cancelNewCategory" type="button" class="absolute right-2 text-slate-400 hover:text-red-500 p-1 transition-colors" title="Batal tambah kategori">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                        @endif
                        
                        {{-- Validasi Error --}}
                        @error('category_id') <span class="text-red-500 text-[10px] font-bold mt-1 block">{{ $message }}</span> @enderror
                        @error('new_category_name') <span class="text-red-500 text-[10px] font-bold mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    {{-- BLOK 2: MERK --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Merk</label>
                        
                        @if(!$isNewBrand)
                            {{-- Mode Dropdown (Default) --}}
                            <select wire:model.live="brand_id" class="w-full border-slate-300 rounded-lg focus:ring-1 focus:ring-slate-900 focus:border-slate-900 text-sm py-2 px-3 text-slate-700 cursor-pointer transition-colors">
                                <option value="">Pilih Merk...</option>
                                @foreach($brands as $brand) 
                                    <option value="{{ $brand->id }}">{{ $brand->name }}</option> 
                                @endforeach
                                <option disabled>──────────</option>
                                <option value="NEW" class="font-bold text-slate-900 bg-slate-100">+ Tambah Merk Baru...</option>
                            </select>
                        @else
                            {{-- Mode Input Teks Baru --}}
                            <div class="relative flex items-center">
                                <input type="text" wire:model="new_brand_name" placeholder="Ketik nama merk baru..." class="w-full border-slate-900 ring-1 ring-slate-900 rounded-lg focus:ring-2 focus:ring-slate-900 focus:border-slate-900 text-sm py-2 pl-3 pr-10 text-slate-900 bg-slate-50 transition-all">
                                <button wire:click="cancelNewBrand" type="button" class="absolute right-2 text-slate-400 hover:text-red-500 p-1 transition-colors" title="Batal tambah merk">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                        @endif

                        {{-- Validasi Error --}}
                        @error('brand_id') <span class="text-red-500 text-[10px] font-bold mt-1 block">{{ $message }}</span> @enderror
                        @error('new_brand_name') <span class="text-red-500 text-[10px] font-bold mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    {{-- BLOK 3: TIPE ASET --}}
                    <div class="mb-4 relative">
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Tipe Aset <span class="text-red-500">*</span></label>
                        
                        @if(!$isNewAssetType)
                            {{-- TAMPILAN DROPDOWN NORMAL --}}
                            <select wire:model.live="asset_type" class="w-full border-slate-300 rounded-lg focus:ring-1 focus:ring-slate-900 focus:border-slate-900 text-sm py-2 px-3 text-slate-700 cursor-pointer transition-colors">
                                <option value="">Pilih Tipe...</option>
                                
                                {{-- Loop dari database (Nilai yang sudah pernah diinput) --}}
                                @foreach($this->availableAssetTypes as $type)
                                    <option value="{{ $type }}">{{ $type }}</option>
                                @endforeach
                                <option disabled>──────────</option>
                                <option value="NEW" class="font-bold text-slate-900 bg-slate-100">+ Tambah Tipe Baru...</option>
                            </select>
                            @error('asset_type') <span class="text-xs font-bold text-red-500 mt-1 block">{{ $message }}</span> @enderror
                        @else
                            {{-- TAMPILAN INPUT MANUAL BARU --}}
                            <div class="flex items-center gap-2">
                                <input type="text" wire:model="new_asset_type_name" 
                                    placeholder="Ketik tipe baru..." 
                                    class="w-full border-blue-300 rounded-lg focus:ring-1 focus:ring-blue-500 text-sm py-2 px-3">
                                <button type="button" wire:click="cancelNewAssetType" class="p-2 text-slate-400 hover:text-red-500 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                </button>
                            </div>
                            <p class="text-[10px] text-blue-500 mt-1">Sistem akan otomatis merapikan huruf kapital.</p>
                            @error('new_asset_type_name') <span class="text-xs font-bold text-red-500 mt-1 block">{{ $message }}</span> @enderror
                        @endif
                    </div>

                    {{-- BLOK 4: LOKASI --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Lokasi Penempatan</label>
                        <div class="relative">
                            <select wire:model="inventory_id" class="w-full border-slate-300 bg-slate-50/50 rounded-lg focus:ring-1 focus:ring-slate-900 focus:border-slate-900 text-sm py-2 px-3 text-slate-700 font-medium cursor-pointer">
                                <option value="">📦 Di Gudang (Stok Cadangan)</option>
                                @foreach($inventories as $inv)
                                    <option value="{{ $inv->id }}">🖥️ {{ $inv->name }} ({{ $inv->cluster->name ?? '?' }})</option>
                                @endforeach
                            </select>
                        </div>
                        @error('inventory_id') <span class="text-red-500 text-[10px] font-bold mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            {{-- KARTU 2: KONTROL STOK & KODE ASET (Versi Standard & Clean) --}}
            <div class="bg-slate-50 p-5 rounded-xl border border-slate-200 shadow-inner">
                <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-200">
                    <label class="block text-xs font-black text-slate-600 uppercase tracking-wider">Jumlah & Penomoran</label>
                    
                    {{-- Perbaikan: Font disamakan text-sm, tidak terlalu bold, background putih jelas --}}
                    <div class="flex items-center gap-2">
                        <input type="number" min="1" wire:model.live="quantity" 
                               class="w-20 text-center font-bold text-slate-700 border-slate-300 bg-white rounded-lg focus:ring-1 focus:ring-slate-900 focus:border-slate-900 text-sm py-2 shadow-sm transition-all">
                        <span class="text-[10px] font-bold text-slate-500 uppercase">Unit</span>
                    </div>
                </div>

                {{-- List Kode Aset --}}
                <div class="space-y-3">
                    <label class="block text-[10px] font-bold text-slate-400 uppercase">Daftar Kode Aset / Barcode</label>
                    
                    {{-- Container Scrollable --}}
                    <div class="bg-white border border-slate-200 rounded-lg max-h-56 overflow-y-auto p-2 space-y-2 custom-scrollbar shadow-sm">
                        @foreach($asset_codes as $index => $code)
                            <div class="flex items-center gap-2 group">
                                {{-- Nomor Urut: Dibuat lebih kecil dan rapi --}}
                                <span class="flex-shrink-0 w-8 h-8 flex items-center justify-center text-[10px] font-bold text-slate-500 bg-slate-100 rounded-lg border border-slate-200">
                                    #{{ $index + 1 }}
                                </span>
                                
                                {{-- Input Kode: UBAH DARI DASHED KE SOLID (Seperti input biasa) --}}
                                <div class="relative w-full">
                                    <input type="text" 
                                        wire:model="asset_codes.{{ $index }}" 
                                        placeholder="Contoh: INV-202X-{{ str_pad($index + 1, 3, '0', STR_PAD_LEFT) }}" 
                                        class="w-full border-slate-300 rounded-lg focus:ring-1 focus:ring-slate-900 focus:border-slate-900 text-sm py-1.5 px-3 placeholder-slate-300 transition-colors">
                                    
                                    {{-- Indikator Error di dalam input --}}
                                    @error('asset_codes.'.$index) 
                                        <div class="absolute right-2 top-1/2 -translate-y-1/2 text-red-500">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        </div>
                                    @enderror
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                
                @if($quantity > 1)
                    <button type="button" wire:click="generateSequence" class="w-full mt-4 text-[10px] font-bold text-white bg-slate-800 hover:bg-slate-900 py-2.5 rounded-lg transition-all shadow-md border border-slate-900 flex justify-center items-center gap-2">
                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.384-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                        GENERATE URUTAN OTOMATIS
                    </button>
                @endif
            </div>
        </div>
            
        {{-- KOLOM KANAN --}}
        <div class="space-y-6">
            {{-- ADMINISTRASI --}}
            <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
                <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Administrasi
                </h4>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">No. Dokumen / Invoice</label>
                        <input type="text" wire:model="document_number" class="w-full border-slate-300 rounded-lg focus:ring-1 focus:ring-slate-900 focus:border-slate-900 text-sm py-2 px-3 placeholder-slate-300" placeholder="INV/202X/...">
                        @error('document_number') <span class="text-xs font-bold text-slate-500 mt-1 block">! {{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Unit Pemilik / Dana</label>
                        <input type="text" wire:model="owner_unit" class="w-full border-slate-300 rounded-lg focus:ring-1 focus:ring-slate-900 focus:border-slate-900 text-sm py-2 px-3 placeholder-slate-300" placeholder="Yayasan / Hibah">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[10px] font-bold text-slate-700 mb-1">Harga Beli</label>
                        <div class="relative">
                            <span class="absolute left-3 top-2 text-slate-400 text-xs">Rp</span>
                            <input type="number" wire:model="purchase_price" class="w-full border-slate-300 rounded-lg focus:ring-1 focus:ring-slate-900 focus:border-slate-900 text-sm py-2 pl-8 pr-3 placeholder-slate-300">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Tgl Terima</label>
                        <input type="date" wire:model="purchase_date" class="w-full border-slate-300 rounded-lg focus:ring-1 focus:ring-slate-900 focus:border-slate-900 text-sm py-2 px-3 text-slate-600">
                    </div>
                </div>
            </div>
        
            {{-- HAPUS class 'h-full' dan ganti dengan 'h-fit' agar tinggi kartu mengikuti isinya --}}
            <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm h-fit flex flex-col">
                <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.384-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                    Spesifikasi Teknis
                </h4>

                <div class="space-y-4">
                    {{-- Inputan Specs (Semua Focus Ring diganti Slate-900) --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Processor (CPU)</label>
                        <input type="text" wire:model="spec_cpu" class="w-full border-slate-300 rounded-lg focus:ring-1 focus:ring-slate-900 focus:border-slate-900 text-sm py-2 px-3 placeholder-slate-300" placeholder="Contoh: Intel Core i5">
                    </div>
                        
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">RAM</label>
                            <input type="text" wire:model="spec_ram" class="w-full border-slate-300 rounded-lg focus:ring-1 focus:ring-slate-900 focus:border-slate-900 text-sm py-2 px-3 placeholder-slate-300" placeholder="8GB">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Storage</label>
                            <input type="text" wire:model="spec_storage" class="w-full border-slate-300 rounded-lg focus:ring-1 focus:ring-slate-900 focus:border-slate-900 text-sm py-2 px-3 placeholder-slate-300" placeholder="SSD 512GB">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Kartu Grafis (VGA)</label>
                        <input type="text" wire:model="spec_gpu" class="w-full border-slate-300 rounded-lg focus:ring-1 focus:ring-slate-900 focus:border-slate-900 text-sm py-2 px-3 placeholder-slate-300" placeholder="NVIDIA RTX 3050">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Lainnya</label>
                        <textarea wire:model="spec_other" rows="2" class="w-full border-slate-300 rounded-lg focus:ring-1 focus:ring-slate-900 focus:border-slate-900 text-sm py-2 px-3 placeholder-slate-300"></textarea>
                    </div>
                </div>

                {{-- KONDISI FISIK --}}
                {{-- Ubah mt-6 menjadi mt-4 agar jarak lebih rapat ke atas --}}
                <div class="mt-4 pt-4 border-t border-slate-100">
                    <label class="block text-xs font-bold text-slate-700 mb-3">Kondisi Fisik Saat Ini <span class="text-red-500">*</span></label>
                    <div class="flex gap-3">
                        <label class="relative flex items-center group cursor-pointer">
                            <input type="radio" wire:model="condition" value="good" class="peer sr-only">
                            <div class="w-3.5 h-3.5 border-2 border-slate-300 rounded-full peer-checked:border-emerald-500 peer-checked:bg-emerald-500 transition-all"></div>
                            <span class="ml-2 text-sm font-medium text-slate-600 peer-checked:text-emerald-700 group-hover:text-slate-800">Baik</span>
                        </label>

                        <label class="relative flex items-center group cursor-pointer">
                            <input type="radio" wire:model="condition" value="maintenance" class="peer sr-only">
                            {{-- Maintenance pakai Amber (Kuning) --}}
                            <div class="w-3.5 h-3.5 border-2 border-slate-300 rounded-full peer-checked:border-amber-500 peer-checked:bg-amber-500 transition-all"></div>
                            <span class="ml-2 text-sm font-medium text-slate-600 peer-checked:text-amber-700 group-hover:text-slate-800">Perbaikan</span>
                        </label>

                        <label class="relative flex items-center group cursor-pointer">
                            <input type="radio" wire:model="condition" value="broken" class="peer sr-only">
                            {{-- Rusak pakai Merah --}}
                            <div class="w-3.5 h-3.5 border-2 border-slate-300 rounded-full peer-checked:border-red-600 peer-checked:bg-red-600 transition-all"></div>
                            <span class="ml-2 text-sm font-medium text-slate-600 peer-checked:text-red-700 group-hover:text-slate-800">Rusak</span>
                        </label>
                    </div>
                    @error('condition') <span class="text-red-500 text-xs mt-1 block font-medium">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>
    </div>
</form>