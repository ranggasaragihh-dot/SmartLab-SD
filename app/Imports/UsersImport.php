<?php

namespace App\Imports;

use App\Models\User;
use App\Models\Role; // Pastikan Model Role diimport
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Row;

class UsersImport implements OnEachRow, WithHeadingRow, WithValidation
{
    public function onRow(Row $row)
    {
        $data = $row->toArray();

        // -------------------------------------------------------
        // 1. MAPPING ROLE (Excel -> Database Name)
        // -------------------------------------------------------
        // Kita normalkan input excel (huruf kecil semua)
        $inputRole = strtolower(trim($data['role'] ?? 'mahasiswa')); // Default ke mahasiswa jika kosong

        // Kamus Penerjemah
        $roleMap = [
            'mahasiswa' => 'student',
            'student'   => 'student',
            'murid'     => 'student',
            
            'dosen'     => 'lecturer',
            'lecturer'  => 'lecturer',
            'pengajar'  => 'lecturer',
            
            'admin'     => 'admin',
            'staff'     => 'admin',
        ];

        // Tentukan Role DB yang mau dipakai
        // Jika tidak ada di kamus, default ke 'student' (Safety Net)
        $targetRoleName = $roleMap[$inputRole] ?? 'student';


        // -------------------------------------------------------
        // 2. BUAT / UPDATE USER (Tabel 'users')
        // -------------------------------------------------------
        // Logic TA (Ya/Tidak -> Boolean)
        $inputTA = strtolower(trim($data['ambil_ta'] ?? ''));
        $statusTA = in_array($inputTA, ['ya', 'yes', '1', 'true']);

        $user = User::firstOrCreate(
            ['email' => $data['email']], // Kunci pengecekan (biar gak duplikat)
            [
                'name'          => $data['nama_lengkap'],
                'nomor_induk'   => $data['nomor_induk'],
                'password'      => Hash::make('12345678'), // Password Default
                'ta'            => $statusTA,
            ]
        );

        // -------------------------------------------------------
        // 3. SAMBUNGKAN KE PIVOT (Tabel 'role_user')
        // -------------------------------------------------------
        // Cari Role ID berdasarkan nama mutlak tadi ('student'/'lecturer'/'admin')
        // Pastikan kolom di DB anda bernama 'role' (sesuai diskusi sebelumnya)
        $roleDB = Role::where('role', $targetRoleName)->first();

        if ($roleDB) {
            // Masukkan ID User dan ID Role ke tabel pivot
            // syncWithoutDetaching mencegah error jika user diimport 2 kali
            $user->roles()->syncWithoutDetaching([$roleDB->id]);
        }
    }

    public function rules(): array
    {
        return [
            'nama_lengkap' => 'required',
            'email'        => 'required|email',
            'nomor_induk'  => 'required',
            // 'role'      => 'required', // Opsional, kalau kosong kena default mahasiswa
        ];
    }
}