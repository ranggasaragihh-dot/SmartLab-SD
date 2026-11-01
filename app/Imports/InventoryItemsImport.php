<?php

namespace App\Imports;

use App\Models\InventoryItem;
use App\Models\Inventory;
use App\Models\InventoryBrand;
use App\Models\InventoryCategory;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;
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
        // Jika kolom Harga isinya teks "Harga Beli:", SKIP baris ini.
        $cekHeader = strtolower($row[6] ?? '');
        if (str_contains($cekHeader, 'harga')) {
            return null;
        }
        
        // Jika Kode Aset kosong, juga SKIP
        if (empty($row[0])) {
            return null;
        }

        // Hapus tanda titik dua ":" jika terbawa dari Excel (Misal "Merk:")
        $kodeAsset   = $this->cleanText($row[0]);
        $namaBarang  = $this->cleanText($row[1]); 
        $merkName    = $this->cleanText($row[2] ?? 'Tanpa Merk');
        $katName     = $this->cleanText($row[3] ?? 'Umum');
        $rawType     = $this->cleanText($row[5] ?? '');
        
        $cleanType = ucwords(strtolower(trim($rawType)));

        if (empty($cleanType)) {
            $cleanType = 'General';
        }

        $rawPrice = $row[6] ?? 0;
        $cleanPrice = preg_replace('/[^0-9]/', '', $rawPrice);
        if (empty($cleanPrice)) $cleanPrice = 0;

        $category = InventoryCategory::firstOrCreate(['name' => $katName]);
        $brand    = InventoryBrand::firstOrCreate(['name' => $merkName, 'category_id' => $category->id],['category_id' => $category->id]);
        $inventory = Inventory::where('name', $namaBarang)->first();

        $rawDate = $row[4] ?? null;
        $purchaseDate = null;

        if (!empty($rawDate)) {
            if (is_numeric($rawDate)) {
                try {
                    $purchaseDate = Date::excelToDateTimeObject($rawDate);
                } catch (\Throwable $e) { $purchaseDate = null; }
            } else {
                $dateString = str_replace(
                    ['Januari', 'Februari', 'Maret', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'],
                    ['January', 'February', 'March', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
                    $rawDate
                );
                $dateString = str_replace(':', '', $dateString); 
                try {
                    $purchaseDate = Carbon::parse($dateString);
                } catch (\Throwable $e) { $purchaseDate = null; }
            }
        }

        // === PARSING SPESIFIKASI ===
        $rawSpecs = $this->cleanText($row[7] ?? '-');
        $cpu = null;
        $ram = null;
        $storage = null;

        // DETEKSI CPU
        // Pola spesifik dicoba dulu: "Intel Core i5-10400" harus menjadi "Core i5-10400", bukan "Intel Core".
        foreach ([
            '/core\s*i\d[-\d\w]*/i',
            '/ryzen\s*\d\s*[-\d\w]*/i',
            '/(celeron|pentium|athlon|xeon)[\s-]*[\w-]*/i',
            '/intel\s+[\w-]+/i',
        ] as $cpuPattern) {
            if (preg_match($cpuPattern, $rawSpecs, $matches)) {
                $cpu = trim($matches[0]);
                break;
            }
        }

        // DETEKSI STORAGE (SSD/HDD)
        if (preg_match('/((\d+\s*(GB|TB))\s*(SSD|HDD|NVME|SATA|M\.2))|((SSD|HDD|NVME|SATA|M\.2)\s*(\d+\s*(GB|TB)))/i', $rawSpecs, $matches)) {
            $storage = trim($matches[0]);
        }

        $ramFound = false;

        // Opsi A: Ada kata "DDR" atau "RAM" atau "DIMM"
        if (preg_match('/(\d+\s*GB)\s*(DDR\d|SODIMM|DIMM|RAM|Memory)|(DDR\d|RAM|Memory)\s*(\d+\s*GB)/i', $rawSpecs, $matches)) {
            // Ambil grup yang hanya berisi kapasitas ("8 GB"), bukan seluruh teks cocokan ("RAM 8GB").
            foreach (array_slice($matches, 1) as $m) {
                if (preg_match('/^\d+\s*GB$/i', trim($m))) {
                    $ram = trim($m);
                    $ramFound = true;
                    break;
                }
            }
        }

        // Opsi B: Jika Opsi A gagal, cari angka "wajar" untuk RAM (2/4/8/16/32/64 GB)
        if (!$ramFound) {
            if (preg_match('/\b(2|4|8|16|32|64)\s*GB\b/i', $rawSpecs, $matches)) {
                $ram = trim($matches[0]);
            }
        }

        $specsArray = [
            'raw_text' => $rawSpecs,
            'cpu' => $cpu,
            'ram' => $ram,
            'storage' => $storage,
        ];

        // Kode aset unik: impor ulang file yang sama memperbarui data, bukan gagal karena duplikat.
        InventoryItem::updateOrCreate(['asset_code' => $kodeAsset], [
            'item_name'       => $namaBarang,
            'brand_id'        => $brand->id,
            'category_id'     => $category->id,
            'inventory_id'    => $inventory ? $inventory->id : null, 
            'purchase_date'   => $purchaseDate,
            'asset_type'      => $cleanType,
            'purchase_price'  => $cleanPrice,
            'condition'       => $this->mapCondition($row[8] ?? 'good'),
            'document_number' => $this->cleanText($row[9] ?? null),
            'owner_unit'      => $this->cleanText($row[10] ?? null),
            'specifications'  => $specsArray,
        ]);

        return null;
    }

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