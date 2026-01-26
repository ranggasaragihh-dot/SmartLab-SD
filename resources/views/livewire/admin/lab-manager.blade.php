<div class="space-y-6">
    
    {{-- 1. HEADER & TOMBOL KONTROL --}}
    <div class="flex flex-col md:flex-row justify-between items-center bg-white p-4 rounded-xl shadow-sm border border-gray-100 gap-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Manajemen {{ $cluster->name }}</h1>
            <p class="text-sm text-gray-500">
                Mode saat ini: 
                <span class="font-bold {{ $mode == 'manage' ? 'text-orange-500' : 'text-blue-500' }}">
                    {{ $mode == 'manage' ? 'Atur Kursi (Teknisi)' : 'Booking (Resepsionis)' }}
                </span>
            </p>
        </div>

        <div class="max-w-7xl mx-auto flex flex-col md:flex-row items-center justify-between gap-4">
            {{-- Bagian Tengah: FILTER TANGGAL (THE MAGIC) --}}    
            <div class="flex items-center bg-gray-50 rounded-lg p-1 border border-gray-200 shadow-inner">
                {{-- Input Tanggal --}}
                <div class="relative group">
                    <input 
                        type="date" 
                        wire:model.live="filterDate" 
                        class="pl-9 pr-3 py-1.5 text-sm font-semibold text-gray-700 bg-transparent border-none focus:ring-0 cursor-pointer hover:bg-white rounded-md transition"
                    >
                </div>
                
                <div class="text-gray-300 font-light text-xl pb-1">|</div>

                {{-- Input Jam --}}
                <div class="relative group">
                    <input 
                        type="time" 
                        wire:model.live="filterTime" 
                        class="pl-9 pr-3 py-1.5 text-sm font-semibold text-gray-700 bg-transparent border-none focus:ring-0 cursor-pointer hover:bg-white rounded-md transition"
                    >
                </div>

            </div>

            <div class="border-l border-gray-300 pl-3 ml-1">
                @php 
                    $status = $this->roomBooking; 
                @endphp

                @if($status)
                    {{-- SKENARIO 1: SUDAH ADA YANG BOOKING --}}
                    @if(isset($status->is_full_room))
                        {{-- Full Room --}}
                        <div class="flex items-center gap-2">
                            <div class="text-right hidden sm:block">
                                <p class="text-[10px] text-blue-500 font-bold uppercase leading-none">Booked By</p>
                                <p class="text-xs font-bold text-gray-800 leading-none truncate w-24">{{ $status->user->name }}</p>
                            </div>
                            <button 
                                wire:click="cancelClusterBooking('{{ $status->user->id }}', '{{ $startTime }}')" 
                                class="bg-red-600 hover:bg-red-700 text-white text-xs px-3 py-2 rounded-lg font-bold shadow-sm transition flex items-center gap-1"
                            >
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                                Cancel
                            </button>
                            <button wire:click="completeClusterBooking('{{ $status->user->id }}', '{{ $startTime }}')" class="bg-green-500 hover:bg-green-600 text-white text-xs px-3 py-2 rounded-lg font-bold shadow-sm transition flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                Selesai
                            </button>
                        </div>

                    @elseif(isset($status->is_mixed))
                        {{-- Mixed / Parsial --}}
                        <div class="flex items-center gap-2">
                            <div class="text-right hidden sm:block">
                                <p class="text-[10px] text-amber-600 font-bold uppercase leading-none">Parsial</p>
                                <p class="text-xs font-bold text-gray-800 leading-none">{{ $status->booked_count }}/{{ $status->total_count }} Unit</p>
                            </div>
                            <button wire:click="cancelRoomBooking" class="bg-red-500 hover:bg-red-600 text-white text-xs px-3 py-2 rounded-lg font-bold shadow-sm transition flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                Reset
                            </button>
                        </div>
                    @endif

                @else
                    {{-- SKENARIO 2: KOSONG (AVAILABLE) --}}
                    {{-- Tombol ini HANYA MUNCUL jika tidak ada booking --}}
                    <button 
                        wire:click="$set('showClusterModal', true)" 
                        class="bg-slate-800 hover:bg-slate-900 text-white text-xs px-4 py-2 rounded-lg font-bold shadow-md shadow-gray-300 transition hover:-translate-y-0.5 flex items-center gap-2"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Booking 1 Ruangan
                    </button>
                @endif
            </div>

        </div>

        {{-- KONTEN DENAH --}}
        <div class="flex-1 p-6">
            {{-- ... load denah layout di sini ... --}}
        </div>

        <div class="flex items-center gap-4">
            {{-- Tombol Ganti Mode --}}
            <div class="bg-gray-100 p-1 rounded-lg flex">
                <button wire:click="$set('mode', 'booking')" 
                    class="px-4 py-2 rounded-md text-sm font-medium transition {{ $mode == 'booking' ? 'bg-white shadow text-blue-600' : 'text-gray-500 hover:text-gray-700' }}">
                    📅 Booking
                </button>
                <button wire:click="$set('mode', 'manage')" 
                    class="px-4 py-2 rounded-md text-sm font-medium transition {{ $mode == 'manage' ? 'bg-white shadow text-orange-600' : 'text-gray-500 hover:text-gray-700' }}">
                    🛠️ Atur Kursi
                </button>
            </div>

            {{-- Tombol Booking Satu Ruangan --}}
            <!-- <button wire:click="$set('showClusterModal', true)" class="bg-slate-800 hover:bg-slate-900 text-white px-4 py-2 rounded-lg text-sm font-bold flex items-center gap-2">
                <span>🏢</span> Booking 1 Ruangan
            </button> -->
        </div>
    </div>

    {{-- 2. AREA VISUAL DENAH --}}
    <div class="bg-white p-8 rounded-xl shadow-sm border border-gray-100 min-h-[500px]">
        @if($cluster->name == 'PC-01')
            {{-- Include Denah --}}
            {{-- Kita kirim 'items' berisi inventory yang sudah diload relasi bookingnya di Controller --}}
            @include('livewire.booking.layouts.denah', [
                'items' => $cluster->inventory, // Gunakan relasi inventory dari $cluster
                'isAdmin' => true,
                'mode' => $mode
            ])
        @else
            <div class="bg-white p-10 rounded-3xl shadow-sm text-center">
                <p class="text-gray-400">Denah untuk ruangan ini belum tersedia.</p>
            </div>
         @endif

        <div wire:loading wire:target="mode" class="absolute inset-0 bg-white/50 backdrop-blur-sm flex items-center justify-center z-10">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
        </div>
    </div>

    {{-- ======================================================================== --}}
    {{-- 3. KOLEKSI MODAL POPUP                                                   --}}
    {{-- ======================================================================== --}}
    
    {{-- A. MODAL INFO & CANCEL (KHUSUS KURSI TERISI) --}}
    @if($showCancelModal && $selectedBooking)
    @teleport('body')
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
        <div class="bg-white p-6 rounded-2xl w-full max-w-sm shadow-2xl m-4 relative overflow-hidden">
            
            {{-- Hiasan Atas --}}
            <div class="absolute top-0 left-0 w-full h-2 bg-blue-500"></div>

            <div class="text-center mb-6">
                <h3 class="text-xl font-bold text-gray-900 mt-4">Info Peminjaman</h3>
                <p class="text-sm text-gray-500">Unit: {{ $selectedInventory->name ?? '-' }}</p>
            </div>

            <div class="bg-gray-50 rounded-xl p-4 text-left space-y-3 border border-gray-200">
                {{-- Info User --}}
                <div>
                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Peminjam</span>
                    <p class="font-bold text-gray-800 text-lg">{{ $selectedBooking->user->name ?? 'User Tidak Dikenal' }}</p>
                    <p class="text-xs text-gray-500">{{ $selectedBooking->user->email ?? '-' }}</p>
                </div>
                
                <div class="border-t border-gray-200 my-2"></div>

                {{-- Info Waktu --}}
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Mulai</span>
                        <p class="font-mono text-sm font-semibold">{{ \Carbon\Carbon::parse($selectedBooking->start_time)->format('H:i') }}</p>
                    </div>
                    <div>
                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Selesai</span>
                        <p class="font-mono text-sm font-semibold">{{ \Carbon\Carbon::parse($selectedBooking->end_time)->format('H:i') }}</p>
                    </div>
                </div>
                 
                {{-- Info Status --}}
                <div class="mt-2">
                     <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Status</span>
                     <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                        {{ ucfirst($selectedBooking->status) }}
                    </span>
                </div>
            </div>

            {{-- AKSI ADMIN --}}
            <div class="mt-6 space-y-3">
                <button 
                    wire:click="cancelBooking" 
                    wire:confirm="Yakin ingin membatalkan paksa booking ini?"
                    class="w-full py-3 bg-red-50 text-red-600 border border-red-200 rounded-xl font-bold hover:bg-red-100 transition-colors flex items-center justify-center gap-2"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                    </svg>
                    Paksa Batalkan
                </button>

                <button wire:click="$set('showCancelModal', false)" class="w-full py-2 text-gray-500 hover:text-gray-700 text-sm font-medium">
                    Tutup
                </button>
            </div>
        </div>
    </div>
    @endteleport
    @endif

    {{-- B. MODAL SINGLE BOOKING (KHUSUS KURSI KOSONG) --}}
    @if($showBookingModal)
    @teleport('body')
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-md p-6">
            <h3 class="text-lg font-bold mb-4">Booking Baru: {{ $selectedInventory->name }}</h3>
            
            <div class="space-y-4">
                {{-- Form Booking --}}
                <div>
                    <label class="text-sm font-bold text-gray-500 uppercase tracking-wide">Pilih User</label>
                    <select wire:model="userId" class="w-full border-gray-300 rounded-lg bg-gray-50 mt-1">
                        <option value="">-- Pilih User --</option>
                        @foreach($users as $u)
                            <option value="{{ $u->id }}">{{ $u->name }}</option>
                        @endforeach
                    </select>
                    @error('userId') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs font-bold text-gray-500 uppercase tracking-wide">Mulai</label>
                        <input wire:model="startTime" type="datetime-local" class="w-full border-gray-300 rounded-lg mt-1">
                         @error('startTime') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="text-xs font-bold text-gray-500 uppercase tracking-wide">Selesai</label>
                        <input wire:model="endTime" type="datetime-local" class="w-full border-gray-300 rounded-lg mt-1">
                         @error('endTime') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                </div>
                
                <div>
                    <label class="text-xs font-bold text-gray-500 uppercase tracking-wide">Catatan</label>
                    <textarea wire:model="notes" class="w-full border-gray-300 rounded-lg mt-1" placeholder="Keperluan..."></textarea>
                </div>
            </div>

            <div class="flex justify-end gap-2 mt-6">
                <button wire:click="$set('showBookingModal', false)" class="px-4 py-2 border rounded-lg hover:bg-gray-50">Batal</button>
                <button wire:click="saveBooking" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-bold">Simpan</button>
            </div>
        </div>
    </div>
    @endteleport
    @endif

    {{-- C. MODAL CLUSTER BOOKING --}}
    @if($showClusterModal)
    @teleport('body')
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-md p-6 border-l-4 border-slate-800">
            <h3 class="text-lg font-bold mb-2">Booking 1 Ruangan ({{ $cluster->name }})</h3>
            <p class="text-xs text-gray-500 mb-4">Aksi ini akan membooking semua kursi yang tersedia.</p>
            
            <div class="space-y-4">
                <div>
                    <label class="text-sm font-bold text-gray-500 uppercase tracking-wide">Penanggung Jawab</label>
                    <select wire:model="userId" class="w-full border-gray-300 rounded-lg mt-1">
                        <option value="">-- Pilih --</option>
                        @foreach($users as $u)
                            <option value="{{ $u->id }}">{{ $u->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <input wire:model="startTime" type="datetime-local" class="border-gray-300 rounded-lg">
                    <input wire:model="endTime" type="datetime-local" class="border-gray-300 rounded-lg">
                </div>
                <textarea wire:model="notes" class="w-full border-gray-300 rounded-lg" placeholder="Keperluan..."></textarea>
            </div>

            <div class="flex justify-end gap-2 mt-6">
                <button wire:click="$set('showClusterModal', false)" class="px-4 py-2 border rounded-lg">Batal</button>
                <button wire:click="saveClusterBooking" class="px-4 py-2 bg-slate-800 text-white rounded-lg font-bold">Booking Semua</button>

                <!-- <button 
                    wire:click="cancelClusterBooking('{{ $userId }}', '{{ $startTime }}')"
                    class="btn btn-danger">
                    Batal
                </button>

                <button 
                    wire:click="completeClusterBooking({{ $userId }}, '{{ $startTime }}')"
                    class="btn btn-success">
                    Selesai
                </button> -->
            </div>
        </div>
    </div>
    @endteleport
    @endif

</div>