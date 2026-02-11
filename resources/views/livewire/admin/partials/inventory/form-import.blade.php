<form id="importForm" wire:submit.prevent="importExcel" class="animate-fade-in">
    
    {{-- Info Box (Yellow) --}}
    <div class="mb-6 p-5 bg-amber-50 border border-amber-100 rounded-xl flex gap-4">
        <div class="shrink-0 text-amber-500">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <div>
            <h4 class="font-bold text-amber-800 text-sm mb-2">Panduan Format File</h4>
            <ul class="text-xs text-amber-700 space-y-1.5 list-disc pl-4">
                <li>Format wajib: .xlsx atau .csv</li>
                <li>Header: <strong>kode_asset, merk, kategori, posisi_meja, spec_cpu, spec_ram</strong></li>
                <li>Maksimal ukuran file <strong>5MB</strong>.</li>
            </ul>
        </div>
    </div>

    {{-- Area Upload --}}
    <div class="space-y-4">
        
        
            {{-- Dropzone --}}
            <div class="relative group">
                <label for="dropzone-file" class="flex flex-col items-center justify-center w-full h-48 border-2 border-slate-300 border-dashed rounded-2xl cursor-pointer bg-white group-hover:bg-slate-50 group-hover:border-blue-400 transition-all">
                    <div class="flex flex-col items-center justify-center pt-5 pb-6">
                        <div class="w-12 h-12 bg-slate-100 rounded-full flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                            <svg class="w-6 h-6 text-slate-400 group-hover:text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                        </div>
                        <p class="text-sm text-slate-600 font-medium mb-1"><span class="text-slate-900 font-bold hover:underline">Klik untuk upload</span> atau drag file ke sini</p>
                        <p class="text-xs text-slate-400">XLSX, CSV (Max. 5MB)</p>
                    </div>
                    <input id="dropzone-file" type="file" wire:model="excelFile" class="hidden" accept=".xlsx, .csv" />
                </label>
            </div>

        @if($excelFile)
            {{-- File Success (Green Box) --}}
            <div class="bg-emerald-50 border border-emerald-100 rounded-xl p-4 flex items-center justify-between animate-fade-in-up">
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 bg-white rounded-lg border border-emerald-100 flex items-center justify-center shadow-sm shrink-0">
                        <svg class="w-5 h-5 text-emerald-600" fill="currentColor" viewBox="0 0 24 24"><path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zM6 20V4h7v5h5v11H6z"/></svg>
                    </div>
                    <div>
                        <h5 class="text-sm font-bold text-emerald-900 truncate max-w-[200px]">
                            {{ $excelFile->getClientOriginalName() }}
                        </h5>
                        <p class="text-xs text-emerald-600 font-medium">Siap untuk diimport</p>
                    </div>
                </div>
                <button type="button" wire:click="$set('excelFile', null)" class="text-emerald-400 hover:text-emerald-700 p-2 hover:bg-emerald-100 rounded-lg transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        @endif

        @error('excelFile') 
            <div class="p-3 bg-red-50 border border-red-100 rounded-lg text-xs text-red-600 font-bold flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                {{ $message }}
            </div>
        @enderror
    </div>
</form>