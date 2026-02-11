<div class="flex flex-col h-screen bg-slate-50 font-sans text-slate-900 overflow-hidden">
    
    <div class="flex-1 p-6 overflow-hidden flex flex-col">
        <div class="bg-white border border-slate-200 rounded-xl shadow-sm flex flex-col h-full overflow-hidden">
            
            {{-- 1. Panggil Header --}}
            @include('livewire.admin.partials.inventory.header')

            {{-- 2. Tabel Data --}}
            <div class="flex-1 overflow-auto bg-white relative">
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
                                <th class="px-6 py-4 w-32">Kode Aset</th>
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
                                    <span class="font-mono font-bold text-blue-600 bg-blue-50 px-2 py-1 rounded text-xs border border-blue-100">
                                        {{ $item->asset_code }}
                                    </span>
                                </td>

                                {{-- 2. Info Barang --}}
                                <td class="px-6 py-4 align-top">
                                    <div class="font-bold text-slate-800">{{ $item->brand->name ?? 'No Brand' }}</div>
                                    <div class="text-xs text-slate-500 font-medium mt-0.5">{{ $item->category->name ?? 'Uncategorized' }}</div>
                                </td>

                                {{-- 3. Lokasi --}}
                                <td class="px-6 py-4 align-top">
                                    @if($item->inventory)
                                        <div class="flex items-center gap-2">
                                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                            <span class="font-bold text-slate-700">{{ $item->inventory->name }}</span>
                                        </div>
                                        <div class="text-[10px] text-slate-400 pl-4 mt-0.5">
                                            {{ $item->inventory->cluster->name ?? 'Unknown Cluster' }}
                                        </div>
                                    @else
                                        <div class="flex items-center gap-2">
                                            <span class="w-2 h-2 rounded-full bg-slate-300"></span>
                                            <span class="font-bold text-slate-400 italic">Di Gudang</span>
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
                                        <div class="text-xs text-slate-500 italic truncate max-w-[180px]" title="{{ $raw }}">
                                            {{ Str::limit($raw, 45) }}
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

                                {{-- 6. Aksi --}}
                                <td class="px-6 py-4 align-top text-right">
                                    <div class="flex justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <button wire:click="edit({{ $item->id }})" class="p-1.5 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded transition-colors" title="Edit Data">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </button>
                                        <button wire:click="delete({{ $item->id }})" class="p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded transition-colors" onclick="return confirm('Hapus aset ini permanen?')" title="Hapus Data">
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
            </div>

            {{-- 3. Footer --}}
            <div class="p-4 border-t border-slate-200 bg-slate-50">
                {{ $items->links() }}
            </div>
        </div>
    </div>

    {{-- 4. Panggil Modal --}}
    @include('livewire.admin.partials.inventory.modal')
    @include('livewire.admin.partials.inventory.delete-modal') {{-- Jika ada --}}

</div>