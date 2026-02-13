<?php

namespace App\Http\Controllers;

use App\Models\Dtrans;
use App\Models\Htrans;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class AdminController extends Controller
{
    public function dashboard()
    {
        $now   = Carbon::now();
        $start = $now->copy()->subMonths(11)->startOfMonth();
        $end   = $now->copy()->endOfMonth();

        $dateExpr = "(
        CASE
            WHEN h.tanggal LIKE '%/%/%' THEN STR_TO_DATE(h.tanggal, '%d/%m/%Y')
            ELSE h.tanggal
        END
    )";

        // ===== Total transaksi / bulan
        $rawMonthly = DB::table('h_trans as h')
            ->selectRaw("DATE_FORMAT($dateExpr, '%Y-%m') as ym, COUNT(*) as total")
            ->whereBetween(DB::raw($dateExpr), [$start->toDateString(), $end->toDateString()])
            ->groupBy('ym')
            ->orderBy('ym')
            ->pluck('total', 'ym');

        $months        = collect(range(0, 11))->map(fn($i) => $start->copy()->addMonths($i));
        $monthlyLabels = $months->map(fn($d) => $d->isoFormat('MMM YYYY'));
        $monthlyData   = $months->map(fn($d) => (int) ($rawMonthly[$d->format('Y-m')] ?? 0));

        // ===== Ringkasan bulan ini
        $cmStart = $now->copy()->startOfMonth()->toDateString();
        $cmEnd   = $now->copy()->endOfMonth()->toDateString();

        $sumKeluarBulanIni = (int) DB::table('d_trans as d')
            ->join('h_trans as h', 'd.id_trans', '=', 'h.id_trans')
            ->where('h.jenis', 'keluar')
            ->whereBetween(DB::raw($dateExpr), [$cmStart, $cmEnd])
            ->sum('d.qty');

        $sumMasukBulanIni = (int) DB::table('d_trans as d')
            ->join('h_trans as h', 'd.id_trans', '=', 'h.id_trans')
            ->where('h.jenis', 'masuk')
            ->whereBetween(DB::raw($dateExpr), [$cmStart, $cmEnd])
            ->sum('d.qty');

        $countTransaksiBulanIni = (int) DB::table('h_trans as h')
            ->whereBetween(DB::raw($dateExpr), [$cmStart, $cmEnd])
            ->count();

        $countUnitAktifBulanIni = (int) DB::table('d_trans as d')
            ->join('h_trans as h', 'd.id_trans', '=', 'h.id_trans')
            ->where('h.jenis', 'keluar')
            ->whereBetween(DB::raw($dateExpr), [$cmStart, $cmEnd])
            ->distinct('h.id_unit')
            ->count('h.id_unit');

        // ===== Top barang keluar (bulan ini)
        $topBarangKeluar = DB::table('d_trans as d')
            ->join('h_trans as h', 'd.id_trans', '=', 'h.id_trans')
            ->join('barang as b', 'b.id_barang', '=', 'd.id_barang')
            ->where('h.jenis', 'keluar')
            ->whereBetween(DB::raw($dateExpr), [$cmStart, $cmEnd])
            ->groupBy('b.id_barang', 'b.nama_barang')
            ->selectRaw('b.nama_barang, SUM(d.qty) as total_keluar')
            ->orderByDesc('total_keluar')
            ->limit(7)
            ->get();

        // Map ke array untuk Chart.js
        $topBarangLabels = $topBarangKeluar->pluck('nama_barang')->values()->all();
        $topBarangData   = $topBarangKeluar->pluck('total_keluar')->map(fn($v) => (int)$v)->values()->all();

        // ===== Top unit peminta (bulan ini)
        $topUnitRows = DB::table('d_trans as d')
            ->join('h_trans as h', 'd.id_trans', '=', 'h.id_trans')
            ->leftJoin('unit_kerja as u', 'u.id_unit', '=', 'h.id_unit')
            ->where('h.jenis', 'keluar')
            ->whereBetween(DB::raw($dateExpr), [$cmStart, $cmEnd])
            ->groupBy('u.id_unit', 'u.unit_kerja')
            ->selectRaw('COALESCE(u.unit_kerja, "-") as unit_kerja, SUM(d.qty) as total_keluar')
            ->orderByDesc('total_keluar')
            ->limit(7)
            ->get();

        $topUnitLabels = $topUnitRows->pluck('unit_kerja')->values()->all();
        $topUnitData   = $topUnitRows->pluck('total_keluar')->map(fn($v) => (int)$v)->values()->all();

        // return view('admin.dashboard', compact(
        //     'monthlyLabels',
        //     'monthlyData',
        //     'sumKeluarBulanIni',
        //     'sumMasukBulanIni',
        //     'countTransaksiBulanIni',
        //     'countUnitAktifBulanIni',
        //     'topBarangLabels',
        //     'topBarangData',
        //     'topUnitLabels',
        //     'topUnitData'
        // ));

        // ===== Barang stok minimum (<= 2)
        $lowStockItems = DB::table('barang')
            ->select(
                'id_barang',
                'nama_barang',
                DB::raw('COALESCE(qty, 0) as qty')
            )
            ->whereRaw('COALESCE(qty, 0) <= 2')
            ->orderByRaw('COALESCE(qty, 0) asc')
            ->get();

        $lowStockCount = $lowStockItems->count();

        return view('admin.dashboard', compact(
            // variabel lama
            'monthlyLabels',
            'monthlyData',
            'sumKeluarBulanIni',
            'sumMasukBulanIni',
            'countTransaksiBulanIni',
            'countUnitAktifBulanIni',
            'topBarangLabels',
            'topBarangData',
            'topUnitLabels',
            'topUnitData',

            // ⬇ tambahan baru
            'lowStockItems',
            'lowStockCount'
        ));
    }

    public function index(Request $request)
    {
        // optional filter sederhana
        $q = trim((string)$request->get('q', ''));
        $users = User::when($q, function ($w) use ($q) {
            $w->where(function ($x) use ($q) {
                $x->where('nama_user', 'like', "%$q%")
                    ->orWhere('username', 'like', "%$q%")
                    ->orWhere('role', 'like', "%$q%");
            });
        })
            ->orderBy('id_user', 'asc')
            ->get(['id_user', 'nama_user', 'username', 'role', 'status']);

        return view('admin.users.index', compact('users', 'q'));
    }

    // ====== PROFIL USER YANG LOGIN ======
    public function profile(Request $request)
    {
        $u = $request->session()->get('auth_user'); // ambil dari session
        if (!$u) {
            return redirect()->route('admin.login')->withErrors(['msg' => 'Silakan login dahulu.']);
        }

        // Ambil fresh dari DB (biar up-to-date)
        $user = User::find($u['id']);
        if (!$user) {
            return redirect()->route('admin.login')->withErrors(['msg' => 'Akun tidak ditemukan.']);
        }

        return view('admin.profile', compact('user'));
    }

    // ====== FORM TAMBAH USER (ADMIN-ONLY) ======
    public function createUser()
    {
        $roles = ['admin', 'staff', 'koordinator', 'officer'];
        return view('admin.users.create', compact('roles'));
    }

    // ====== SIMPAN USER BARU (ADMIN-ONLY) ======
    public function storeUser(Request $request)
    {
        $data = $request->validate([
            'nama_user' => ['required', 'string', 'max:100'],
            'username'  => ['required', 'regex:/^[A-Za-z0-9]+$/', 'max:50', 'unique:users,username'],
            'password'  => ['required', 'string', 'min:3'],
            'role'      => ['required', 'in:admin,staff,koordinator,officer'],
            'status'    => ['nullable', 'in:active,inactive'],
        ], [
            'username.unique' => 'Username sudah dipakai.',
        ]);

        $user = new User();
        $user->nama_user = $data['nama_user'];
        $user->username  = $data['username'];
        $user->password  = Hash::make($data['password']); // simpan BCRYPT
        $user->role      = $data['role'];
        $user->status    = $data['status'] ?? 'active';
        $user->save();

        return redirect()->route('admin.dashboard')->with('success', 'User baru berhasil dibuat.');
    }

    // ====== UPDATE STATUS USER (ADMIN-ONLY) ======
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => ['required', 'in:active,inactive'],
        ]);

        $user = User::find($id);
        if (!$user) {
            return back()->withErrors(['msg' => 'User tidak ditemukan.']);
        }

        // optional: cegah admin menonaktifkan dirinya sendiri
        $auth = $request->session()->get('auth_user');
        if ($auth && (int)$auth['id'] === (int)$user->id_user) {
            return back()->withErrors(['msg' => 'Tidak bisa mengubah status akun yang sedang login.']);
        }

        $user->status = $request->status;
        $user->save();

        return back()->with('success', 'Status user diperbarui.');
    }

    public function updatePassword(Request $request)
    {
        $u = $request->session()->get('auth_user');
        if (!$u) return to_route('admin.login');

        $request->validate([
            'current_password' => ['required', 'string'],
            'new_password'     => ['required', 'string', 'min:6'],
            'confirm_password' => ['required', 'same:new_password'],
        ], [
            'confirm_password.same' => 'Konfirmasi password tidak sama.',
        ]);

        $user = User::findOrFail($u['id']);

        // validasi current (dukung hash atau plaintext sisa-sisa lama)
        $ok = Hash::check($request->current_password, $user->password)
            || hash_equals((string)$user->password, (string)$request->current_password);

        if (!$ok) {
            return back()->withErrors(['current_password' => 'Password sekarang salah.'])->withInput();
        }

        // simpan hash baru
        $user->password = Hash::make($request->new_password);
        $user->save();

        return back()->with('success_password', 'Password berhasil diubah.');
    }

    // ====== RESET PASSWORD USER (ADMIN-ONLY) ======
    public function resetUserPassword(Request $request, $id)
    {
        // pastikan login
        $auth = $request->session()->get('auth_user');
        if (!$auth) {
            return redirect()->route('admin.login');
        }

        // pastikan admin
        if ($auth['role'] !== 'admin') {
            abort(403, 'Tidak punya akses');
        }

        $request->validate([
            'new_password' => ['required', 'string', 'min:6'],
        ]);

        $user = User::find($id);
        if (!$user) {
            return back()->withErrors(['msg' => 'User tidak ditemukan.']);
        }

        // optional: admin tidak bisa reset password dirinya sendiri
        if ((int)$auth['id'] === (int)$user->id_user) {
            return back()->withErrors(['msg' => 'Gunakan menu ganti password sendiri.']);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return back()->with('success', 'Password user berhasil direset.');
    }

    public function updatePhoto(Request $request)
    {
        $u = $request->session()->get('auth_user');
        if (!$u) return to_route('admin.login');

        $request->validate([
            'photo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'], // 2MB
        ]);

        $user = User::findOrFail($u['id']);

        // simpan ke storage/avatars
        $path = $request->file('photo')->store('avatars', 'public');

        // hapus foto lama (opsional)
        if (!empty($user->photo) && Storage::disk('public')->exists($user->photo)) {
            Storage::disk('public')->delete($user->photo);
        }

        $user->photo = $path; // pastikan kolom 'photo' ada di tabel users (nullable)
        $user->save();

        // update data session biar topbar ganti avatar tanpa relog
        $request->session()->put('auth_user.photo', $path);

        return back()->with('success_photo', 'Foto profil berhasil diperbarui.');
    }
}
