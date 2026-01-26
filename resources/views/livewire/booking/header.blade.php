<header class="h-20 bg-white border-b border-gray-100 flex items-center justify-between px-8 shadow-sm z-10 flex-shrink-0">
    <button @click="$dispatch('toggle-sidebar')" class="md:hidden text-gray-500 focus:outline-none">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
    </button>
    <div class="">
        <h1 class="text-2xl font-bold text-amber-500">Booking</h1>
        <p class="text-xs text-gray-400">Pilih kursi pada denah di bawah.</p>
    </div>
    <div class="flex items-center gap-2 bg-gray-50 border border-gray-200 rounded-lg p-1.5">
        <input type="date" wire:model.live="filterDate" class="bg-transparent border-none text-sm text-gray-700 font-bold focus:ring-0 cursor-pointer">
    </div>
</header>