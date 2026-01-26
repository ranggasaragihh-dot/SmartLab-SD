<div class="min-h-screen bg-gray-50 flex flex-col font-sans">
    
    {{-- HEADER --}}
    <div class="bg-white border-b border-gray-200 px-8 py-8">
        <div class="max-w-7xl mx-auto">
            <h1 class="text-3xl font-bold text-gray-800 mb-2">Admin Layout Manager</h1>
            <p class="text-gray-500">Pilih laboratorium atau ruangan untuk mulai mengelola denah & posisi unit.</p>
        </div>
    </div>

    {{-- CONTENT GRID --}}
    <div class="flex-1 p-8">
        <div class="max-w-7xl mx-auto grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            
            @foreach($clusters as $cluster)
                {{-- 
                    PERHATIKAN: 
                    Ganti 'admin.denah.editor' dengan nama route halaman denah Anda yang terpisah itu.
                    Kita kirim parameter ID cluster.
                --}}
                <a href="{{ route('admin.lab.manager', ['clusterId' => $cluster->id]) }}" 
                   class="group bg-white p-8 rounded-3xl border border-gray-100 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 relative overflow-hidden"
                >
                    {{-- Hiasan Background (Lingkaran halus) --}}
                    <div class="absolute top-0 right-0 -mt-6 -mr-6 w-32 h-32 bg-blue-50 rounded-full transition-transform group-hover:scale-110 group-hover:bg-blue-100"></div>

                    <div class="relative z-10">
                        {{-- Header Kartu --}}
                        <div class="flex justify-between items-start mb-6">
                            {{-- Icon Lab --}}
                            <!-- <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-blue-700 text-white rounded-2xl flex items-center justify-center shadow-lg shadow-blue-200 group-hover:shadow-blue-300 transition-shadow">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                            </div> -->

                            {{-- Badge Jumlah Unit --}}
                            <span class="px-3 py-1 bg-gray-100 text-gray-600 rounded-full text-xs font-bold border border-gray-200 group-hover:bg-white">
                                {{ $cluster->inventory()->count() ?? 0 }} Unit
                            </span>
                        </div>

                        {{-- Info Text --}}
                        <h3 class="text-xl font-bold text-gray-800 mb-2 group-hover:text-blue-700 transition-colors">
                            {{ $cluster->name }}
                        </h3>
                        <p class="text-sm text-gray-400 mb-6 line-clamp-2">
                            Kelola denah, posisi kursi, dan status maintenance untuk ruangan ini.
                        </p>
                        
                        {{-- Tombol Fake (Visual Saja) --}}
                        <div class="flex items-center text-blue-600 text-sm font-bold group-hover:underline decoration-2 underline-offset-4">
                            Buka Layout Manager 
                            <svg class="w-4 h-4 ml-2 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                        </div>
                    </div>
                </a>
            @endforeach

        </div>
    </div>
</div>