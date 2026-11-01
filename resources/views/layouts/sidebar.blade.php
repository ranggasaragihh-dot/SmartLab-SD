<aside class="fixed inset-y-0 left-0 z-50 w-64 bg-slate-900 pt-[env(safe-area-inset-top,0px)] pb-[env(safe-area-inset-bottom,0px)] text-white transition-transform duration-300 transform md:relative md:translate-x-0 border-r border-slate-800 shadow-xl font-sans"
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">
                            
            <div class="flex flex-col h-full">
                <div class="h-20 flex items-center gap-3 px-6 border-b border-slate-800 bg-slate-900 relative shrink-0 z-20">
                    <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-amber-500 to-amber-300"></div>
                    <div class="w-10 h-10 bg-white rounded-lg flex items-center justify-center shadow-lg shadow-amber-500/10 flex-shrink-0 z-10">
                        <img 
                            src="{{ asset('images/logo-uajy.png') }}" 
                            alt="Logo" 
                            class="w-7 h-7 object-contain"
                        >
                    </div>

                    <div class="leading-tight z-10">
                        <h1 class="font-bold text-lg tracking-tight text-white">LAB SD</h1>
                        <div class="flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                            <p class="text-[10px] text-slate-300 uppercase tracking-widest font-bold">Administrator</p>
                        </div>
                    </div>
                </div>

        <nav class="flex-1 overflow-y-auto py-6 px-3 space-y-1 custom-scrollbar">
            @foreach($menus as $menu)   
                @if(isset($menu['header']))
                    <div class="mt-8 mb-2 px-3 flex items-center justify-between group">
                        <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest group-hover:text-slate-400 transition-colors">
                            {{ $menu['header'] }}
                        </span>
                        <div class="h-px bg-slate-800 flex-1 ml-3"></div>
                    </div>

                @else
                    @php
                        $isActive = request()->routeIs($menu['route']);
                    @endphp

                        <a href="{{ route($menu['route']) }}"
                    class="group flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-all duration-200 border-l-4 
                    {{ $isActive 
                            ? 'bg-slate-800 text-white border-amber-500 shadow-lg' 
                            : 'border-transparent text-slate-400 hover:bg-slate-800 hover:text-white'
                    }}">
                        <svg class="w-5 h-5 mr-3 transition-colors {{ $isActive ? 'text-amber-500' : 'text-slate-500 group-hover:text-slate-300' }}" 
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $menu['icon'] }}"></path>
                        </svg>
                        {{ $menu['label'] }}
                    </a>
                @endif
            @endforeach

        </nav>

        <div class="p-4 border-t border-slate-800 bg-slate-950/50 shrink-0">
            <form method="POST" action="{{ route('logout') }}" x-data>
                @csrf
                <a href="{{ route('logout') }}" 
                @click.prevent="$root.submit();" 
                class="group flex items-center justify-center w-full px-4 py-2 text-sm font-medium text-red-400 bg-slate-900 border border-slate-800 rounded-lg hover:bg-red-900/20 hover:text-red-300 hover:border-red-900/30 transition-all duration-200 shadow-sm">
                            
                    <svg class="w-4 h-4 mr-2 transition-transform group-hover:-translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                    </svg>
                    <span>Keluar Sistem</span>
                </a>
            </form>
        </div>

    </div>
</aside>