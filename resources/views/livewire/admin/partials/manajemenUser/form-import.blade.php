<div class="space-y-6">
    <div class="bg-slate-50 border border-slate-200 rounded-xl p-5 flex gap-4">
        <div class="flex-shrink-0 mt-0.5">
            <div class="w-8 h-8 bg-white rounded-full flex items-center justify-center border border-slate-100 shadow-sm text-amber-500">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
        </div>

        <div class="text-sm text-slate-600">
            <h4 class="font-bold text-slate-900 mb-2">Panduan Format File</h4>
            <ul class="list-disc list-inside space-y-1.5 marker:text-amber-500">
                <li>Format wajib: <span class="font-mono text-xs bg-white px-1.5 py-0.5 border border-slate-200 rounded text-slate-700">.xlsx</span> atau <span class="font-mono text-xs bg-white px-1.5 py-0.5 border border-slate-200 rounded text-slate-700">.csv</span></li>
                <li>Header kolom wajib: <code class="text-xs font-bold text-slate-800">nama_lengkap</code>, <code class="text-xs font-bold text-slate-800">email</code>, <code class="text-xs font-bold text-slate-800">nomor_induk</code></li>
                <li>Maksimal ukuran file <strong>2MB</strong>.</li>
            </ul>
        </div>
    </div>
    @if(!$importFile)
    <div 
        x-data="{ isDropping: false }"
        @dragover.prevent="isDropping = true"
        @dragleave.prevent="isDropping = false"
        @drop.prevent="isDropping = false"
        class="relative group"
    >
        <label class="flex flex-col items-center justify-center w-full h-52 border-2 border-dashed rounded-2xl cursor-pointer transition-all duration-300 ease-in-out"
            :class="isDropping ? 'border-amber-500 bg-amber-50' : 'border-slate-300 bg-white hover:bg-slate-50 hover:border-amber-400'">
            
            <div class="flex flex-col items-center justify-center pt-5 pb-6 text-center px-4">
                <div class="w-14 h-14 mb-4 rounded-full bg-slate-50 flex items-center justify-center transition-colors duration-300"
                     :class="isDropping ? 'text-amber-600 bg-amber-100' : 'text-slate-400 group-hover:text-amber-500 group-hover:bg-white group-hover:shadow-sm'">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                </div>
                
                <p class="mb-2 text-sm text-slate-600">
                    <span class="font-bold text-slate-900 group-hover:text-amber-600 transition-colors">Klik untuk upload</span> atau drag file ke sini
                </p>
                <p class="text-xs text-slate-400">XLSX, CSV (Max. 2MB)</p>
            </div>

            <input type="file" wire:model="importFile" class="hidden" accept=".xlsx, .csv">
        </label>

        <div wire:loading.flex wire:target="importFile" class="absolute inset-0 bg-white/80 backdrop-blur-[1px] rounded-2xl flex flex-col items-center justify-center z-10">
            <svg class="animate-spin h-8 w-8 text-amber-500 mb-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
            <p class="text-xs font-bold text-slate-600 animate-pulse">Memproses file...</p>
        </div>
    </div>

    @else
        <div class="animate-fade-in-up bg-emerald-50 border border-emerald-100 rounded-lg p-3 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-white rounded-md border border-emerald-100 text-emerald-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                </div>
                <div>
                    <p class="text-sm font-bold text-emerald-900 truncate max-w-[200px]">
                        {{ $importFile->getClientOriginalName() }}
                    </p>
                    <p class="text-xs text-emerald-700">Siap untuk diimport</p>
                </div>
            </div>
            
            <button wire:click="$set('importFile', null)" class="text-emerald-400 hover:text-red-500 transition-colors p-1" title="Batalkan File">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
    @endif

    @error('importFile') 
        <div class="bg-red-50 text-red-600 px-4 py-3 rounded-lg text-sm flex items-center gap-2 border border-red-100 animate-shake">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <span class="font-bold">{{ $message }}</span>
        </div>
    @enderror

    <div class="mt-6 flex flex-row-reverse gap-3 pt-6 border-t border-slate-100">
        <button 
            wire:click="import" 
            wire:loading.attr="disabled" 
            {{ !$importFile ? 'disabled' : '' }}
            class="inline-flex justify-center items-center gap-2 rounded-lg text-sm font-bold text-white px-5 py-2.5 bg-slate-900 hover:bg-slate-800 shadow-lg shadow-slate-900/10 hover:shadow-slate-900/20 transition-all disabled:opacity-50 disabled:cursor-not-allowed disabled:shadow-none"
        >
            <span wire:loading.remove wire:target="import">Mulai Import</span>
            <span wire:loading wire:target="import" class="flex items-center gap-2">
                Mengupload...
            </span>
        </button>

        <button type="button" wire:click="closeModal" class="inline-flex justify-center rounded-lg border border-slate-200 px-5 py-2.5 bg-white text-sm font-bold text-slate-600 hover:bg-slate-50 hover:text-slate-800 transition-colors">
            Batal
        </button>
    </div>

</div>