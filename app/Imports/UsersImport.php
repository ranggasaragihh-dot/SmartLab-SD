<?php

namespace App\Imports;

use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class UsersImport implements ToModel, WithHeadingRow, WithValidation
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        if (!isset($row['nama_lengkap']) || !isset($row['email'])) {
            return null;
        }

        $inputTA = isset($row['ambil_ta']) ? strtolower(trim($row['ambil_ta'])) : '';
        $statusTA = in_array($inputTA, ['ya', 'yes', '1', 'true']);

        // 1. Create User
        $user = User::create([
            // Sesuaikan dengan heading di Excel (name, email, nomor_induk)
            'name'        => $row['name'], 
            'email'       => $row['email'],
            'nomor_induk' => $row['nomor_induk'] ?? null,
            'ta'          => $statusTA,
            // Password default: 12345678 (atau ambil dari excel jika ada)
            'password'    => Hash::make('12345678'), 
        ]);

        // 2. Auto Assign Role 'Mahasiswa' (atau role default lain)
        // Pastikan role 'mahasiswa' ada di DB, atau ganti logic ini
        $role = Role::where('name', 'mahasiswa')->first();
        if ($role) {
            $user->roles()->attach($role->id);
        }

        return $user;
    }

    public function rules(): array
    {
        return [
            'name' => 'required',
            // Validasi email unique agar import tidak crash jika ada duplikat
            'email' => 'required|email|unique:users,email', 
        ];
    }
}