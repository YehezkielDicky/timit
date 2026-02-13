<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    public function showLogin()
    {
        return view('login');
    }

    public function doLogin(Request $request)
    {
        $data = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ], [
            'username.required' => 'Username wajib diisi',
            'password.required'  => 'Password wajib diisi',
        ]);

        $username = trim((string) $data['username']);
        $password = (string) $data['password'];

        // Cari user
        $user = User::whereRaw('TRIM(username) = ?', [$username])->first();

        if (!$user) {
            return back()->withErrors(['username' => 'Username tidak terdaftar'])->withInput();
        }

        // Cek password: dukung hash & plaintext (sementara)
        $passwordOk = Hash::check($password, $user->password)
            || hash_equals((string) $user->password, $password);

        if (!$passwordOk) {
            return back()->withErrors(['password' => 'Password salah'])->withInput();
        }

        // Tolak user non-aktif (jika kolom status ada)
        if (isset($user->status) && $user->status !== 'active') {
            return back()->withErrors(['username' => 'Akun Anda tidak aktif.'])->withInput();
        }

        // Hanya role yang diizinkan
        if (!in_array($user->role, ['admin', 'staff', 'koordinator', 'officer'], true)) {
            return back()->withErrors(['username' => 'Role tidak diizinkan.'])->withInput();
        }

        // Login via session
        $request->session()->regenerate();
        $request->session()->put('auth_user', [
            'id'       => $user->id_user,
            'name'     => $user->nama_user,
            'username' => $user->username,
            'role'     => $user->role,
            'status'   => $user->status ?? 'active',
        ]);

        // Satu dashboard untuk semua role
        return redirect()->route('admin.dashboard');
    }

    public function logout(Request $request)
    {
        $request->session()->forget('auth_user');
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return to_route('admin.login');
    }
}
