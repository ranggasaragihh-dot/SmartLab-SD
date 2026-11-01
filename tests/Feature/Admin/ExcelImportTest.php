<?php

namespace Tests\Feature\Admin;

use App\Imports\InventoryItemsImport;
use App\Imports\UsersImport;
use App\Models\InventoryItem;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
use Tests\TestCase;

/**
 * Format file mengikuti Buku Panduan bab 4.2B dan 4.3B.
 */
class ExcelImportTest extends TestCase
{
    use RefreshDatabase;

    private function writeSheet(array $rows): string
    {
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getActiveSheet()->fromArray($rows);
        $path = tempnam(sys_get_temp_dir(), 'import') . '.csv';
        // CSV: tidak butuh ekstensi zip (xlsx butuh ext-zip di server).
        (new Csv($spreadsheet))->save($path);

        return $path;
    }

    public function test_user_import_uses_manual_headers_and_npm_as_initial_password(): void
    {
        foreach (['admin', 'student', 'lecturer'] as $role) {
            Role::create(['role' => $role]);
        }

        $path = $this->writeSheet([
            ['Nama Lengkap', 'Email', 'Nomor Induk', 'Role', 'Ambil TA'],
            ['Gilang Ramadhan', 'gilang@example.test', '210710001', 'Mahasiswa', 'Ya'],
            ['Dr. Hendra', '', '19800101', 'Dosen', ''],
        ]);

        Excel::import(new UsersImport, $path);

        $gilang = User::where('nomor_induk', '210710001')->firstOrFail();
        $this->assertTrue($gilang->hasRole('student'));
        $this->assertTrue((bool) $gilang->ta);
        $this->assertTrue(auth()->validate(['nomor_induk' => '210710001', 'password' => '210710001']));

        $hendra = User::where('nomor_induk', '19800101')->firstOrFail();
        $this->assertTrue($hendra->hasRole('lecturer'));
        $this->assertNull($hendra->email);
    }

    public function test_inventory_import_can_be_repeated_without_duplicate_errors(): void
    {
        $header = ['Kode Aset:', 'Nama Barang:', 'Merk:', 'Kategori:', 'Tanggal Terima:', 'Aktiva:', 'Harga Beli:', 'Spesifikasi:', 'Kondisi Barang:', 'No Dokumen:', 'Unit:', 'Ruang:'];
        $row = ['FTI-001', 'Komputer PC', 'Lenovo', 'Komputer', '2026-01-10', 'Fixture', '7.500.000', 'Intel Core i5-10400 RAM 8GB DDR4 SSD 512GB', 'Baik', 'FTI/2026/001', 'FTI', 'Lab SD'];

        Excel::import(new InventoryItemsImport, $this->writeSheet([$header, $row]));

        $row[8] = 'Perbaikan';
        Excel::import(new InventoryItemsImport, $this->writeSheet([$header, $row]));

        $this->assertSame(1, InventoryItem::where('asset_code', 'FTI-001')->count());
        $item = InventoryItem::where('asset_code', 'FTI-001')->first();
        $this->assertSame('maintenance', $item->condition);
        $this->assertStringStartsWith('8GB', str_replace(' ', '', $item->specifications['ram']));
        $this->assertStringContainsString('i5', $item->specifications['cpu']);
    }
}
