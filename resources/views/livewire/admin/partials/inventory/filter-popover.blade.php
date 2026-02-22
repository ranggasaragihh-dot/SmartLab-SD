<div x-data="{ openFilter: false }" @click.away="openFilter = false" class="relative">
    <button 
        @click="openFilter = !openFilter" 
        type="button" 
        class="flex items-center gap-2 bg-white border border-slate-300 text-slate-700 text-sm font-bold rounded-lg px-4 py-2 hover:bg-slate-50 focus:border-amber-500 focus:ring-2 focus:ring-amber-500 transition-all shadow-sm"
    >
        <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
        <span>Filter</span>
        
        @php
            $activeCount = 0;
            if(!empty($filterCategory)) $activeCount++;
            if(!empty($filterBrand)) $activeCount++;
            if(!empty($filterLocation)) $activeCount++;
            if(!empty($filterCondition)) $activeCount++;
        @endphp

        @if($activeCount > 0)
            <span class="flex items-center justify-center w-5 h-5 ml-1 text-[10px] text-white bg-amber-500 rounded-full font-black animate-pulse">
                {{ $activeCount }}
            </span>
        @endif
    </button>

    <div 
        x-show="openFilter" 
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-2"
        style="display: none;" 
        class="absolute right-0 sm:right-auto sm:left-0 top-full mt-2 w-72 bg-white rounded-xl shadow-[0_10px_40px_-10px_rgba(0,0,0,0.15)] border border-slate-200 z-50 overflow-hidden"
    >
        <div class="px-4 py-3 border-b border-slate-100 bg-slate-50 flex items-center justify-between">
            <h3 class="text-xs font-black text-slate-800 uppercase tracking-wider">Parameter Filter</h3>
            @if($activeCount > 0)
                <button wire:click="resetFilters" class="text-[10px] font-bold text-red-500 hover:text-red-700 hover:underline">
                    Reset Semua
                </button>
            @endif
        </div>

        <div class="p-4 space-y-4">
            <div>
                <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1.5">Kategori Aset</label>
                <select wire:model.live="filterCategory" class="w-full text-sm border-slate-300 rounded-md focus:ring-slate-900 focus:border-slate-900 shadow-sm py-1.5 px-3">
                    <option value="">Semua Kategori</option>
                    @foreach($categories as $cat) 
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option> 
                    @endforeach
                </select>
            </div>

            <div class="mt-4">
                <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1.5">Merk / Pabrikan</label>
                
                <select 
                    wire:model.live="filterBrand" 
                    @disabled(empty($filterCategory))
                    class="w-full text-sm border-slate-300 rounded-md shadow-sm py-1.5 px-3 transition-colors focus:ring-amber-500 focus:border-amber-500 disabled:bg-slate-100 disabled:text-slate-400 disabled:border-slate-200 disabled:cursor-not-allowed"
                >
                    @if(empty($filterCategory))
                        <option value="">🔒 Pilih Kategori Dulu</option>
                    @else
                        <option value="">Semua Merk</option>
                    @endif
                    
                    @foreach($availableBrands as $brand) 
                        <option value="{{ $brand->id }}">{{ $brand->name }}</option> 
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1.5">Lokasi Ruangan</label>
                <select wire:model.live="filterLocation" class="w-full text-sm border-slate-300 rounded-md focus:ring-amber-500 focus:border-amber-500 shadow-sm py-1.5 px-3">
                    <option value="">Semua Lokasi</option>
                    @if(\App\Models\InventoryItem::whereNull('inventory_id')->exists())
                        <option value="NULL" class="font-bold text-amber-600">📦 Gudang / Stok</option>
                    @endif
                    @foreach($this->inventories as $inv) 
                        <option value="{{ $inv->id }}">{{ $inv->name }}</option> 
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1.5">Kondisi Fisik</label>
                <select wire:model.live="filterCondition" class="w-full text-sm border-slate-300 rounded-md focus:ring-amber-500 focus:border-amber-500 shadow-sm py-1.5 px-3">
                    <option value="">Semua Kondisi</option>
                    @foreach($this->activeConditions as $status)
                        <option value="{{ $status }}">
                            @if($status === 'good') Baik
                            @elseif($status === 'maintenance') Perawatan
                            @elseif($status === 'broken') Rusak
                            @else {{ ucfirst($status) }}
                            @endif
                        </option>
                    @endforeach
                </select>
            </div>

        </div>
    </div>
</div>