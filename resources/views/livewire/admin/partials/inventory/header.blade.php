<div class="p-4 border-b border-slate-200 bg-white z-20 flex flex-col sm:flex-row sm:items-center justify-between gap-4 shrink-0">
    
    {{-- KIRI: Judul & Deskripsi --}}
    <div>
        <h1 class="text-xl font-bold text-slate-900 tracking-tight flex items-center gap-2">
            <svg class="w-6 h-6 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
            Manajemen Aset Fisik
        </h1>
        <p class="text-sm text-slate-500 mt-1">Kelola data inventaris PC, laptop, dan perlengkapan lab.</p>
    </div>

    {{-- KANAN: Search & Tombol Aksi --}}
    <div class="flex items-center gap-2">
        {{-- Search Bar --}}
        <div class="relative group">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="w-4 h-4 text-slate-400 group-focus-within:text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>
            <input type="text" wire:model.live="search" placeholder="Cari Aset..." class="pl-9 pr-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm text-slate-700 focus:ring-2 focus:ring-blue-500 transition-all w-48 sm:w-64">
        </div>

        {{-- TOMBOL UTAMA (Trigger Modal) --}}
        <button wire:click="create" class="px-4 py-2 bg-slate-900 text-white rounded-lg text-sm font-bold hover:bg-slate-800 shadow-md shadow-slate-200 transition-all flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
            Tambah Inventaris
        </button>
    </div>
</div>