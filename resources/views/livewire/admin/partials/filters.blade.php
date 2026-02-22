<div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 flex flex-col md:flex-row gap-4">
    <div class="flex-1 relative">
        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        </div>
        {{-- Perhatikan wire:model mengarah ke variabel di Inventory.php --}}
        <input wire:model.live.debounce.300ms="search" type="text" class="pl-10 w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 transition-colors" placeholder="Cari nama station...">
    </div>

    <div class="w-full md:w-64">
        <select wire:model.live="filterCluster" class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 cursor-pointer">
            <option value="">Semua Lokasi Lab</option>
            @foreach($clusters as $cluster)
                <option value="{{ $cluster->id }}">{{ $cluster->name }}</option>
            @endforeach
        </select>
    </div>
</div>