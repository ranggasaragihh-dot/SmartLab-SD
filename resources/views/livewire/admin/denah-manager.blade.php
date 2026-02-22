<div class="min-h-screen bg-gray-50 flex flex-col font-sans">
    <div class="bg-white border-b border-gray-200 px-8 py-8">
        <div class="max-w-7xl mx-auto">
            <h1 class="text-3xl font-bold text-gray-800 mb-2">Admin Layout Manager</h1>
            <p class="text-gray-500">Pilih laboratorium atau ruangan untuk mulai mengelola denah & posisi unit.</p>
        </div>
    </div>

    <div class="flex-1 p-8">
        <div class="max-w-7xl mx-auto grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            
            @foreach($clusters as $cluster)
                <a href="{{ route('admin.lab.manager', ['clusterId' => $cluster->id]) }}" 
                   class="group bg-white p-8 rounded-3xl border border-gray-100 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 relative overflow-hidden"
                >
                    <div class="absolute top-0 right-0 -mt-6 -mr-6 w-32 h-32 bg-blue-50 rounded-full transition-transform group-hover:scale-110 group-hover:bg-blue-100"></div>

                    <div class="relative z-10">
                        <div class="flex justify-between items-start mb-6">
                            <span class="px-3 py-1 bg-gray-100 text-gray-600 rounded-full text-xs font-bold border border-gray-200 group-hover:bg-white">
                                {{ $cluster->inventory()->count() ?? 0 }} Unit
                            </span>
                        </div>

                        <h3 class="text-xl font-bold text-gray-800 mb-2 group-hover:text-blue-700 transition-colors">
                            {{ $cluster->name }}
                        </h3>
                        <p class="text-sm text-gray-400 mb-6 line-clamp-2">
                            Kelola denah, posisi kursi, dan status maintenance untuk ruangan ini.
                        </p>
                        
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