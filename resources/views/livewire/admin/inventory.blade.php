{{-- 1. PEMBUNGKUS UTAMA (ROOT DIV) - WAJIB ADA --}}
<div class="space-y-6">

    {{-- 2. HEADER PAGE --}}
    {{-- Pastikan file ada di: resources/views/livewire/admin/partials/header.blade.php --}}
    @include('livewire.admin.partials.header', [
        'title'    => 'Manajemen Unit PC',
        'subtitle' => 'Daftar inventaris komputer dan meja.',
        'button'   => 'Registrasi PC',
        'action'   => 'create' 
    ])

    {{-- 3. FLASH MESSAGE (Opsional, jika tidak ada di header) --}}
    @if (session()->has('message'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded shadow-sm">
            {{ session('message') }}
        </div>
    @endif

    {{-- 4. FILTER SECTION --}}
    {{-- Pastikan file ada di: resources/views/livewire/admin/partials/filters.blade.php --}}
    @include('livewire.admin.partials.filters')

    {{-- 5. TABEL DATA --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-500">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 border-b">
                    <tr>
                        <th class="px-6 py-3">Lokasi Lab</th>
                        <th class="px-6 py-3">Kode Meja</th>
                        <th class="px-6 py-3">Status Fisik</th>
                        <th class="px-6 py-3">Mode & Booking</th>
                        <th class="px-6 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($inventories as $item)
                        <tr class="bg-white border-b hover:bg-gray-50 transition-colors">
                            
                            {{-- Lokasi --}}
                            <td class="px-6 py-4 font-medium text-gray-900">
                                @if($item->cluster)
                                    {{-- Jadikan Link ke Halaman Denah --}}
                                    <!-- <a href="{{ route('admin.lab.manager', $item->cluster->id) }}" 
                                    class="group flex items-center gap-1 bg-blue-50 hover:bg-blue-100 text-blue-600 hover:text-blue-800 text-xs font-bold px-2.5 py-1 rounded-full border border-blue-200 transition-all cursor-pointer w-fit"
                                    title="Klik untuk lihat Denah {{ $item->cluster->name }}">
                                        
                                        {{-- Ikon Peta Kecil --}}
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                                        
                                        {{ $item->cluster->name }}
                                        
                                        <span class="text-blue-400 group-hover:translate-x-1 transition-transform">&rarr;</span>
                                    </a> -->

                                    {{ $item->cluster->name }}
                                @else
                                    <span class="text-gray-400 text-xs italic">- Tanpa Lokasi -</span>
                                @endif
                            </td>

                            {{-- Nama Station --}}
                            <td class="px-6 py-4">
                                <div class="font-bold text-gray-800">{{ $item->name }}</div>
                                <div class="text-xs text-gray-400 font-mono">{{ $item->mac_address }}</div>
                            </td>

                            {{-- Status --}}
                            <td class="px-6 py-4">
                                @if($item->status == 'available')
                                    <span class="inline-flex items-center bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded-full">Available</span>
                                @elseif($item->status == 'maintenance')
                                    <span class="inline-flex items-center bg-yellow-100 text-yellow-800 text-xs font-medium px-2.5 py-0.5 rounded-full">Maintenance</span>
                                @else
                                    <span class="inline-flex items-center bg-red-100 text-red-800 text-xs font-medium px-2.5 py-0.5 rounded-full">Broken</span>
                                @endif
                            </td>

                            {{-- Mode --}}
                            <td class="px-6 py-4">
                                <div class="flex flex-col gap-1">
                                    @if($item->admin_override_mode == 'force_open')
                                        <span class="text-green-600 font-bold text-[10px] uppercase">🔓 Force Open</span>
                                    @elseif($item->admin_override_mode == 'force_close')
                                        <span class="text-red-600 font-bold text-[10px] uppercase">🔒 Force Close</span>
                                    @else
                                        <span class="text-gray-400 text-[10px] uppercase">🛡️ System Auto</span>
                                    @endif
                                    
                                    @if(!$item->is_bookable)
                                        <span class="text-red-500 text-[10px] font-bold">🚫 Booking Closed</span>
                                    @endif
                                </div>
                            </td>

                            {{-- Aksi --}}
                            <td class="px-6 py-4 text-center flex justify-center gap-2">
                                {{-- Edit --}}
                                <button wire:click="edit({{ $item->id }})" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </button>
                                {{-- QR --}}
                                <button wire:click="showQr({{ $item->id }})" class="p-2 text-gray-600 hover:bg-gray-100 rounded-lg">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                                </button>
                                {{-- Delete --}}
                                <button wire:click="confirmDelete({{ $item->id }})" class="p-2 text-red-600 hover:bg-red-50 rounded-lg">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center py-8 text-gray-500">Data kosong.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50">
            {{ $inventories->links() }}
        </div>
    </div>

    {{-- 6. INCLUDE MODALS (Harus di dalam div utama, tapi di paling bawah) --}}
    {{-- Pastikan nama folder dan file sesuai --}}
    
    @include('livewire.admin.partials.modal-form')
    @include('livewire.admin.partials.modal-delete')
    @include('livewire.admin.partials.modal-qr')

</div> {{-- END ROOT DIV (Sangat Penting!) --}}