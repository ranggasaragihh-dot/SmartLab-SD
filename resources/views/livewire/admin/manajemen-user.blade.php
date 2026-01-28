<div class="space-y-6">

    {{-- 2. HEADER PAGE --}}
    {{-- Pastikan file ada di: resources/views/livewire/admin/partials/header.blade.php --}}
    @include('livewire.admin.partials.manajemenUser.header', [
        'title'    => 'Manajemen User',
        'subtitle' => 'Daftar semua pengguna yang terdaftar dalam sistem.',
        'button'   => 'Tambah User',
        'action'   => 'create' 
    ])

    {{-- ALERTS (Jika ada) --}}
    @if (session()->has('message') || session()->has('error'))
    <div class="px-6 pt-4">
        @if (session()->has('message'))
            <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded-r-md">
                <div class="flex">
                    <div class="flex-shrink-0"><i class="fas fa-check-circle text-green-500"></i></div>
                    <div class="ml-3 text-sm font-medium text-green-700">{{ session('message') }}</div>
                </div>
            </div>
        @endif
        @if (session()->has('error'))
            <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-r-md">
                <div class="flex">
                    <div class="flex-shrink-0"><i class="fas fa-exclamation-circle text-red-500"></i></div>
                    <div class="ml-3 text-sm font-medium text-red-700">{{ session('error') }}</div>
                </div>
            </div>
        @endif
    </div>
    @endif

    {{-- TABLE SECTION: Full Width (Tanpa Padding Samping) --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-500">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 border-b">
                    <tr>
                        {{-- Padding th disesuaikan agar teks tidak mepet dinding --}}
                        <th class="px-6 py-3">User Profile</th>
                        <th class="px-6 py-3">Role</th>
                        <th class="px-6 py-3">Nomor Induk</th>
                        <th class="px-6 py-3">Tugas Akhir</th>
                        <th class="px-6 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($users as $user)
                    <tr class="bg-white border-b hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 font-medium text-gray-900">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10">
                                    @if($user->photo)
                                        <img class="h-10 w-10 rounded-full object-cover border border-gray-200" src="{{ asset('storage/'.$user->photo) }}" alt="{{ $user->name }}">
                                    @else
                                        <div class="h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 font-bold border border-indigo-200 text-sm">
                                            {{ substr($user->name, 0, 1) }}
                                        </div>
                                    @endif
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-semibold text-gray-900">{{ $user->name }}</div>
                                    <div class="text-sm text-gray-500">{{ $user->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 font-medium text-gray-900">
                            <div class="flex flex-wrap gap-1">
                                @forelse($user->role_badges as $badge)
                                    <span class="px-2.5 py-0.5 inline-flex text-xs font-medium rounded-full {{ $badge['class'] }} border border-opacity-20">
                                        {{ $badge['label'] }}
                                    </span>
                                @empty
                                    <span class="text-gray-400 text-sm italic">No Role</span>
                                @endforelse
                            </div>
                        </td>
                        <td class="px-6 py-4 font-medium text-gray-900">
                            {{ $user->nomor_induk ?? '-' }}
                        </td>
                        <td class="px-6 py-4 font-medium text-gray-900">
                            {{ $user->ta ?? '-' }}
                        </td>
                        <td class="px-6 py-4 text-center flex justify-center gap-2">
                            <div class="flex justify-end items-center gap-3">
                                <button wire:click="edit({{ $user->id }})" class="text-indigo-600 hover:text-indigo-900 font-medium hover:underline transition">
                                    Edit
                                </button>
                                <span class="text-gray-300">|</span>
                                @if($user->id === auth()->id())
                                    <span class="text-gray-400 cursor-not-allowed" title="Akun Sendiri">Hapus</span>
                                @else
                                    <button
                                        wire:click="delete({{ $user->id }})"
                                        wire:confirm="Yakin ingin menghapus {{ $user->name }}?"
                                        class="text-red-600 hover:text-red-900 font-medium hover:underline transition">
                                        Hapus
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- FOOTER PAGINATION --}}
    <div class="bg-white px-6 py-4 border-t border-gray-200">
        {{ $users->links() }}
    </div>
    
    @include('livewire.admin.partials.manajemenUser.modal')

</div>