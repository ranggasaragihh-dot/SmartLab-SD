<?php

namespace App\Imports;

use App\Models\InventoryItem;
use App\Models\Inventory;
use App\Models\InventoryBrand;
use App\Models\InventoryCategory;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow; // Wajib import ini
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class InventoryItemsImport implements ToModel, WithStartRow
{
    /**
     * MULAI DARI BARIS KE-2 (Supaya Header tidak ikut dibaca)
     */
    public function startRow(): int
    {
        return 2;
    }

    public function model(array $row)
    {
        // === PENCEGAHAN ERROR (Header Trap) ===
        // Kadang Excel punya baris kosong atau header ganda.
        // Kita cek: Jika kolom Harga isinya teks "Harga Beli:", SKIP baris ini.
        $cekHeader = strtolower($row[6] ?? '');
        if (str_contains($cekHeader, 'harga')) {
            return null; // Lewati baris ini
        }
        
        // Jika Kode Aset kosong, juga SKIP
        if (empty($row[0])) {
            return null;
        }

        // === 1. AMBIL DATA ===
        // Hapus tanda titik dua ":" jika terbawa dari Excel (Misal "Merk:")
        $kodeAsset   = $this->cleanText($row[0]);
        $namaBarang  = $this->cleanText($row[1]); 
        $merkName    = $this->cleanText($row[2] ?? 'Tanpa Merk');
        $katName     = $this->cleanText($row[3] ?? 'Umum');
        
        // === 2. BERSIHKAN HARGA (PENTING!) ===
        // Mengubah "Rp 5.000.000" atau "5.000.000" menjadi "5000000" (Murni Angka)
        $rawPrice = $row[6] ?? 0;
        // Hapus semua karakter KECUALI angka
        $cleanPrice = preg_replace('/[^0-9]/', '', $rawPrice);
        if (empty($cleanPrice)) $cleanPrice = 0;

        // === 3. LOGIKA LOOKUP DATABASE ===
        $category = InventoryCategory::firstOrCreate(['name' => $katName]);
        $brand    = InventoryBrand::firstOrCreate(['name' => $merkName, 'category_id' => $category->id],['category_id' => $category->id]);

        // Cari ID Meja
        $inventory = Inventory::where('name', $namaBarang)->first();

        // === 4. PARSING TANGGAL (Support Indo) ===
        $rawDate = $row[4] ?? null;
        $purchaseDate = null;

        if (!empty($rawDate)) {
            if (is_numeric($rawDate)) {
                try {
                    $purchaseDate = Date::excelToDateTimeObject($rawDate);
                } catch (\Throwable $e) { $purchaseDate = null; }
            } else {
                // Translate Bulan Indo
                $dateString = str_replace(
                    ['Januari', 'Februari', 'Maret', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'],
                    ['January', 'February', 'March', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
                    $rawDate
                );
                // Hapus karakter aneh (seperti titik dua di akhir tanggal)
                $dateString = str_replace(':', '', $dateString); 
                try {
                    $purchaseDate = Carbon::parse($dateString);
                } catch (\Throwable $e) { $purchaseDate = null; }
            }
        }

        // === 5. PARSING SPESIFIKASI ===
        $rawSpecs = $this->cleanText($row[7] ?? '-');
        $cpu = null;
        $ram = null;
        $storage = null;

        // -- LOGIKA DETEKTIF PERBAIKAN --
        // $rawSpecs = String spesifikasi dari Excel/Input Manual

        // 1. DETEKSI CPU (Lebih lengkap: Support Gen 12/13/14, Xeon, Athlon)
        // Pola: Mencari "Core iX", "Ryzen X", "Pentium", "Celeron", "Xeon", dll.
        if (preg_match('/(core\s*i\d[-\d\w]*|ryzen\s*\d\s*[-\d\w]*|celeron|pentium|athlon|xeon|intel\s+[\w-]+)/i', $rawSpecs, $matches)) {
            $cpu = trim($matches[0]); 
        }

        // 2. DETEKSI STORAGE (SSD/HDD) - KITA CARI DULUAN
        // Pola: Angka+GB/TB diikuti tipe (500GB SSD) ATAU Tipe diikuti Angka (SSD 500GB)
        // Kita cari ini duluan biar tidak tertukar dengan RAM
        if (preg_match('/((\d+\s*(GB|TB))\s*(SSD|HDD|NVME|SATA|M\.2))|((SSD|HDD|NVME|SATA|M\.2)\s*(\d+\s*(GB|TB)))/i', $rawSpecs, $matches)) {
            // $matches[0] akan berisi string lengkap, misal "SSD 512GB" atau "1TB HDD"
            $storage = trim($matches[0]);
        }

        // 3. DETEKSI RAM (LEBIH SPESIFIK)
        // Logika: 
        // A. Cari angka yang ditempel kata kunci memori (8GB DDR4, RAM 16GB) -> Paling Akurat
        // B. Jika tidak ada kata kunci, cari angka spesifik RAM (2, 4, 8, 16, 32, 64) diikuti GB.
        //    Ini mencegah "500GB" terdeteksi sebagai RAM.

        $ramFound = false;

        // Opsi A: Ada kata "DDR" atau "RAM" atau "DIMM"
        if (preg_match('/(\d+\s*GB)\s*(DDR\d|SODIMM|DIMM|RAM|Memory)|(DDR\d|RAM|Memory)\s*(\d+\s*GB)/i', $rawSpecs, $matches)) {
            // Ambil bagian yang ada angkanya (misal "8GB")
            // Karena regex di atas ada 2 grup alternatif, kita cek mana yang ketangkap
            foreach ($matches as $m) {
                if (stripos($m, 'GB') !== false) {
                    $ram = trim($m);
                    $ramFound = true;
                    break;
                }
            }
        }

        // Opsi B: Jika Opsi A gagal, cari angka "wajar" untuk RAM (2/4/8/16/32/64 GB)
        // Tapi pastikan angka itu BUKAN bagian dari string Storage yang sudah ditemukan tadi (opsional, tapi regex di bawah sudah cukup aman)
        if (!$ramFound) {
            // Regex ini hanya menangkap angka 1 digit atau 2 digit (maksimal 99GB) untuk menghindari 500GB
            // Dan memaksa spasi batas kata (\b) agar "128GB" (SSD) tidak dianggap RAM jika kita batasi cuma 2 digit.
            if (preg_match('/\b(2|4|8|16|32|64)\s*GB\b/i', $rawSpecs, $matches)) {
                $ram = trim($matches[0]);
            }
        }

        // PACKING KE JSON
        $specsArray = [
            'raw_text' => $rawSpecs, // <--- INI KUNCINYA (Simpan teks asli apapun isinya)
            'cpu' => $cpu,           // Bisa null (untuk Kursi) atau terisi (untuk PC)
            'ram' => $ram,
            'storage' => $storage,
        ];

        // === 6. RETURN MODEL ===
        return new InventoryItem([
            'asset_code'      => $kodeAsset,     
            'item_name'       => $namaBarang,
            'brand_id'        => $brand->id,
            'category_id'     => $category->id,
            'inventory_id'    => $inventory ? $inventory->id : null, 

            'purchase_date'   => $purchaseDate,
            'asset_type'      => $this->cleanText($row[5] ?? 'Aset Tetap'),
            'purchase_price'  => $cleanPrice, // Pakai harga yang sudah dibersihkan
            'condition'       => $this->mapCondition($row[8] ?? 'good'),
            'document_number' => $this->cleanText($row[9] ?? null),
            'owner_unit'      => $this->cleanText($row[10] ?? null),
            
            'specifications'  => $specsArray,
        ]);
    }

    // Helper: Bersihkan teks dari tanda baca aneh (seperti titik dua ":")
    private function cleanText($text) {
        if (is_null($text)) return null;
        return trim(str_replace(':', '', $text));
    }

    private function mapCondition($text) {
        $text = strtolower($text);
        if (str_contains($text, 'rusak')) return 'broken';
        if (str_contains($text, 'perbaikan') || str_contains($text, 'maintenance')) return 'maintenance';
        return 'good';
    }
}