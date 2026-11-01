<div x-data="{ open: @entangle('showBookModal') }">
    <template x-teleport="body">
        <div 
            x-show="open"
            x-cloak
            style="display: none; z-index: 999999 !important;" {{-- Fallback native CSS, akan dihapus Alpine saat load --}}
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
                class="relative z-10 w-full max-w-[460px] rounded-[24px] bg-[#f4f4f5] p-4 shadow-[0_20px_60px_rgba(15,23,42,0.18)] sm:p-5"
            >
                @if($selectedInventoryId)
                    <div class="mb-3">
                        <h3 class="text-[1.8rem] font-black leading-none tracking-[-0.04em] text-[#1f2b3d] sm:text-[2rem]">
                            Booking <span class="text-[#f4a62d]">tempat</span>
                        </h3>
                    </div>

                    <div class="space-y-2.5">
                        <div>
                            <label class="mb-1 block text-[0.65rem] font-bold uppercase tracking-[0.12em] text-[#46566b]">Tanggal</label>
                            <div
                                x-data="{
                                    calendarOpen: false,
                                    selectedDate: @entangle('filterDate').live,
                                    today: @js(now('Asia/Jakarta')->toDateString()),
                                    month: new Date(),
                                    weekdays: ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'],
                                    init() {
                                        const [year, month] = this.selectedDate.split('-').map(Number);
                                        this.month = new Date(year, month - 1, 1);
                                    },
                                    get monthLabel() {
                                        return this.month.toLocaleDateString('id-ID', { month: 'long', year: 'numeric' });
                                    },
                                    get calendarDays() {
                                        const year = this.month.getFullYear();
                                        const month = this.month.getMonth();
                                        const firstWeekday = new Date(year, month, 1).getDay();

                                        return Array.from({ length: 42 }, (_, index) => {
                                            const date = new Date(year, month, index - firstWeekday + 1);
                                            const value = `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`;

                                            return {
                                                value,
                                                day: date.getDate(),
                                                inMonth: date.getMonth() === month,
                                                disabled: value < this.today,
                                            };
                                        });
                                    },
                                    changeMonth(amount) {
                                        this.month = new Date(this.month.getFullYear(), this.month.getMonth() + amount, 1);
                                    },
                                    chooseDate(day) {
                                        if (day.disabled) return;
                                        this.selectedDate = day.value;
                                        this.calendarOpen = false;
                                    },
                                    get formattedDate() {
                                        const [year, month, day] = this.selectedDate.split('-');
                                        return `${day} / ${month} / ${year}`;
                                    },
                                }"
                                @click.outside="calendarOpen = false"
                                class="relative"
                            >
                                <button type="button" @click="calendarOpen = !calendarOpen" :aria-expanded="calendarOpen" class="flex w-full items-center rounded-[10px] border border-[#d9dee5] bg-[#edf1f5] px-3 py-1.5 text-left shadow-inner shadow-white/60">
                                    <span x-text="formattedDate" class="flex-1 text-[0.95rem] font-bold text-[#253243]"></span>
                                    <span class="ml-3 flex h-7 w-7 items-center justify-center rounded-[8px] bg-[#dfe7ef] text-[#46566b]">
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                    </span>
                                </button>

                                <div x-show="calendarOpen" x-cloak x-transition class="absolute left-0 top-full z-30 mt-2 w-72 max-w-[calc(100vw-3rem)] rounded-[14px] border border-[#e2e8f0] bg-white p-3 shadow-xl">
                                    <div class="mb-3 flex items-center justify-between">
                                        <button type="button" @click="changeMonth(-1)" :disabled="month.getFullYear() === Number(today.slice(0, 4)) && month.getMonth() === Number(today.slice(5, 7)) - 1" class="flex h-8 w-8 items-center justify-center rounded-lg text-[#475569] hover:bg-slate-100 disabled:cursor-not-allowed disabled:text-slate-200" aria-label="Bulan sebelumnya">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m15 18-6-6 6-6"/></svg>
                                        </button>
                                        <span x-text="monthLabel" class="text-sm font-bold capitalize text-[#253243]"></span>
                                        <button type="button" @click="changeMonth(1)" class="flex h-8 w-8 items-center justify-center rounded-lg text-[#475569] hover:bg-slate-100" aria-label="Bulan berikutnya">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6"/></svg>
                                        </button>
                                    </div>

                                    <div class="grid grid-cols-7 gap-1 text-center">
                                        <template x-for="weekday in weekdays" :key="weekday">
                                            <span x-text="weekday" class="py-1 text-[0.65rem] font-bold uppercase text-slate-400"></span>
                                        </template>
                                        <template x-for="day in calendarDays" :key="day.value">
                                            <button type="button" @click="chooseDate(day)" :disabled="day.disabled" :aria-pressed="selectedDate === day.value" :class="{
                                                'bg-blue-600 text-white hover:bg-blue-700': selectedDate === day.value,
                                                'cursor-not-allowed bg-slate-50 text-slate-300': day.disabled,
                                                'text-slate-400 hover:bg-slate-100': !day.disabled && !day.inMonth && selectedDate !== day.value,
                                                'text-slate-700 hover:bg-slate-100': !day.disabled && day.inMonth && selectedDate !== day.value,
                                            }" class="aspect-square rounded-md text-xs transition-colors" x-text="day.day"></button>
                                        </template>
                                    </div>
                                </div>
                            </div>
                            @error('filterDate') <span class="mt-1 block text-xs text-red-500">Tanggal booking tidak boleh sebelum hari ini.</span> @enderror
                        </div>

                        <div class="grid grid-cols-1 gap-3">
                            <div>
                                <label class="mb-1 block text-[0.65rem] font-bold uppercase tracking-[0.12em] text-[#46566b]">Mulai</label>
                                <div class="grid grid-cols-[1fr_auto_1fr] items-center gap-2 rounded-full border border-[#d9dee5] bg-[#edf1f5] px-2 py-1.5 shadow-inner shadow-white/60">
                                    <select wire:model.live="start_hour" class="w-full appearance-none rounded-full bg-transparent px-2 py-2 text-[1rem] font-bold text-[#253243] outline-none">
                                        <option value="">--</option>
                                        @for ($h = 0; $h < 24; $h++)
                                            @php $hourValue = str_pad((string) $h, 2, '0', STR_PAD_LEFT); @endphp
                                            @continue($this->isStartHourDisabled($hourValue))
                                            <option value="{{ $hourValue }}">{{ $hourValue }}</option>
                                        @endfor
                                    </select>
                                    <span class="text-[1rem] font-bold text-[#46566b]">:</span>
                                    <select wire:model.live="start_minute" @disabled($start_hour === null || $start_hour === '') class="w-full appearance-none rounded-full bg-transparent px-2 py-2 text-[1rem] font-bold text-[#253243] outline-none disabled:cursor-not-allowed disabled:opacity-50">
                                        <option value="">--</option>
                                        @for ($m = 0; $m < 60; $m++)
                                            @php $minuteValue = str_pad((string) $m, 2, '0', STR_PAD_LEFT); @endphp
                                            @continue($this->isTimeOptionDisabled('start', $this->start_hour, $minuteValue))
                                            <option value="{{ $minuteValue }}">{{ $minuteValue }}</option>
                                        @endfor
                                    </select>
                                </div>
                                @error('start_hour') <span class="mt-1 block text-xs text-red-500">{{ $message }}</span> @enderror
                                @error('start_minute') <span class="mt-1 block text-xs text-red-500">{{ $message }}</span> @enderror
                                @error('start_time') <span class="mt-1 block text-xs text-red-500">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="mb-1 block text-[0.65rem] font-bold uppercase tracking-[0.12em] text-[#46566b]">Selesai</label>
                                <div class="grid grid-cols-[1fr_auto_1fr] items-center gap-2 rounded-full border border-[#d9dee5] bg-[#edf1f5] px-2 py-1.5 shadow-inner shadow-white/60">
                                    <select wire:model.live="end_hour" @disabled($start_hour === null || $start_hour === '' || $start_minute === null || $start_minute === '') class="w-full appearance-none rounded-full bg-transparent px-2 py-2 text-[1rem] font-bold text-[#253243] outline-none disabled:cursor-not-allowed disabled:opacity-50">
                                        <option value="">--</option>
                                        @for ($h = 0; $h < 24; $h++)
                                            @php $hourValue = str_pad((string) $h, 2, '0', STR_PAD_LEFT); @endphp
                                            @continue($this->isEndHourDisabled($hourValue))
                                            <option value="{{ $hourValue }}">{{ $hourValue }}</option>
                                        @endfor
                                    </select>
                                    <span class="text-[1rem] font-bold text-[#46566b]">:</span>
                                    <select wire:model="end_minute" @disabled($start_hour === null || $start_hour === '' || $start_minute === null || $start_minute === '' || $end_hour === null || $end_hour === '') class="w-full appearance-none rounded-full bg-transparent px-2 py-2 text-[1rem] font-bold text-[#253243] outline-none disabled:cursor-not-allowed disabled:opacity-50">
                                        <option value="">--</option>
                                        @for ($m = 0; $m < 60; $m++)
                                            @php $minuteValue = str_pad((string) $m, 2, '0', STR_PAD_LEFT); @endphp
                                            @continue($this->isTimeOptionDisabled('end', $this->end_hour, $minuteValue))
                                            <option value="{{ $minuteValue }}">{{ $minuteValue }}</option>
                                        @endfor
                                    </select>
                                </div>
                                @error('end_hour') <span class="mt-1 block text-xs text-red-500">{{ $message }}</span> @enderror
                                @error('end_minute') <span class="mt-1 block text-xs text-red-500">{{ $message }}</span> @enderror
                                @error('end_time') <span class="mt-1 block text-xs text-red-500">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>

                    <p class="mt-1.5 text-[0.7rem] font-medium text-[#64748b]">
                        Durasi booking: {{ $cluster->min_duration_minutes ?? 30 }}-{{ $cluster->max_duration_minutes ?? 120 }} menit. Maksimal keterlambatan: {{ $cluster->delay_time ?? 15 }} menit.
                    </p>

                    <div class="mt-4 flex items-center justify-end gap-2.5">
                        <button @click="open = false" type="button" class="rounded-[10px] bg-[#f5f6f8] px-3.5 py-1.5 text-[0.9rem] font-bold text-[#1f2b3d] transition hover:bg-white">Batal</button>
                        <button wire:click="createBooking" wire:loading.attr="disabled" type="button" class="rounded-[10px] bg-[#f4a62d] px-3.5 py-1.5 text-[0.9rem] font-bold text-[#ffffff] transition hover:bg-[#e6981c] disabled:opacity-50">Konfirmasi</button>
                    </div>
                @else
                    <div class="text-center py-8 text-gray-500">Memuat data...</div>
                @endif
            </div>
        </div>
    </template>
