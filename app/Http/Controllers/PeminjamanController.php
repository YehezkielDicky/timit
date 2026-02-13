<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Peminjaman;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PeminjamanController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string)$request->get('q', ''));

        $data = Peminjaman::with(['barang', 'user'])
            ->when($q, function ($w) use ($q) {
                $w->whereHas('barang', fn($x) => $x->where('nama_barang', 'like', "%{$q}%"))
                    ->orWhereHas('user', fn($x) => $x->where('nama_user', 'like', "%{$q}%"));
            })
            ->orderBy('tanggal', 'desc')
            ->orderBy('id_peminjaman', 'desc')
            ->get();

        $barang = Barang::orderBy('nama_barang')->get(['id_barang', 'nama_barang', 'qty']);
        $users  = User::orderBy('nama_user')->get(['id_user', 'nama_user']);

        return view('admin.peminjaman.index', compact('data', 'q', 'barang', 'users'));
    }

    public function store(Request $request)
    {
        $val = $request->validate([
            'id_barang'        => ['required', 'integer', Rule::exists('barang', 'id_barang')],
            'id_users'         => ['required', 'integer', Rule::exists('users', 'id_user')],
            'jumlah'           => ['required', 'integer', 'min:1'],
            'tanggal'          => ['required', 'date'],
            'keterangan'       => ['nullable', 'string', 'max:255'],
            'status'           => ['nullable', Rule::in(['dipinjam', 'dikembalikan'])],
            'tanggal_kembali'  => ['nullable', 'date'],
        ]);

        $val['status'] = $val['status'] ?? 'dipinjam';
        $val['tanggal_kembali'] = $val['status'] === 'dikembalikan'
            ? ($val['tanggal_kembali'] ?? now()->toDateString())
            : null;

        DB::transaction(function () use ($val) {
            $barang = Barang::lockForUpdate()->findOrFail($val['id_barang']);

            // Bila status dipinjam → kurangi stok, jika dikembalikan → tidak mengurangi
            if ($val['status'] === 'dipinjam') {
                if ($barang->qty < $val['jumlah']) {
                    abort(422, 'Stok tidak mencukupi. Sisa stok: ' . $barang->qty);
                }
                $barang->qty -= $val['jumlah'];
                $barang->save();
            }

            Peminjaman::create($val);
        });

        return back()->with('success', 'Peminjaman berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $p = Peminjaman::findOrFail($id);

        $val = $request->validate([
            'id_barang' => 'required|exists:barang,id_barang',
            'id_users' => 'required|exists:users,id_user',
            'jumlah' => 'required|integer|min:1',
            'tanggal' => 'required|date',
            'status' => 'required|in:dipinjam,dikembalikan',
            'tanggal_kembali' => 'nullable|date',
            'keterangan' => 'nullable|string|max:255',
        ]);

        DB::transaction(function () use ($p, $val) {
            $barang = Barang::lockForUpdate()->findOrFail($val['id_barang']);

            // Jika sebelumnya status "dipinjam", kembalikan stok dulu
            if ($p->status === 'dipinjam') {
                $barang->qty += $p->jumlah;
            }

            // Jika sekarang status "dipinjam", kurangi stok lagi sesuai jumlah baru
            if ($val['status'] === 'dipinjam') {
                if ($barang->qty < $val['jumlah']) {
                    throw new \Exception('Stok barang tidak mencukupi.');
                }
                $barang->qty -= $val['jumlah'];
            }

            $barang->save();

            // Update peminjaman
            $p->update([
                'id_barang' => $val['id_barang'],
                'id_users' => $val['id_users'],
                'jumlah' => $val['jumlah'],
                'tanggal' => $val['tanggal'],
                'status' => $val['status'],
                'tanggal_kembali' => $val['status'] === 'dikembalikan'
                    ? ($val['tanggal_kembali'] ?? now())
                    : null,
                'keterangan' => $val['keterangan'] ?? null,
            ]);
        });

        return redirect()->route('peminjaman.index')->with('success', 'Peminjaman berhasil diperbarui.');
    }


    public function destroy($id)
    {
        DB::transaction(function () use ($id) {
            $p = Peminjaman::lockForUpdate()->findOrFail($id);

            // Jika masih dipinjam, kembalikan stok dulu
            if ($p->status === 'dipinjam') {
                $barang = Barang::lockForUpdate()->findOrFail($p->id_barang);
                $barang->qty += $p->jumlah;
                $barang->save();
            }

            $p->delete();
        });

        return back()->with('success', 'Peminjaman dihapus.');
    }

    // === Aksi cepat: kembalikan barang ===
    public function kembalikan($id)
    {
        DB::transaction(function () use ($id) {
            $p = Peminjaman::lockForUpdate()->findOrFail($id);

            if ($p->status === 'dikembalikan') {
                return; // tidak lakukan apa-apa
            }

            // tambah stok kembali
            $barang = Barang::lockForUpdate()->findOrFail($p->id_barang);
            $barang->qty += $p->jumlah;
            $barang->save();

            $p->status = 'dikembalikan';
            $p->tanggal_kembali = now()->toDateString();
            $p->save();
        });

        return back()->with('success', 'Barang dikembalikan & stok ditambah.');
    }
}
