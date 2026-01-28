{{-- ======================================================================== --}}
{{-- MODAL 1: FORM BOOKING (CREATE)                                           --}}
{{-- ======================================================================== --}}
<div x-data="{ open: @entangle('showBookModal') }"> 
    {{-- 
       PERHATIKAN: Div di atas TIDAK BOLEH ada x-show atau style="display:none".
       Dia hanya bertugas sebagai 'kantong' data.
    --}}

    <template x-teleport="body">
        {{-- Logika Tampil/Sembunyi pindah ke sini (setelah diteleport) --}}
        <div 
            x-show="open"
            x-cloak
            style="display: none; z-index: 999999 !important;" {{-- Fallback native CSS, akan dihapus Alpine saat load --}}
            x-transition.opacity.duration.300ms
            class="fixed inset-0 z-[100] flex items-center justify-center bg-black/60 backdrop-blur-sm p-4"
        >
            {{-- Klik area gelap untuk tutup --}}
            <div @click="open = false" class="absolute inset-0"></div>

            {{-- KONTEN MODAL --}}
            <div 
                x-show="open"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 scale-90"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-90"
                class="bg-white p-6 rounded-2xl w-full max-w-md shadow-2xl relative z-10"
            >
                @if($selectedInventoryId)
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-xl font-bold text-gray-800">
                            Booking <span class="text-amber-500">{{ $selectedInventoryName }}</span>
                        </h3>
                        <button @click="open = false" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    {{-- Form Input --}}
                    <div class="space-y-5">
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-1">Waktu Mulai</label>
                            <input type="datetime-local" wire:model="start_time" class="w-full rounded-xl border-gray-300 bg-gray-50 focus:border-amber-500 focus:ring-amber-500">
                            @error('start_time') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-1">Waktu Selesai</label>
                            <input type="datetime-local" wire:model="end_time" class="w-full rounded-xl border-gray-300 bg-gray-50 focus:border-amber-500 focus:ring-amber-500">
                            @error('end_time') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="mt-8 flex gap-3">
                        <button @click="open = false" class="px-4 py-2.5 rounded-xl border border-gray-300 text-gray-600 font-bold hover:bg-gray-50">Batal</button>
                        <button wire:click="createBooking" wire:loading.attr="disabled" class="flex-1 py-2.5 bg-amber-500 text-white rounded-xl font-bold hover:bg-amber-600 disabled:opacity-50">
                            <span wire:loading.remove wire:target="createBooking">Konfirmasi</span>
                            <span wire:loading wire:target="createBooking">Memproses...</span>
                        </button>
                    </div>
                @else
                    <div class="text-center py-8 text-gray-500">Memuat data...</div>
                @endif
            </div>
        </div>
    </template>
</div>

{{-- ======================================================================== --}}
{{-- MODAL 2: INFO & AKSI (CANCEL/FINISH) - UNTUK USER PEMILIK BOOKING        --}}
{{-- ======================================================================== --}}
<div x-data="{ open: @entangle('showCancelModal') }"> 
    <template x-teleport="body">
        <div 
            x-show="open"
            x-cloak
            style="display: none; z-index: 999999 !important;"
            x-transition.opacity.duration.300ms
            class="fixed inset-0 z-[100] flex items-center justify-center bg-black/60 backdrop-blur-sm p-4"
        >
            <div @click="open = false" class="absolute inset-0"></div>

            <div 
                x-show="open"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 scale-90"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-90"
                class="bg-white p-6 rounded-2xl w-full max-w-sm shadow-2xl relative z-10 overflow-hidden"
            >
                {{-- PASTIIN DATA ADA DULU BARU RENDER --}}
                @if($selectedInventoryId && $selectedBooking)
                    <div class="text-center pt-2">
                        <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-amber-100 mb-4">
                            <svg class="h-6 w-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        
                        <h3 class="text-lg font-bold text-gray-800">Status Unit</h3>
                        <p class="text-sm text-gray-500 mb-6">
                            Anda sedang menyewa <span class="font-bold text-amber-600">{{ $selectedInventoryName }}</span>
                        </p>
                        
                        {{-- Info Waktu --}}
                        <div class="bg-gray-50 rounded-xl p-4 mb-6 border border-gray-100">
                            <div class="flex justify-between text-sm mb-2">
                                <span class="text-gray-500">Mulai</span>
                                <span class="font-bold text-gray-700">{{ \Carbon\Carbon::parse($selectedBooking->start_time)->format('H:i') }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">Selesai</span>
                                <span class="font-bold text-gray-700">{{ \Carbon\Carbon::parse($selectedBooking->end_time)->format('H:i') }}</span>
                            </div>
                        </div>
                    </div>

                    <button 
                        wire:click="finishBooking" 
                        wire:loading.attr="disabled"
                        class="w-full py-3 bg-green-500 text-white shadow-lg shadow-green-200 rounded-xl font-bold hover:bg-green-600 transition-colors flex items-center justify-center gap-2"
                    >
                        <span wire:loading.remove wire:target="finishBooking">
                            <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Selesaikan Sesi
                        </span>
                        <span wire:loading wire:target="finishBooking">Memproses...</span>
                    </button>

                    <button 
                        wire:click="cancelBooking" 
                        wire:loading.attr="disabled"
                        class="w-full py-3 bg-red-50 text-red-600 border border-red-100 rounded-xl font-bold hover:bg-red-100 transition-colors flex items-center justify-center gap-2"
                    >
                        <span wire:loading.remove wire:target="cancelBooking">
                            <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            Batalkan Booking
                        </span>
                        <span wire:loading wire:target="cancelBooking">Memproses...</span>
                    </button>

                        <!-- <button @click="open = false" class="w-full py-2 mt-2 text-xs text-gray-400 hover:text-gray-600">
                            Tutup / Kembali
                        </button> -->

                @else
                    {{-- Loading State kalau data belum siap --}}
                    <!-- <div class="text-center py-8 text-gray-500">Memuat data...</div> -->
                    
                    <!-- </div> -->
                @endif
            </div>
        </div>
    </template>
</div> 