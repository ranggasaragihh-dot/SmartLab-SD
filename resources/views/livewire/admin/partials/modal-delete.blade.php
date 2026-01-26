@if($showDeleteModal)
<div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 backdrop-blur-sm p-4">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-sm p-6 text-center">
        <h3 class="text-lg font-bold mb-2">Hapus Data Ini?</h3>
        <p class="text-gray-500 text-sm mb-6">Tindakan ini tidak dapat dibatalkan.</p>
        <div class="flex justify-center gap-3">
            <button wire:click="closeModal" class="px-4 py-2 bg-gray-100 rounded-lg">Batal</button>
            <button wire:click="destroy" class="px-4 py-2 bg-red-600 text-white rounded-lg">Hapus</button>
        </div>
    </div>
</div>
@endif