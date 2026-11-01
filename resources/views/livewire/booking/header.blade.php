<header class="bg-white border-b border-gray-100 shadow-sm z-10 flex-shrink-0">
    <div class="flex flex-col justify-between gap-3 px-4 pb-4 pt-[calc(env(safe-area-inset-top,0px)+0.75rem)] md:h-20 md:flex-row md:items-center md:gap-4 md:px-8 md:py-0">
        <div class="flex w-full items-center gap-2 md:w-auto md:gap-3">
            <button type="button" @click="$dispatch('toggle-sidebar')" aria-label="Buka navigasi" class="-ml-1 flex h-11 w-11 shrink-0 items-center justify-center rounded-lg text-gray-500 transition hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-amber-400 md:hidden">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
            </button>

            <div>
                <h1 class="text-xl font-bold leading-tight text-amber-500 sm:text-2xl">Booking</h1>
                <p class="mt-0.5 text-xs text-gray-400">Pilih yang ada di bawah.</p>
            </div>
        </div>

        <div
            x-data="{
                calendarOpen: false,
                selectedDate: @entangle('filterDate').live,
                today: @js(now('Asia/Jakarta')->toDateString()),
                minimumDate: '2026-01-01',
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
                            disabled: value < this.today || value < this.minimumDate || date.getMonth() !== month,
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
            class="relative w-full md:w-72"
        >
            <button type="button" @click="calendarOpen = !calendarOpen" :aria-expanded="calendarOpen" aria-label="Pilih tanggal untuk melihat jadwal booking" class="flex w-full items-center gap-3 rounded-lg border border-slate-200 bg-white px-3 py-2 text-left text-sm font-bold text-slate-700 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                <svg class="h-4 w-4 shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3M5 11h14M5 5h14a2 2 0 012 2v12a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2z"/></svg>
                <span x-text="formattedDate" class="flex-1"></span>
                <svg class="h-4 w-4 text-slate-400 transition" :class="calendarOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m19 9-7 7-7-7"/></svg>
            </button>

            <div x-show="calendarOpen" x-cloak x-transition class="absolute right-0 top-full z-50 mt-2 w-72 rounded-xl border border-slate-200 bg-white p-3 shadow-xl">
                <div class="mb-3 flex items-center justify-between">
                    <button type="button" @click="changeMonth(-1)" :disabled="month.getFullYear() === 2026 && month.getMonth() === 0" class="flex h-8 w-8 items-center justify-center rounded-lg text-slate-500 transition hover:bg-slate-100 disabled:cursor-not-allowed disabled:text-slate-200" aria-label="Bulan sebelumnya">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m15 18-6-6 6-6"/></svg>
                    </button>
                    <span x-text="monthLabel" class="text-sm font-bold capitalize text-slate-800"></span>
                    <button type="button" @click="changeMonth(1)" class="flex h-8 w-8 items-center justify-center rounded-lg text-slate-500 transition hover:bg-slate-100" aria-label="Bulan berikutnya">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6"/></svg>
                    </button>
                </div>

                <div class="grid grid-cols-7 gap-1 text-center">
                    <template x-for="weekday in weekdays" :key="weekday">
                        <span x-text="weekday" class="py-1 text-[0.65rem] font-bold uppercase text-slate-400"></span>
                    </template>
                    <template x-for="day in calendarDays" :key="day.value">
                        <button type="button" @click="chooseDate(day)" :disabled="day.disabled" :aria-pressed="selectedDate === day.value" :class="{
                            'bg-amber-500 text-white hover:bg-amber-600': selectedDate === day.value,
                            'cursor-not-allowed bg-slate-100 text-slate-300': day.disabled,
                            'text-slate-700 hover:bg-slate-100': !day.disabled && selectedDate !== day.value,
                        }" class="aspect-square rounded-md text-xs transition-colors" x-text="day.day"></button>
                    </template>
                </div>
                <p class="mt-2 text-center text-[0.65rem] text-slate-400">Pilih tanggal untuk melihat jadwal booking</p>
            </div>
        </div>
    </div>
</header>