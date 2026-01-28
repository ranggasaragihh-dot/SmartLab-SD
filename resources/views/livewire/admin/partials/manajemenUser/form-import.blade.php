<div class="text-center">
    {{-- Info Box --}}
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6 text-left">
        <h4 class="text-sm font-bold text-blue-800 mb-1">Panduan Import:</h4>
        <ul class="text-sm text-blue-700 list-disc list-inside space-y-1">
            <li>Gunakan file <strong>.xlsx</strong> atau <strong>.csv</strong>.</li>
            <li>Header Wajib: <code>nama_lengkap</code>, <code>email</code>, <code>nomor_induk</code>.</li>
            <li>Opsi Lain: <code>ambil_ta</code> (Isi "Ya" atau "Tidak").</li>
        </ul>
    </div>

    {{-- File Input --}}
    <label class="block w-full cursor-pointer bg-gray-50 border-2 border-dashed border-gray-300 rounded-xl p-8 hover:bg-indigo-50 hover:border-indigo-300 transition-all group">
        <div class="flex flex-col items-center justify-center pt-5 pb-6">
            <svg class="w-10 h-10 mb-3 text-gray-400 group-hover:text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
            <p class="mb-2 text-sm text-gray-500"><span class="font-semibold">Klik untuk upload</span></p>
            <p class="text-xs text-gray-500">XLSX, CSV (MAX. 2MB)</p>
        </div>
        <input type="file" wire:model="importFile" class="hidden">
    </label>

    {{-- File Feedback --}}
    <div class="mt-4 h-6">
        @if($importFile)
            <span class="text-sm font-bold text-green-600 flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                {{ $importFile->getClientOriginalName() }}
            </span>
        @endif
        @error('importFile') <span class="text-sm text-red-500 font-bold">{{ $message }}</span> @enderror
    </div>

    {{-- FOOTER BUTTONS --}}
    <div class="mt-6 flex flex-row-reverse gap-2">
        <button 
            wire:click="import" 
            wire:loading.attr="disabled" 
            {{ !$importFile ? 'disabled' : '' }}
            class="inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none disabled:opacity-50 disabled:cursor-not-allowed sm:text-sm">
            <span wire:loading.remove wire:target="import">Proses Import</span>
            <span wire:loading wire:target="import">Mengupload...</span>
        </button>
        <button type="button" wire:click="closeModal" class="inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:text-sm">
            Batal
        </button>
    </div>
</div>