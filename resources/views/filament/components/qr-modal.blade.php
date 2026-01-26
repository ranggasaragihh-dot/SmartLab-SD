<div class="flex flex-col items-center justify-center p-4 space-y-4">
    
    <div class="text-center">
        <h3 class="text-xl font-bold text-gray-900">{{ $name }}</h3>
        <p class="text-sm text-gray-500">Scan QR ini menggunakan Google Lens</p>
    </div>

    <div class="p-6 bg-white border-2 border-gray-200 rounded-xl shadow-sm">
        {{-- 
           HAPUS IF/ELSE, LANGSUNG PAKSA TAMPILKAN.
           Jika error, berarti memang belum terinstall dengan benar.
        --}}
        {{-- Menggunakan Namespace Lengkap --}}
        {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(250)->generate($token) !!}
    </div>

    <div class="w-full text-center space-y-2">
        <p class="text-xs text-gray-400">Link Alternatif:</p>
        <div class="bg-gray-100 p-2 rounded text-xs text-gray-600 font-mono break-all select-all border border-gray-200">
            {{ $token }}
        </div>
    </div>

    <button type="button" onclick="window.print()" class="mt-4 px-4 py-2 bg-gray-800 text-white rounded-lg hover:bg-gray-700 text-sm font-medium transition">
        🖨️ Cetak
    </button>
</div>