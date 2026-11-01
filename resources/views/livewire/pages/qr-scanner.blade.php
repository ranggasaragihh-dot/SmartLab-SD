<div class="min-h-[100dvh] bg-slate-50">
    <header class="border-b border-slate-200 bg-white">
        <div class="mx-auto flex max-w-5xl items-center justify-between px-4 pt-[calc(env(safe-area-inset-top,0px)+1rem)] pb-4 sm:px-6">
            <div>
                <h1 class="text-xl font-black text-[#1f2b3d]">QR Check-in</h1>
                <p class="mt-0.5 text-xs font-medium text-slate-500">LAB SD</p>
            </div>
            <a href="{{ route('booking.index') }}" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-600 shadow-sm transition hover:bg-slate-50 hover:text-slate-900">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                Booking
            </a>
        </div>
    </header>

    <main x-data="qrScanner()" class="mx-auto w-full max-w-lg px-4 py-6 sm:py-10">
        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-5 py-4">
                <h2 class="text-base font-bold text-slate-800">Pemindaian QR</h2>
            </div>

            <div class="p-4 sm:p-5">
                <div class="relative aspect-square overflow-hidden rounded-xl bg-slate-950">
                    <div id="qr-camera" class="h-full w-full"></div>
                    <div x-show="!isScanning && !isStarting" class="pointer-events-none absolute inset-0 flex flex-col items-center justify-center gap-3 text-slate-400">
                        <svg class="h-12 w-12" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4h5v5H4zM15 4h5v5h-5zM4 15h5v5H4zM15 15h2v2h-2zM19 15v2m-4 3h2m2-3h1v3h-3"/></svg>
                        <span class="text-sm font-semibold">Kamera belum aktif</span>
                    </div>
                    <div x-show="isScanning" class="pointer-events-none absolute inset-0 flex items-center justify-center">
                        <div class="h-3/5 w-3/5 rounded-2xl border-2 border-amber-400/90 shadow-[0_0_0_999px_rgba(15,23,42,0.18)]"></div>
                    </div>
                </div>

                <p x-show="feedback" x-cloak role="status" x-text="feedback" :class="feedbackType === 'error' ? 'border-red-200 bg-red-50 text-red-700' : 'border-slate-200 bg-slate-50 text-slate-600'" class="mt-3 rounded-lg border px-3 py-2 text-sm font-medium"></p>

                <button type="button" @click="isScanning ? stop() : start()" :disabled="isStarting || isSubmitting" class="mt-4 flex w-full items-center justify-center gap-2 rounded-lg bg-[#f4a62d] px-4 py-3 text-sm font-bold text-white transition hover:bg-[#e6981c] disabled:cursor-wait disabled:opacity-60">
                    <svg x-show="!isScanning" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 7V5a1 1 0 011-1h2m10 0h2a1 1 0 011 1v2m0 10v2a1 1 0 01-1 1h-2M7 20H5a1 1 0 01-1-1v-2m4-5h8"/></svg>
                    <svg x-show="isScanning" x-cloak class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 6l12 12M18 6L6 18"/></svg>
                    <span x-show="isStarting">Membuka kamera...</span>
                    <span x-show="!isStarting && !isScanning">Mulai pemindaian</span>
                    <span x-show="!isStarting && isScanning">Matikan kamera</span>
                </button>
            </div>
        </section>
    </main>
</div>