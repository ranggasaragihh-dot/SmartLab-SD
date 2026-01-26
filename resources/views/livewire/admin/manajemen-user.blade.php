<div class="p-6 bg-white rounded-lg shadow">
    <h2 class="text-xl font-bold mb-4">Manajemen User</h2>

    @if (session()->has('message'))
        <div class="bg-green-100 text-green-700 p-3 rounded mb-4">{{ session('message') }}</div>
    @endif
    @if (session()->has('error'))
        <div class="bg-red-100 text-red-700 p-3 rounded mb-4">{{ session('error') }}</div>
    @endif

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nomor Induk</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($users as $user)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 h-10 w-10">
                                @if($user->photo)
                                    <img class="h-10 w-10 rounded-full object-cover" src="{{ asset('storage/'.$user->photo) }}">
                                @else
                                    <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center text-gray-600 font-bold">
                                        {{ substr($user->name, 0, 1) }}
                                    </div>
                                @endif
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
                                <div class="text-sm text-gray-500">{{ $user->email }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex flex-wrap gap-1"> 
                            @forelse($user->roles as $role)
                                @php
                                    // 1. Ambil String Value (Aman dari Enum maupun String biasa)
                                    // Cek: Apakah ini object Enum? Jika ya ambil ->value. Jika tidak, ambil string langsung.
                                    $roleName = ($role->name instanceof \BackedEnum) ? $role->name->value : $role->name;

                                    // 2. Tentukan Warna per Role
                                    $badgeClass = match($roleName) {
                                        'admin' => 'bg-purple-100 text-purple-800 border border-purple-200',
                                        'dosen' => 'bg-blue-100 text-blue-800 border border-blue-200',
                                        'mahasiswa' => 'bg-green-100 text-green-800 border border-green-200',
                                        default => 'bg-gray-100 text-gray-800 border border-gray-200',
                                    };
                                @endphp

                                <span class="px-2 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full {{ $badgeClass }}">
                                    {{ ucfirst($roleName) }}
                                </span>

                            @empty
                                <span class="px-2 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-50 text-red-600 border border-red-200">
                                    No Role
                                </span>
                            @endforelse

                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $user->nomor_induk ?? '-' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <button 
                            wire:click="delete({{ $user->id }})"
                            wire:confirm="Yakin ingin menghapus user {{ $user->name }}? Data tidak bisa dikembalikan."
                            class="text-red-600 hover:text-red-900 font-bold">
                            Hapus
                        </button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $users->links() }}
    </div>
</div>