<!-- <x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    <div x-data="{ state: $wire.$entangle('{{ $getStatePath() }}') }">
        
        @php
            $clusterId = $getRecord()->inventory_id ?? $get('inventory_id');
            
            $resources = \App\Models\Booking::where('id', $clusterId)
                ->orderBy('id')
                ->get();
        @endphp

        @if(!$clusterId)
            <div class="p-4 text-sm text-gray-500 bg-gray-100 rounded-lg border border-gray-200">
                👆 Silakan pilih <strong>Lokasi Lab</strong> terlebih dahulu untuk melihat denah.
            </div>
        @elseif($resources->isEmpty())
             <div class="p-4 text-sm text-yellow-600 bg-yellow-50 rounded-lg border border-yellow-200">
                ⚠️ Lab ini belum memiliki Meja/PC yang terdaftar.
            </div>
        @else
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4 p-4 bg-gray-50 rounded-xl border border-gray-200">
                
                @foreach($resources as $resource)
                    @php
                        // Tentukan Warna berdasarkan Status Resource (Bukan Booking)
                        $colorClass = match($resource->status) {
                            'available' => 'bg-white border-green-500 text-green-700 hover:bg-green-50',
                            'maintenance' => 'bg-orange-50 border-orange-300 text-orange-600 cursor-not-allowed opacity-75',
                            'broken' => 'bg-red-50 border-red-300 text-red-600 cursor-not-allowed opacity-60',
                            default => 'bg-gray-100 text-gray-400',
                        };

                        $isSelectable = $resource->status === 'available';
                    @endphp

                    <button
                        type="button"
                        @if($isSelectable)
                            @click="state = '{{ $resource->id }}'"
                        @endif
                        class="relative group flex flex-col items-center justify-center p-3 h-24 border-2 rounded-lg transition-all duration-200
                        {{ $colorClass }}
                        "
                        :class="state == '{{ $resource->id }}' ? '!border-blue-600 !bg-blue-50 ring-2 ring-blue-200 z-10' : ''"
                    >
                        <x-heroicon-o-computer-desktop class="w-8 h-8 mb-1 opacity-80" />
                        
                        <span class="text-xs font-bold">{{ $resource->code }}</span>
                        
                        <span class="text-[10px] mt-1 px-1.5 py-0.5 rounded-full bg-gray-100 text-gray-600">
                            {{ $resource->type == 'pc_station' ? 'PC' : 'Meja' }}
                        </span>

                        <div x-show="state == '{{ $resource->id }}'" class="absolute -top-2 -right-2 bg-blue-600 text-white rounded-full p-1 shadow-sm">
                            <x-heroicon-s-check class="w-3 h-3" />
                        </div>
                    </button>
                @endforeach
            
            </div>
            
            <div class="flex gap-4 mt-2 text-xs text-gray-500 px-2">
                <div class="flex items-center"><span class="w-3 h-3 bg-white border border-green-500 rounded mr-1"></span> Tersedia</div>
                <div class="flex items-center"><span class="w-3 h-3 bg-blue-50 border border-blue-600 rounded mr-1"></span> Dipilih</div>
                <div class="flex items-center"><span class="w-3 h-3 bg-orange-50 border border-orange-300 rounded mr-1"></span> Maintenance</div>
                <div class="flex items-center"><span class="w-3 h-3 bg-red-50 border border-red-300 rounded mr-1"></span> Rusak</div>
            </div>
        @endif

    </div>
</x-dynamic-component> -->