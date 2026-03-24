<?php

namespace App\Imports;

use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Row;

class UsersImport implements OnEachRow, WithHeadingRow, WithValidation, WithChunkReading
{
    public function onRow(Row $row)
    {
        $data = $row->toArray();

        // Kita normalkan input excel (huruf kecil semua)
        $inputRole = strtolower(trim($data['role'] ?? 'mahasiswa'));

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
        $targetRoleName = $roleMap[$inputRole] ?? 'student';

        // Logic TA (Ya/Tidak -> Boolean)
        $inputTA = strtolower(trim($data['ambil_ta'] ?? ''));
        $statusTA = in_array($inputTA, ['ya', 'yes', '1', 'true']);

        $user = User::firstOrNew(['nomor_induk' => trim($data['nomor_induk'])]);
        $inputEmail = trim($data['email'] ?? '');
        $user->email = ($inputEmail === '') ? null : $inputEmail;
        $user->name = $data['nama_lengkap'];
        $user->ta = $statusTA;

        if (!$user->exists) {
            $user->password = Hash::make((string) $data['nomor_induk']);
        }
        $user->save();

        $roleDB = Role::where('role', $targetRoleName)->first();
        if ($roleDB) {
            $user->roles()->sync([$roleDB->id]);
        }
    }

    public function rules(): array
    {
        return [
            'nama_lengkap' => 'required',
            'email'        => 'nullable|email',
            'nomor_induk'  => 'required',
            'role'         => 'nullable|string',
        ];
    }

    public function chunkSize(): int
    {
        return 500; 
    }
}