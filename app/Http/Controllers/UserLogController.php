<?php

namespace App\Http\Controllers;

use App\Models\UserLog;
use Illuminate\Http\Request;

class UserLogController extends Controller
{
    // Menampilkan daftar log
    // public function index(Request $request)
    // {
    //     $user = $request->session()->get('auth_user');

    //     // Cek role: hanya admin dan koordinator yang boleh
    //     if (!in_array($user['role'] ?? '', ['admin', 'koordinator'])) {
    //         abort(403, 'Anda tidak memiliki izin untuk mengakses halaman ini.');
    //     }

    //     $logs = UserLog::with('user')->orderByDesc('id')->paginate(25);
    //     return view('admin.logs.index', compact('logs'));
    // }
    public function index(Request $request)
    {
        $user = $request->session()->get('auth_user');

        // Cek role
        if (!in_array($user['role'] ?? '', ['admin', 'koordinator'])) {
            abort(403, 'Anda tidak memiliki izin untuk mengakses halaman ini.');
        }

        // Ambil keyword pencarian
        $search = $request->input('search');

        // Query dasar
        $logs = UserLog::with('user')
            ->when($search, function ($query) use ($search) {
                $query->whereHas('user', function ($q) use ($search) {
                    $q->where('nama_user', 'like', "%$search%");
                })
                ->orWhere('description', 'like', "%$search%");
            })
            ->orderByDesc('id')
            ->paginate(25);

        return view('admin.logs.index', compact('logs', 'search'));
    }

    // Method helper untuk menyimpan log (bisa dipanggil dari controller lain)
    public static function add($activity, $module, $description = null)
    {
        $user = session('auth_user');

        if ($user) {
            UserLog::create([
                'user_id' => $user['id_user'],
                'activity' => $activity,
                'module' => $module,
                'description' => $description,
                'ip_address' => request()->ip(),
                'user_agent' => request()->header('User-Agent'),
                'created_at' => now(),
            ]);
        }
    }
}
