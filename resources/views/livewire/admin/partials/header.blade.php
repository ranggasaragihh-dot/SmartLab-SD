{{-- File: resources/views/livewire/admin/partials/header.blade.php --}}

<div class="space-y-4 mb-6 font-sans">
    
    {{-- BARIS UTAMA: Judul (Kiri) & Kontrol (Kanan) --}}
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        
        {{-- BAGIAN KIRI: Judul & Subjudul --}}
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">{{ $title }}</h1>
            <p class="text-sm text-slate-500 mt-1">{{ $subtitle }}</p>
        </div>
        
        {{-- BAGIAN KANAN: Search Bar & Tombol --}}
        <div class="flex flex-col sm:flex-row gap-3 w-full lg:w-auto">
            
            {{-- LOGIKA SEARCH BAR: Hanya muncul jika variabel 'searchable' dikirim --}}
            @if(isset($searchable) && $searchable)
                <div class="relative w-full sm:w-64">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        {{-- Icon Search --}}
                        <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input 
                        wire:model.live="search" 
                        type="text" 
                        class="w-full pl-9 pr-4 py-2 bg-white border border-slate-300 rounded-lg text-sm shadow-sm placeholder-slate-400 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition-all" 
                        placeholder="{{ $searchPlaceholder ?? 'Cari data...' }}"
                    >
                </div>
            @endif

            {{-- TOMBOL UTAMA (Warna Hitam/Slate-900) --}}
            <button 
                wire:click="{{ $action }}" 
                class="inline-flex items-center justify-center px-4 py-2 text-sm font-bold text-white bg-slate-900 rounded-lg hover:bg-slate-800 shadow-md transition-all whitespace-nowrap gap-2 focus:ring-4 focus:ring-slate-200"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.5v15m7.5-7.5h-15"></path></svg>
                {{ $button }}
            </button>

        </div>
    </div>

    {{-- FLASH MESSAGE (Alert Modern) --}}
    @if (session()->has('message'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)" 
             class="flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-lg shadow-sm animate-fade-in-down">
            <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            <span class="text-sm font-medium">{{ session('message') }}</span>
        </div>
    @endif

    @if (session()->has('error'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)" 
             class="flex items-center gap-3 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg shadow-sm animate-fade-in-down">
            <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
            <span class="text-sm font-medium">{{ session('error') }}</span>
        </div>
    @endif

</div>