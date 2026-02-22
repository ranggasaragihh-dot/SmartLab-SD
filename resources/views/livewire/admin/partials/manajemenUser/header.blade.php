@props(['title', 'subtitle', 'button', 'action'])

<div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">{{ $title }}</h1>
        <p class="text-gray-500 text-sm">{{ $subtitle }}</p>
    </div>
    
    <button wire:click="create" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center gap-2 transition shadow-lg shadow-blue-500/30">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        {{ $button }}
    </button>
</div>

@if (session()->has('message'))
    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)" 
         class="mt-4 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded shadow-sm transition-all">
        {{ session('message') }}
    </div>
@endif