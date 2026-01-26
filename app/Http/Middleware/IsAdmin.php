<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class IsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        // Jika user belum login, atau role-nya BUKAN admin
        if (!Auth::check()) {
            // Tendang ke halaman dashboard mahasiswa biasa atau 403
            return redirect('/login'); 
        }
        
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // 2. LOGIKA MANUAL: CEK DATA RELASI
        // Karena satu user bisa punya banyak role, kita tidak bisa pakai '==' (sama dengan)
        // Kita harus "Mencari" apakah ada tiket 'admin' di kantong user.
        // dd($user);
        // Cara bacanya: 
        // "Tolong ambil user ini -> masuk ke relasi roles -> cari yang kolom role-nya 'admin' -> apakah ada?"
        // $isAdmin = $user->roles()->where('role', 'admin')->exists();
        // $isAdmin = $user->hasRole('admin');

        // 3. PERBANDINGAN
        // Jika hasilnya FALSE (Tidak punya tiket admin)
        if (! $user->hasRole('admin')) {
            // Tendang balik ke dashboard utama
            return redirect('/dashboard')->with('error', 'Anda bukan Admin!');
        }

        return $next($request);
    }
}