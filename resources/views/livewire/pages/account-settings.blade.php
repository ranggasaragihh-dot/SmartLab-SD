<div x-data="{ showCurrentPassword: false, showNewPassword: false, showPasswordConfirmation: false }" class="mx-auto w-full max-w-xl px-4 py-8 sm:px-0 sm:py-10">
    <div class="mb-5 flex items-start justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-[#1f2b3d]">Pengaturan Akun</h1>
            <p class="mt-1 text-sm text-slate-500">Ganti password akun Anda secara berkala.</p>
        </div>
        <a href="{{ Auth::user()->hasRole('admin') ? route('admin.dashboard') : route('booking.index') }}" class="inline-flex shrink-0 items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-600 shadow-sm transition hover:bg-slate-50 hover:text-slate-900">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
            Kembali
        </a>
    </div>

    <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
        <h2 class="text-base font-bold text-slate-800">Ganti Password</h2>
        <p class="mt-1 text-sm text-slate-500">Masukkan password saat ini, lalu buat password baru minimal 6 karakter.</p>

        @if (session('password-updated'))
            <div role="status" class="mt-4 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm font-medium text-emerald-700">
                {{ session('password-updated') }}
            </div>
        @endif

        <div role="group" aria-label="Form ganti password" class="mt-5 space-y-4">
            <div>
                <label for="current_password" class="mb-1.5 block text-sm font-semibold text-slate-700">Password saat ini</label>
                <div class="relative">
                    <input id="current_password" :type="showCurrentPassword ? 'text' : 'password'" wire:model="current_password" autocomplete="off" required class="w-full rounded-lg border-slate-300 py-2.5 pr-11 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500">
                    <button type="button" @click="showCurrentPassword = !showCurrentPassword" :aria-label="showCurrentPassword ? 'Sembunyikan password' : 'Tampilkan password'" :title="showCurrentPassword ? 'Sembunyikan password' : 'Tampilkan password'" class="absolute inset-y-0 right-0 flex w-10 items-center justify-center text-slate-400 transition hover:text-slate-700">
                        <svg x-show="!showCurrentPassword" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12s3.5-7 9.75-7 9.75 7 9.75 7-3.5 7-9.75 7-9.75-7-9.75-7z"/><circle cx="12" cy="12" r="3"/></svg>
                        <svg x-show="showCurrentPassword" x-cloak class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="m3 3 18 18M10.6 10.6a2 2 0 002.8 2.8M9.9 5.2A10.8 10.8 0 0112 5c6.25 0 9.75 7 9.75 7a15 15 0 01-3.1 3.8M6.2 6.2C3.6 7.9 2.25 12 2.25 12s3.5 7 9.75 7c1.3 0 2.5-.3 3.6-.8"/></svg>
                    </button>
                </div>
                @error('current_password') <p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="password" class="mb-1.5 block text-sm font-semibold text-slate-700">Password baru</label>
                <div class="relative">
                    <input id="password" :type="showNewPassword ? 'text' : 'password'" wire:model="password" autocomplete="off" minlength="6" required class="w-full rounded-lg border-slate-300 py-2.5 pr-11 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500">
                    <button type="button" @click="showNewPassword = !showNewPassword" :aria-label="showNewPassword ? 'Sembunyikan password' : 'Tampilkan password'" :title="showNewPassword ? 'Sembunyikan password' : 'Tampilkan password'" class="absolute inset-y-0 right-0 flex w-10 items-center justify-center text-slate-400 transition hover:text-slate-700">
                        <svg x-show="!showNewPassword" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12s3.5-7 9.75-7 9.75 7 9.75 7-3.5 7-9.75 7-9.75-7-9.75-7z"/><circle cx="12" cy="12" r="3"/></svg>
                        <svg x-show="showNewPassword" x-cloak class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="m3 3 18 18M10.6 10.6a2 2 0 002.8 2.8M9.9 5.2A10.8 10.8 0 0112 5c6.25 0 9.75 7 9.75 7a15 15 0 01-3.1 3.8M6.2 6.2C3.6 7.9 2.25 12 2.25 12s3.5 7 9.75 7c1.3 0 2.5-.3 3.6-.8"/></svg>
                    </button>
                </div>
                @error('password') <p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="password_confirmation" class="mb-1.5 block text-sm font-semibold text-slate-700">Konfirmasi password baru</label>
                <div class="relative">
                    <input id="password_confirmation" :type="showPasswordConfirmation ? 'text' : 'password'" wire:model="password_confirmation" autocomplete="off" minlength="6" required @keydown.enter.prevent="$wire.updatePassword()" class="w-full rounded-lg border-slate-300 py-2.5 pr-11 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500">
                    <button type="button" @click="showPasswordConfirmation = !showPasswordConfirmation" :aria-label="showPasswordConfirmation ? 'Sembunyikan password' : 'Tampilkan password'" :title="showPasswordConfirmation ? 'Sembunyikan password' : 'Tampilkan password'" class="absolute inset-y-0 right-0 flex w-10 items-center justify-center text-slate-400 transition hover:text-slate-700">
                        <svg x-show="!showPasswordConfirmation" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12s3.5-7 9.75-7 9.75 7 9.75 7-3.5 7-9.75 7-9.75-7-9.75-7z"/><circle cx="12" cy="12" r="3"/></svg>
                        <svg x-show="showPasswordConfirmation" x-cloak class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="m3 3 18 18M10.6 10.6a2 2 0 002.8 2.8M9.9 5.2A10.8 10.8 0 0112 5c6.25 0 9.75 7 9.75 7a15 15 0 01-3.1 3.8M6.2 6.2C3.6 7.9 2.25 12 2.25 12s3.5 7 9.75 7c1.3 0 2.5-.3 3.6-.8"/></svg>
                    </button>
                </div>
            </div>

            <div class="flex justify-end border-t border-slate-100 pt-4">
                <button type="button" wire:click="updatePassword" wire:loading.attr="disabled" class="inline-flex items-center rounded-lg bg-[#f4a62d] px-4 py-2 text-sm font-bold text-white transition hover:bg-[#e6981c] disabled:cursor-wait disabled:opacity-60">
                    <span wire:loading.remove wire:target="updatePassword">Simpan Password</span>
                    <span wire:loading wire:target="updatePassword">Menyimpan...</span>
                </button>
            </div>
        </div>
    </section>
</div>