<div class="space-y-6">
    @include('livewire.admin.partials.manajemenUser.header', [
        'title'    => 'Manajemen Cluster',
        'subtitle' => 'Kelola daftar lab dan aksesibilitas.',
        'button'   => 'Tambah Lab',
        'action'   => 'create' 
    ])

    {{-- Flash Message --}}
    @if (session()->has('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
            <p>{{ session('success') }}</p>
        </div>
    @endif
    @if (session()->has('error'))
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
            <p>{{ session('error') }}</p>
        </div>
    @endif

    {{-- Tabel Cluster --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-500">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 border-b">
                    <tr>
                        <th class="px-6 py-3">Nama Lab</th>
                        <th class="px-6 py-3">Tipe</th>
                        <th class="px-6 py-3">Config (Delay)</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($clusters as $cluster)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    @if($cluster->denah_image)
                                        <img class="h-10 w-10 rounded-lg object-cover mr-3 border" 
                                            src="{{ Storage::url($cluster->denah_image) }}" 
                                            alt="Denah">
                                    @else
                                        <div class="h-10 w-10 rounded-lg bg-gray-200 mr-3 flex items-center justify-center text-gray-400">
                                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                        </div>
                                    @endif
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">{{ $cluster->name }}</div>
                                        <div class="text-xs text-gray-500">{{ Str::limit($cluster->description, 30) }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $cluster->type === 'public' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800' }}">
                                    {{ ucfirst($cluster->type) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                {{ $cluster->delay_time ?? 15 }} Menit
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $cluster->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $cluster->is_active ? 'Active' : 'Maintenance' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center flex justify-center gap-2">
                                <div class="flex justify-end items-center gap-3">
                                    <button wire:click="edit({{ $cluster->id }})" class="text-indigo-600 hover:text-indigo-900 font-medium hover:underline transition">
                                        Edit
                                    </button>
                                    <span class="text-gray-300">|</span>
                                    <button
                                        wire:click="delete({{ $cluster->id  }})"
                                        wire:confirm="Yakin ingin menghapus {{ $cluster->name }}?"
                                        class="text-red-600 hover:text-red-900 font-medium hover:underline transition">
                                        Hapus
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-10 text-center text-gray-500 italic">
                                Belum ada data cluster. Silakan tambah baru.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- 4. MODAL FORM --}}
    @include('livewire.admin.partials.clusterManager.modal')
</div>