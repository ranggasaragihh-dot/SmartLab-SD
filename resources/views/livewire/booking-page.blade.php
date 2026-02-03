<div class="flex h-screen w-full bg-slate-50 overflow-hidden font-sans">

    {{-- 1. SIDEBAR (TIDAK DIUBAH) --}}
    @include('livewire.booking.sidebar')

    {{-- 2. KONTEN UTAMA --}}
    <main class="flex-1 flex flex-col min-w-0 bg-slate-50">
        
        @include('livewire.booking.header')

        <div class="flex-1 flex flex-col h-full overflow-hidden bg-slate-50 font-sans">
            @include('livewire.booking.booking-list')
        </div>
        @include('layouts.footer')
    </main>
    @include('livewire.booking.modals')
</div>