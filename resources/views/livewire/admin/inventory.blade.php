<div class="font-sans text-slate-900">

    @php
        // Membuat HTML Dropdown di sini agar Header tetap bersih/generic
        $dropdownHtml = '
        <select wire:model.live="filterCluster" class="w-full sm:w-48 px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm shadow-sm focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 cursor-pointer">
            <option value="">Semua Lokasi Lab</option>
            ' . collect($clusters)->map(fn($c) => '<option value="'.$c->id.'">'.$c->name.'</option>')->implode('') . '
        </select>
        ';
    @endphp

    {{-- 1. HEADER PAGE (SISTRUK Style) --}}
    {{-- Kita gunakan include header yang sama, aktifkan searchable --}}
    @include('livewire.admin.partials.header', [
        'title'             => 'Manajemen Unit PC',
        'subtitle'          => 'Total ' . $inventories->total() . ' unit komputer terdaftar.',
        'button'            => 'Registrasi PC',
        'action'            => 'create', 
        'searchable'        => true,
        'filters'           => $dropdownHtml,
        'searchPlaceholder' => 'Cari Nama PC, MAC, atau Meja...'
    ])

    {{-- 2. FILTER & TOOLS SECTION (Opsional, jika butuh filter Lab) --}}
    

    {{-- 3. TABEL DATA --}}
    <div class="bg-white rounded-xl shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)] border border-slate-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                {{-- Header Tabel --}}
                <thead class="bg-slate-50 border-b border-slate-100 text-xs uppercase font-bold text-slate-500 tracking-wider">
                    <tr>
                        <th class="px-6 py-4">Unit PC & MAC</th>
                        <th class="px-6 py-4">Lokasi</th>
                        <th class="px-6 py-4 text-center">Status Fisik</th>
                        <th class="px-6 py-4 text-center">Mode Sistem</th>
                        <th class="px-6 py-4 text-right">Aksi</th>
                    </tr>
                </thead>

                {{-- Body Tabel --}}
                <tbody class="divide-y divide-slate-50">
                    @forelse($inventories as $item)
                        <tr class="hover:bg-slate-50/80 transition-colors group">
                            
                            {{-- KOLOM 1: IDENTITAS PC --}}
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    {{-- Icon Monitor (Visual Anchor) --}}
                                    <div class="w-10 h-10 rounded-lg bg-slate-100 flex items-center justify-center text-slate-500 border border-slate-200 group-hover:border-amber-400 group-hover:text-amber-600 transition-colors">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                    </div>
                                    <div>
                                        <div class="font-bold text-slate-900">{{ $item->name }}</div>
                                        <div class="text-xs text-slate-500 font-mono flex items-center gap-1">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"></path></svg>
                                            {{ $item->mac_address ?? '-' }}
                                        </div>
                                    </div>
                                </div>
                            </td>

                            {{-- KOLOM 2: LOKASI --}}
                            <td class="px-6 py-4">
                                @if($item->cluster)
                                    <div class="font-bold text-slate-700">{{ $item->cluster->name }}</div>
                                    <!-- <div class="inline-flex items-center gap-1 mt-1 px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-600 border border-slate-200">
                                        Meja: <span class="text-slate-900">{{ $item->table_number ?? '?' }}</span>
                                    </div> -->
                                @else
                                    <span class="text-slate-400 text-xs italic">- Belum ditempatkan -</span>
                                @endif
                            </td>

                            {{-- KOLOM 3: STATUS FISIK (Dot Style) --}}
                            <td class="px-6 py-4 text-center">
                                @if($item->status == 'available')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        Available
                                    </span>
                                @elseif($item->status == 'maintenance')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                        Maintenance
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-red-50 text-red-700 border border-red-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                                        Broken
                                    </span>
                                @endif
                            </td>

                            {{-- KOLOM 4: MODE SISTEM (Badges) --}}
                            <td class="px-6 py-4">
                                <div class="flex flex-col items-center gap-1.5">
                                    {{-- Status Override --}}
                                    @if($item->admin_override_mode == 'force_open')
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-green-50 text-green-700 border border-green-200 w-fit">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"></path></svg>
                                            Force Open
                                        </span>
                                    @elseif($item->admin_override_mode == 'force_close')
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-red-50 text-red-700 border border-red-200 w-fit">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                            Force Close
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-blue-50 text-blue-600 border border-blue-200 w-fit">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                            System Auto
                                        </span>
                                    @endif

                                    {{-- Status Booking --}}
                                    @if(!$item->is_bookable)
                                        <span class="text-[9px] font-bold text-red-500 uppercase tracking-wide border-t border-red-100 pt-1">
                                            🚫 Booking Closed
                                        </span>
                                    @endif
                                </div>
                            </td>

                            {{-- KOLOM 5: AKSI (Sama dengan User Management) --}}
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    {{-- Edit --}}
                                    <button wire:click="edit({{ $item->id }})" class="p-1.5 text-slate-400 hover:text-amber-600 hover:bg-amber-50 rounded-md transition-all" title="Edit Data">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                    </button>
                                    
                                    {{-- QR Code (Fitur Unik PC) --}}
                                    <button wire:click="showQr({{ $item->id }})" class="p-1.5 text-slate-400 hover:text-slate-900 hover:bg-slate-100 rounded-md transition-all" title="Lihat QR Code">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z">
                                            </path>
                                        </svg>
                                    <!-- <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 17h.01M8 11h16M14.5 3H9.5a5 5 0 00-5 5v10a5 5 0 005 5h10a5 5 0 005-5V8a5 5 0 00-5-5z"></path></svg> -->
                                    </button>

                                    {{-- Delete --}}
                                    <button wire:click="confirmDelete({{ $item->id }})" class="p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-md transition-all" title="Hapus Unit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-slate-500">
                                <div class="flex flex-col items-center justify-center">
                                    <svg class="w-12 h-12 text-slate-200 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                    <p>Tidak ada data PC ditemukan.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        {{-- Footer Pagination --}}
        <div class="px-6 py-4 border-t border-slate-100 bg-slate-50">
            {{ $inventories->links() }}
        </div>
    </div>
    
    {{-- MODALS --}}
    {{-- Sesuaikan path ini dengan folder Anda nantinya --}}
    @include('livewire.admin.partials.modal-form')
    @include('livewire.admin.partials.modal-delete')
    @include('livewire.admin.partials.modal-qr')

</div>