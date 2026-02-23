<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\UserLog;
use App\Models\Htrans;
use App\Models\Kategori;
use Illuminate\Http\Request;

class BarangController extends Controller
{
    // helper log
    protected function writeLog(Request $request, string $activity, string $module, ?string $desc = null): void
    {
        $u = $request->session()->get('auth_user');
        if (!$u) return;

        UserLog::create([
            'user_id'    => $u['id'],
            'activity'   => $activity,
            'module'     => $module,
            'description' => $desc,
            'ip_address' => $request->ip(),
            'user_agent' => $request->header('User-Agent'),
            'created_at' => now(),
        ]);
    }

    // LIST
    public function index(Request $request)
    {
        $q = trim($request->get('q',''));

        // Barang aktif (pagination)
        $data = Barang::where('status_barang', 'aktif')
            ->when($q, fn($w) => $w->where('nama_barang', 'like', "%{$q}%"))
            ->with('kategori')
            ->orderBy('id_barang', 'asc')
            ->paginate(10, ['*'], 'aktif_page'); // paginate aktif

        // Barang nonaktif (pagination)
        $nonaktif = Barang::where('status_barang', 'nonaktif')
            ->when($q, fn($w) => $w->where('nama_barang', 'like', "%{$q}%"))
            ->with('kategori')
            ->orderBy('id_barang', 'asc')
            ->paginate(10, ['*'], 'nonaktif_page'); // paginate nonaktif

        $kategori = Kategori::orderBy('nama_kategori')->get();

        return view('admin.barang.index', compact('data', 'nonaktif', 'q', 'kategori'));
    }

    public function ajaxDelete(Request $request)
    {
        $barang = Barang::find($request->id);
        if (!$barang) return response()->json(['status' => false]);

        $barang->status_barang = "nonaktif";
        $barang->save();

        return response()->json(['status' => true]);
    }

    public function ajaxRestore(Request $request)
    {
        $barang = Barang::find($request->id);
        if (!$barang) return response()->json(['status' => false]);

        $barang->status_barang = "aktif";
        $barang->save();

        return response()->json(['status' => true]);
    }

    // FORM CREATE
    public function create()
    {
        $kategori = Kategori::orderBy('nama_kategori')->get();
        return view('admin.barang.create', compact('kategori'));
    }

    // STORE
    public function store(Request $request)
    {
        $val = $request->validate([
            'nama_barang' => 'required|max:100|unique:barang,nama_barang',
            'id_kategori' => 'required|exists:kategori,id_kategori',
        ]);

        $barang = Barang::create($val);

        $this->writeLog($request, 'Create', 'Barang', "Tambah: {$barang->nama_barang}");

        return redirect()->route('barang.index')->with('success', 'Barang berhasil ditambahkan.');
    }

    // EDIT
    public function edit($id)
    {
        $barang = Barang::findOrFail($id);
        $kategori = Kategori::orderBy('nama_kategori')->get();

        return view('admin.barang.edit', compact('barang', 'kategori'));
    }

    // UPDATE
    public function update(Request $request, $id)
    {
        $barang = Barang::findOrFail($id);

        $val = $request->validate([
            'nama_barang' => 'required|max:100|unique:barang,nama_barang,' . $barang->id_barang . ',id_barang',
            'id_kategori' => 'required|exists:kategori,id_kategori',
        ]);

        $barang->update($val);

        $this->writeLog($request, 'Update', 'Barang', "Update: {$barang->nama_barang}");

        return redirect()->route('barang.index')->with('success', 'Barang berhasil diperbarui.');
    }

    // DELETE
    public function destroy(Request $request, $id)
    {
        $barang = Barang::findOrFail($id);
        $barang->status_barang = 'nonaktif';
        $barang->save();

        return back()->with('success', 'Barang dipindahkan ke daftar nonaktif.');
    }
    public function restore($id)
    {
        $barang = Barang::findOrFail($id);
        $barang->status_barang = 'aktif';
        $barang->save();

        return back()->with('success', 'Barang berhasil diaktifkan kembali.');
    }

    // JSON HISTORY TRANSAKSI
    public function transactionsJson($id)
    {
        $barang = Barang::findOrFail($id);

        $transaksi = Htrans::with(['details' => function ($q) use ($id) {
            $q->where('id_barang', $id);
        }, 'unit'])
            ->whereHas('details', fn($q) => $q->where('id_barang', $id))
            ->orderBy('tanggal', 'asc')
            ->orderBy('id_trans', 'asc')
            ->get();

        return response()->json([
            'data' => $transaksi->map(function ($t) {
                $d = $t->details->first();
                return [
                    'tanggal'    => $t->tanggal,
                    'no_surat'   => $t->no_surat,
                    'jenis'      => $t->jenis,
                    'qty'        => $d->qty ?? '-',
                    'unit_kerja' => $t->unit->unit_kerja ?? '-',
                ];
            }),
        ]);
    }

    // JSON PEMINJAMAN
    public function peminjamanJson($id)
    {
        $data = \App\Models\Peminjaman::with('user')
            ->where('id_barang', $id)
            ->orderBy('tanggal', 'desc')
            ->get();

        return response()->json([
            'data' => $data
        ]);
    }
}
