<div class="px-6 py-5 border-b border-slate-200 bg-white z-20 flex flex-col gap-4 md:flex-row md:items-center md:justify-between shrink-0">
    
    {{-- KIRI: Judul Compact --}}
    <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-lg bg-slate-100 flex items-center justify-center shrink-0">
            <svg class="w-6 h-6 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
        </div>
        <div>
            <h1 class="text-lg font-bold text-slate-900 tracking-tight leading-none">
                Manajemen Aset Fisik
            </h1>
            <p class="text-xs text-slate-500 mt-1 font-medium">Database inventaris & perlengkapan lab.</p>
        </div>
    </div>

    {{-- KANAN: Toolbar (Search + Filters + Action) --}}
    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
        
        {{-- Group Filter & Search (Menyatu agar rapi) --}}
        <div class="flex items-center gap-2 bg-slate-50 p-1 rounded-lg border border-slate-200">
            
            {{-- Search Bar (Tanpa Border Luar) --}}
            <div class="relative w-full sm:w-64">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                </div>
                <input type="text" 
                       wire:model.live.debounce.300ms="search"
                       placeholder="Cari aset..." 
                       class="w-full pl-9 pr-3 py-1.5 bg-transparent border-none text-sm focus:ring-0 placeholder-slate-400 text-slate-700">
            </div>

            {{-- Divider Vertical --}}
            <div class="w-px h-5 bg-slate-300 hidden sm:block"></div>

            {{-- Filter Kategori (Compact) --}}
            <div class="hidden sm:block relative">
                 <select wire:model.live="filterCategory" class="appearance-none bg-transparent border-none text-xs font-bold text-slate-600 focus:ring-0 py-1.5 pl-2 pr-7 cursor-pointer hover:text-slate-900 transition-colors">
                    <option value="">Semua Kategori</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}">{{ Str::limit($cat->name, 15) }}</option>
                    @endforeach
                </select>
                 {{-- Custom Arrow Icon --}}
                <div class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                    <svg class="h-3 w-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                </div>
            </div>

             {{-- Filter Brand (Compact - Opsional: Jika layar kecil disembunyikan atau digabung) --}}
             <div class="hidden md:block relative border-l border-slate-200 pl-1">
                 <select wire:model.live="filterBrand" class="appearance-none bg-transparent border-none text-xs font-bold text-slate-600 focus:ring-0 py-1.5 pl-2 pr-7 cursor-pointer hover:text-slate-900 transition-colors">
                    <option value="">Semua Merk</option>
                    @foreach($brands as $brand)
                        <option value="{{ $brand->id }}">{{ Str::limit($brand->name, 10) }}</option>
                    @endforeach
                </select>
                <div class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                    <svg class="h-3 w-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                </div>
            </div>
        </div>

        {{-- TOMBOL UTAMA (Action) --}}
        <button wire:click="create" class="px-4 py-2 bg-slate-900 text-white rounded-lg text-sm font-bold hover:bg-slate-800 shadow-md hover:shadow-lg hover:shadow-slate-900/20 transition-all flex items-center justify-center gap-2 shrink-0">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
            <span>Tambah</span>
        </button>
    </div>
</div>

{{-- Mobile Filters (Muncul hanya di layar kecil jika perlu) --}}
<div class="px-6 pb-4 sm:hidden flex gap-2">
    <select wire:model.live="filterCategory" class="w-full border-slate-200 rounded-lg text-xs py-2 bg-white">
        <option value="">Semua Kategori</option>
        @foreach($categories as $cat) <option value="{{ $cat->id }}">{{ $cat->name }}</option> @endforeach
    </select>
    <select wire:model.live="filterBrand" class="w-full border-slate-200 rounded-lg text-xs py-2 bg-white">
        <option value="">Semua Merk</option>
        @foreach($brands as $brand) <option value="{{ $brand->id }}">{{ $brand->name }}</option> @endforeach
    </select>
</div>