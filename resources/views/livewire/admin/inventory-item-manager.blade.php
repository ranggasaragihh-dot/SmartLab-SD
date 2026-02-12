<div class="flex flex-col h-screen bg-slate-50 font-sans text-slate-900 overflow-hidden">
    
    <div class="flex-1 p-6 overflow-hidden flex flex-col">
        <div class="bg-white border border-slate-200 rounded-xl shadow-sm flex flex-col h-full overflow-hidden">
            
            {{-- 1. Panggil Header --}}
            @include('livewire.admin.partials.inventory.header')

            {{-- 2. Tabel Data --}}
            <div class="flex-1 overflow-auto bg-white relative">
                
                {{-- Flash Message --}}
                @if (session()->has('message'))
                    <div class="m-4 p-4 bg-green-50 border border-green-200 text-green-700 rounded-lg flex items-center gap-2 text-sm font-medium">
                        <svg class="w-5 h-5 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        {{ session('message') }}
                    </div>
                @endif

                <table class="w-full text-left border-collapse">
                    <thead class="bg-slate-50 sticky top-0 z-10 shadow-sm">
                        <tr class="text-xs uppercase tracking-wider text-slate-500 font-bold border-b border-slate-200">
                            <th class="px-6 py-4 w-36">Kode Aset</th>
                            <th class="px-6 py-4">Informasi Barang</th>
                            <th class="px-6 py-4">Lokasi Fisik</th>
                            <th class="px-6 py-4">Spesifikasi Utama</th>
                            <th class="px-6 py-4 text-center">Kondisi</th>
                            <th class="px-6 py-4 text-right">Kontrol</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm">
                        @forelse($items as $item)
                        <tr class="hover:bg-slate-50/80 transition-colors group">
                            {{-- 1. Kode Aset --}}
                            <td class="px-6 py-4 align-top">
                                <span class="font-mono font-bold text-blue-600 bg-blue-50 px-2 py-1 rounded text-xs border border-blue-100 whitespace-nowrap">
                                    {{ $item->asset_code }}
                                </span>
                            </td>

                            {{-- 2. Info Barang --}}
                            <td class="px-6 py-4 align-top">
                                <div class="font-bold text-slate-800">{{ $item->brand->name ?? 'No Brand' }}</div>
                                <div class="text-xs text-slate-500 font-medium mt-0.5">{{ $item->category->name ?? 'Uncategorized' }}</div>
                            </td>

                            {{-- 3. Lokasi Fisik (PERBAIKAN LOGIKA DISINI) --}}
                            <td class="px-6 py-4 align-top">
                                @if($item->inventory)
                                    {{-- KONDISI: BARANG TERPASANG DI MEJA/PC --}}
                                    <div class="flex flex-col items-start">
                                        <div class="flex items-center gap-1.5 font-bold text-blue-700 bg-blue-50 px-2 py-0.5 rounded-md border border-blue-100">
                                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                            {{ $item->inventory->name }}
                                        </div>
                                        <div class="text-[10px] text-slate-400 mt-1 pl-1 flex items-center gap-1">
                                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                            {{ $item->inventory->cluster->name ?? 'Lab ?' }}
                                        </div>
                                    </div>
                                @else
                                    {{-- KONDISI: BARANG DI GUDANG --}}
                                    <div class="flex items-center gap-2">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-md bg-slate-100 text-slate-500 text-xs font-bold border border-slate-200">
                                            <svg class="w-3.5 h-3.5 mr-1.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                            Gudang / Stok
                                        </span>
                                    </div>
                                @endif
                            </td>

                            {{-- 4. Specs --}}
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
                                                <span class="text-slate-400 w-8 shrink-0 font-mono">CPU</span>
                                                <span class="font-bold text-slate-700 truncate max-w-[140px]">{{ $cpu }}</span>
                                            </div>
                                        @endif
                                        @if($ram)
                                            <div class="flex items-start gap-1 text-xs">
                                                <span class="text-slate-400 w-8 shrink-0 font-mono">RAM</span>
                                                <span class="font-bold text-slate-700">{{ $ram }}</span>
                                            </div>
                                        @endif
                                    </div>
                                @else
                                    <div class="text-xs text-slate-500 italic truncate max-w-[200px]" title="{{ $raw }}">
                                        {{ Str::limit($raw, 40) }}
                                    </div>
                                @endif
                            </td>

                            {{-- 5. Kondisi --}}
                            <td class="px-6 py-4 align-top text-center">
                                @if($item->condition == 'good')
                                    <span class="inline-flex px-2 py-1 rounded-full bg-emerald-50 text-emerald-700 text-[10px] font-bold border border-emerald-100 uppercase tracking-wide">Baik</span>
                                @elseif($item->condition == 'maintenance')
                                    <span class="inline-flex px-2 py-1 rounded-full bg-amber-50 text-amber-600 text-[10px] font-bold border border-amber-100 uppercase tracking-wide">Maintenance</span>
                                @else
                                    <span class="inline-flex px-2 py-1 rounded-full bg-red-50 text-red-600 text-[10px] font-bold border border-red-100 uppercase tracking-wide">Rusak</span>
                                @endif
                            </td>

                            {{-- 6. Aksi (TOMBOL DIRAPIKAN) --}}
                            <td class="px-6 py-4 align-top text-right">
                                <div class="flex justify-end items-center gap-1 opacity-100 sm:opacity-0 sm:group-hover:opacity-100 transition-opacity duration-200">
                                    
                                    {{-- Tombol Edit --}}
                                    <button wire:click="edit({{ $item->id }})" class="p-2 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-all" title="Edit Data">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </button>
                                    
                                    {{-- Tombol QR --}}
                                    <button wire:click="showQr({{ $item->id }})" class="p-2 text-slate-400 hover:text-slate-800 hover:bg-slate-100 rounded-lg transition-all" title="Lihat QR Code">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path></svg>
                                    </button>

                                    {{-- Tombol Hapus --}}
                                    <button wire:click="delete({{ $item->id }})" class="p-2 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-all" onclick="return confirm('Hapus aset ini permanen?')" title="Hapus Data">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="p-12 text-center text-slate-400">
                                <div class="flex flex-col items-center justify-center">
                                    <svg class="w-12 h-12 mb-3 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                    <span class="text-sm font-medium">Belum ada data inventaris.</span>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- 3. Footer Pagination --}}
            <div class="p-4 border-t border-slate-200 bg-slate-50">
                {{ $items->links() }}
            </div>
        </div>
    </div>

    {{-- 4. Panggil Modal (Sudah Modular) --}}
    @include('livewire.admin.partials.inventory.modal')
    @include('livewire.admin.partials.inventory.delete-modal') 
    @include('livewire.admin.partials.inventory.modal-qr')

</div>