<div class="flex min-h-[100dvh] w-full bg-slate-50 font-sans md:h-[100dvh] md:overflow-hidden">

    @include('livewire.booking.sidebar')

    <main class="flex min-h-0 min-w-0 flex-1 flex-col bg-slate-50">
        
        @include('livewire.booking.header')

        <div class="flex min-h-0 flex-1 flex-col bg-slate-50 font-sans md:overflow-hidden">
            @include('livewire.booking.booking-list')
        </div>
        @include('layouts.footer')
    </main>
    @include('livewire.booking.modals')
</div>