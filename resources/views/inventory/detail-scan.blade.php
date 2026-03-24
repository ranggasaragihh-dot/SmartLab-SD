<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sertifikat Aset - LAB SD</title>
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    {{-- <link href="{{ asset('css/app.css') }}" rel="stylesheet"> --}}
</head>
<body class="antialiased bg-slate-50">

    <div class="max-w-md mx-auto min-h-screen bg-slate-50 p-4 sm:p-6 flex flex-col justify-center font-sans relative">
         <div class="max-w-md mx-auto min-h-screen bg-slate-50 p-4 sm:p-6 flex flex-col justify-center font-sans">
    
            {{-- KARTU INFORMASI UTAMA --}}
            <div class="bg-white rounded-2xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-100 overflow-hidden">
                <div class="bg-slate-900 p-6 text-center relative overflow-hidden">
                    {{-- Aksen Latar Belakang --}}
                    <div class="absolute -top-10 -right-10 w-32 h-32 bg-slate-800 rounded-full blur-2xl opacity-50"></div>
                    
                    <div class="relative z-10">
                        <div class="w-14 h-14 bg-slate-800 rounded-xl flex items-center justify-center mx-auto mb-4 border border-slate-700 shadow-inner">
                            <svg class="w-7 h-7 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        </div>
                        <h1 class="text-xl font-black text-white leading-tight mb-2">{{ $item->item_name }}</h1>
                        <span class="inline-block px-3 py-1 bg-slate-800 text-amber-400 rounded-md text-[10px] tracking-widest font-bold uppercase border border-slate-700 shadow-sm">
                            {{ $item->asset_code }}
                        </span>
                    </div>
                </div>

                {{-- Status & Lokasi --}}
                <div class="grid grid-cols-2 divide-x divide-slate-100 border-b border-slate-100 bg-slate-50/50">
                    <div class="p-4 flex flex-col items-center justify-center">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Status Fisik</span>
                        @php
                            $cond = strtolower($item->condition);
                            $badgeStyle = match(true) {
                                in_array($cond, ['good', 'baik']) => 'text-emerald-700 bg-emerald-100 border-emerald-200',
                                in_array($cond, ['broken', 'rusak']) => 'text-red-700 bg-red-100 border-red-200',
                                in_array($cond, ['maintenance', 'perbaikan']) => 'text-amber-700 bg-amber-100 border-amber-200',
                                default => 'text-slate-700 bg-slate-100 border-slate-200'
                            };
                        @endphp
                        <span class="inline-flex items-center justify-center rounded-full px-3 py-1 text-xs font-black border {{ $badgeStyle }} uppercase tracking-wide">
                            {{ $item->condition }}
                        </span>
                    </div>
                    
                    <div class="p-4 flex flex-col items-center justify-center text-center">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Lokasi Saat Ini</span>
                        <span class="text-sm font-bold text-slate-800 leading-tight">
                            {{ $item->inventory->name ?? 'Gudang Utama' }}
                        </span>
                    </div>
                </div>

                <div class="p-6 space-y-4">
                    <div class="flex justify-between items-center pb-3 border-b border-slate-50 border-dashed">
                        <span class="text-xs font-bold text-slate-500 flex items-center gap-2">
                            <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                            Kategori
                        </span>
                        <span class="text-sm font-bold text-slate-900 text-right">{{ $item->category->name }}</span>
                    </div>
                    
                    <div class="flex justify-between items-center">
                        <span class="text-xs font-bold text-slate-500 flex items-center gap-2">
                            <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/></svg>
                            Merk
                        </span>
                        <span class="text-sm font-bold text-slate-900 text-right">{{ $item->brand->name }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>