</div>

<div x-data="{ 
    isCancel: @entangle('showCancelModal'), 
    isFinish: @entangle('showFinishModal') 
}"> 
    <template x-teleport="body">
        <div 
            {{-- TAMPILKAN JIKA SALAH SATU BERNILAI TRUE --}}
            x-show="isCancel || isFinish"
            x-cloak
            style="display: none; z-index: 999999 !important;"
            x-transition.opacity.duration.300ms
            class="fixed inset-0 z-[100] flex items-center justify-center bg-black/60 backdrop-blur-sm p-4"
        >
            <div @click="isCancel = false; isFinish = false" class="absolute inset-0"></div>

            <div 
                x-show="isCancel || isFinish"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 scale-90"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-90"
                class="bg-white p-6 rounded-2xl w-full max-w-sm shadow-2xl relative z-10 overflow-hidden"
            >
                @if($selectedBooking)
                    <div class="text-center pt-2">
                        <div x-show="isFinish">
                            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100 mb-4">
                                <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <h3 class="text-lg font-bold text-gray-800">Selesaikan Sesi?</h3>
                            <p class="text-sm text-gray-500 mb-6">
                                Anda akan mengakhiri penggunaan <span class="font-bold text-green-600">{{ $selectedInventoryName ?? 'Unit ini' }}</span>
                            </p>
                        </div>

                        <div x-show="isCancel">
                            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
                                <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </div>
                            <h3 class="text-lg font-bold text-gray-800">Batalkan Booking?</h3>
                            <p class="text-sm text-gray-500 mb-6">
                                Anda akan membatalkan jadwal di <span class="font-bold text-red-600">{{ $selectedInventoryName ?? 'Unit ini' }}</span>
                            </p>
                        </div>
                        
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
                        x-show="isFinish"
                        wire:click="finishBooking" 
                        wire:loading.attr="disabled"
                        class="w-full py-3 bg-green-500 text-white shadow-lg shadow-green-200 rounded-xl font-bold hover:bg-green-600 transition-colors flex items-center justify-center gap-2"
                    >
                        <span wire:loading.remove wire:target="finishBooking">
                            <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Ya, Selesaikan
                        </span>
                        <span wire:loading wire:target="finishBooking">Memproses...</span>
                    </button>

                    <button 
                        x-show="isCancel"
                        wire:click="cancelBooking" 
                        wire:loading.attr="disabled"
                        class="w-full py-3 bg-red-600 text-white shadow-lg shadow-red-200 rounded-xl font-bold hover:bg-red-700 transition-colors flex items-center justify-center gap-2"
                    >
                        <span wire:loading.remove wire:target="cancelBooking">
                            <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            Ya, Batalkan
                        </span>
                        <span wire:loading wire:target="cancelBooking">Memproses...</span>
                    </button>
                    
                    <button @click="isCancel = false; isFinish = false" class="w-full py-2 mt-3 text-xs font-bold text-gray-400 hover:text-gray-600">
                        Batal / Kembali
                    </button>
                @else
                    <div class="flex flex-col items-center justify-center py-8 text-gray-400 animate-pulse">
                        <svg class="w-8 h-8 mb-2" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                        <span class="text-xs">Memuat Data...</span>
                    </div>
                @endif
            </div>
        </div>
    </template>
</div>