<div class="font-sans text-slate-900">
    @include('livewire.admin.partials.header', [
        'title'             => 'Manajemen User',
        'subtitle'          => 'Total ' . $users->total() . ' pengguna terdaftar.',
        'button'            => 'Tambah User',
        'action'            => 'create',
        'searchable'        => true,
        'searchPlaceholder' => 'Cari Nama, NPM, atau Email...'
    ])

    <div class="bg-white rounded-xl shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)] border border-slate-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-slate-50 border-b border-slate-100 text-xs uppercase font-bold text-slate-500 tracking-wider">
                    <tr>
                        <th class="px-6 py-4">Mahasiswa</th>
                        <th class="px-6 py-4">NPM / ID</th>
                        <th class="px-6 py-4 text-center">Role</th>
                        <th class="px-6 py-4 text-center">Status TA</th>
                        <th class="px-6 py-4 text-right">Aksi</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-50">
                    @forelse($users as $user)
                        <tr class="hover:bg-slate-50/80 transition-colors group">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="flex-shrink-0 w-9 h-9 rounded-full bg-slate-900 text-white flex items-center justify-center text-xs font-bold ring-2 ring-white shadow-sm">
                                        {{ substr($user->name, 0, 2) }}
                                    </div>
                                    <div class="min-w-0">
                                        <div class="font-bold text-slate-800 truncate">
                                            {{ $user->name }}
                                            @if($user->is_banned)
                                                <span class="ml-1 px-1.5 py-0.5 text-[10px] font-bold uppercase rounded bg-red-50 text-red-600 border border-red-100">Nonaktif</span>
                                            @endif
                                        </div>
                                        <div class="text-xs text-slate-500 truncate">{{ $user->email }}</div>
                                    </div>
                                </div>
                            </td>

                            <td class="px-6 py-4">
                                <span class="font-mono text-xs font-semibold text-slate-600 bg-slate-100 px-2 py-1 rounded border border-slate-200">
                                    {{ $user->nomor_induk ?? '-' }}
                                </span>
                            </td>

                            <td class="px-6 py-4 text-center">
                                <div class="inline-flex justify-center">
                                    @forelse($user->role_badges as $badge)
                                        <span class="px-2 py-0.5 text-[10px] font-bold uppercase rounded-full bg-blue-50 text-blue-600 border border-blue-100">
                                            {{ $badge['label'] }}
                                        </span>
                                    @empty
                                        <span class="text-slate-300 text-xs">-</span>
                                    @endforelse
                                </div>
                            </td>

                            <td class="px-6 py-4 text-center">
                                @if($user->ta == 1)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                        Sedang TA
                                    </span>
                                @else
                                    <span class="text-xs text-slate-400 font-medium">Belum</span>
                                @endif
                            </td>

                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button wire:click="edit({{ $user->id }})" class="p-1.5 text-slate-400 hover:text-amber-600 hover:bg-amber-50 rounded-md transition-all">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                    </button>
                                        
                                    @if($user->id !== auth()->id())
                                        <button wire:click="toggleActive({{ $user->id }})"
                                            wire:confirm="{{ $user->is_banned ? 'Aktifkan kembali akun ' . $user->name . '?' : 'Nonaktifkan akun ' . $user->name . '? User tidak akan bisa login dan booking yang belum dimulai dibatalkan.' }}"
                                            title="{{ $user->is_banned ? 'Aktifkan akun' : 'Nonaktifkan akun' }}"
                                            class="px-2 py-1 text-[10px] font-bold rounded-md border transition-all {{ $user->is_banned ? 'text-emerald-600 border-emerald-200 hover:bg-emerald-50' : 'text-slate-500 border-slate-200 hover:text-red-600 hover:bg-red-50' }}">
                                            {{ $user->is_banned ? 'Aktifkan' : 'Nonaktifkan' }}
                                        </button>
                                        <button wire:click="confirmDelete({{ $user->id }})" class="p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-md transition-all">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-slate-500">
                                Tidak ada data ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
            
        <div class="px-6 py-4 border-t border-slate-100 bg-slate-50">
            {{ $users->links() }}
        </div>
    </div>
        @include('livewire.admin.partials.manajemenUser.modal')
</div>