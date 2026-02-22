<div class="font-sans text-slate-900">
    @include('livewire.admin.partials.header', [
        'title'             => 'Manajemen Aset Fisik',
        'subtitle'          => 'Database inventaris & perlengkapan lab.',
        'button'            => 'Tambah Aset',
        'action'            => 'create',
        'searchable'        => true,
        'searchPlaceholder' => 'Cari aset...',
        
        'filters' => view('livewire.admin.partials.inventory.filter-popover', [
            'categories'      => $categories,
            'brands'          => $brands,
            'filterCategory'  => $filterCategory,
            'filterBrand'     => $filterBrand,
            'filterLocation'  => $filterLocation,
            'filterCondition' => $filterCondition,
            'availableBrands' => $this->availableBrands
        ])->render()
    ])

    <div class="bg-white rounded-xl shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)] border border-slate-100 overflow-hidden mt-6">

        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-slate-50 border-b border-slate-100 text-xs uppercase font-bold text-slate-500 tracking-wider">
                    <tr>
                        <th class="px-6 py-4">Kode Aset</th>
                        <th class="px-6 py-4">Informasi Barang</th>
                        <th class="px-6 py-4">Lokasi Fisik</th>
                        <th class="px-6 py-4">Spesifikasi Utama</th>
                        <th class="px-6 py-4 text-center">Kondisi</th>
                        <th class="px-6 py-4 text-right">Aksi</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-50">
                    @forelse($items as $item)
                        <tr class="hover:bg-slate-50/80 transition-colors group">
                            <td class="px-6 py-4 align-top">
                                <span class="font-mono text-xs font-semibold text-slate-600 bg-slate-100 px-2 py-1 rounded border border-slate-200">
                                    {{ $item->asset_code }}
                                </span>
                            </td>

                            <td class="px-6 py-4 align-top">
                                <div class="font-bold text-slate-800 truncate">{{ $item->brand->name ?? 'No Brand' }}</div>
                                <div class="text-xs text-slate-500 truncate">{{ $item->category->name ?? 'Uncategorized' }}</div>
                            </td>

                            <td class="px-6 py-4 align-top">
                                @if($item->inventory)
                                    <div class="font-bold text-slate-800 text-sm truncate">
                                        {{ $item->inventory->name }}
                                    </div>
                                    <div class="text-xs text-slate-500 truncate">
                                        {{ $item->inventory->cluster->name ?? 'Lab ?' }}
                                    </div>
                                @else
                                    <span class="text-xs text-slate-400 font-medium">Gudang / Stok</span>
                                @endif
                            </td>

                            <td class="px-6 py-4 align-top">
                                @php
                                    $specs = $item->specifications ?? [];
                                    $cpu = $specs['cpu'] ?? null;
                                    $ram = $specs['ram'] ?? null;
                                    $raw = $specs['raw_text'] ?? '-';
                                @endphp

                                @if($cpu || $ram)
                                    <div class="space-y-1">
                                        @if($cpu)
                                            <div class="flex items-start gap-1 text-xs">
                                                <span class="text-slate-400 w-8 shrink-0">CPU</span>
                                                <span class="font-medium text-slate-700 truncate max-w-[140px]">{{ $cpu }}</span>
                                            </div>
                                        @endif
                                        @if($ram)
                                            <div class="flex items-start gap-1 text-xs">
                                                <span class="text-slate-400 w-8 shrink-0">RAM</span>
                                                <span class="font-medium text-slate-700">{{ $ram }}</span>
                                            </div>
                                        @endif
                                    </div>
                                @else
                                    <div class="text-xs text-slate-500 truncate max-w-[200px]" title="{{ $raw }}">
                                        {{ Str::limit($raw, 40) }}
                                    </div>
                                @endif
                            </td>

                            <td class="px-6 py-4 align-top text-center">
                                @if($item->condition == 'good')
                                    <span class="text-xs text-slate-400 font-medium">Baik</span>
                                @elseif($item->condition == 'maintenance')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                        Maintenance
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-red-50 text-red-700 border border-red-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                                        Rusak
                                    </span>
                                @endif
                            </td>

                            <td class="px-6 py-4 align-top text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button wire:click="edit({{ $item->id }})" class="p-1.5 text-slate-400 hover:text-amber-600 hover:bg-amber-50 rounded-md transition-all" title="Edit Data">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                    </button>
                                    <button wire:click="showQr({{ $item->id }})" class="p-1.5 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-md transition-all" title="Lihat QR Code">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path></svg>
                                    </button>
                                    <button wire:click="confirmDelete({{ $item->id }})" class="p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-md transition-all" title="Hapus Data">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-500">
                                Tidak ada data ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
            
        <div class="px-6 py-4 border-t border-slate-100 bg-slate-50">
            {{ $items->links() }}
        </div>
    </div>
    
    @include('livewire.admin.partials.inventory.modal')
    @include('livewire.admin.partials.inventory.delete-modal') 
    @include('livewire.admin.partials.inventory.modal-qr')
</div>