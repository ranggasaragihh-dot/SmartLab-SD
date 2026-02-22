<header class="bg-white border-b border-gray-100 shadow-sm z-10 flex-shrink-0">
    <div class="px-4 py-4 md:px-8 md:h-20 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-3 w-full md:w-auto">
            <button @click="$dispatch('toggle-sidebar')" class="md:hidden text-gray-500 focus:outline-none -ml-1">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
            </button>

            <div>
                <h1 class="text-2xl font-bold text-amber-500 leading-none">Booking</h1>
                <p class="text-xs text-gray-400 mt-0.5">Pilih yang ada di bawah.</p>
            </div>
        </div>

        <div class="w-full md:w-auto">
            <div class="grid grid-cols-2 md:flex md:flex-row gap-3">
                <div class="relative w-full">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <input type="datetime-local" 
                        wire:model.live="filterDate" 
                        class="w-full pl-10 pr-3 py-2 bg-white border border-slate-200 text-slate-700 text-xs font-bold rounded-lg focus:ring-blue-500 focus:border-blue-500 shadow-sm appearance-none"
                        placeholder="Pilih Tanggal & Jam">
                </div>
            </div>
        </div>
    </div>
</header>