<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\UserLog;
use App\Models\Htrans;
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
        $q = trim((string) $request->get('q', ''));

        $data = Barang::when($q, function ($w) use ($q) {
            $w->where('nama_barang', 'like', "%{$q}%");
        })
        ->orderBy('id_barang', 'asc')
        ->get();

        return view('admin.barang.index', compact('data', 'q'));
    }


    // FORM CREATE
    public function create(Request $request)
    {
        return view('admin.barang.create');
    }

    // SIMPAN
    public function store(Request $request)
    {
        $val = $request->validate([
            'nama_barang' => ['required', 'string', 'max:100', 'unique:barang,nama_barang'],
        ], [
            'nama_barang.unique' => 'Nama barang sudah ada.',
        ]);

        $barang = Barang::create($val);

        // LOG
        $this->writeLog($request, 'Create', 'Barang', "Tambah: {$barang->nama_barang} (id: {$barang->id_barang})");

        return redirect()->route('barang.index')->with('success', 'Barang berhasil ditambahkan.');
    }

    // FORM EDIT
    public function edit(Request $request, $id)
    {
        $barang = Barang::findOrFail($id);

        return view('admin.barang.edit', compact('barang'));
    }

    // UPDATE
    public function update(Request $request, $id)
    {
        $barang = Barang::findOrFail($id);

        $val = $request->validate([
            'nama_barang' => ['required', 'string', 'max:100', 'unique:barang,nama_barang,' . $barang->id_barang . ',id_barang'],
        ], [
            'nama_barang.unique' => 'Nama barang sudah ada.',
        ]);

        $barang->update($val);

        // LOG
        $this->writeLog($request, 'Update', 'Barang', "Update: {$barang->nama_barang} (id: {$barang->id_barang})");

        return redirect()->route('barang.index')->with('success', 'Barang berhasil diperbarui.');
    }

    // HAPUS
    public function destroy(Request $request, $id)
    {
        $barang = Barang::findOrFail($id);
        $nama   = $barang->nama_barang;
        $barang->delete();

        // LOG
        $this->writeLog($request, 'Delete', 'Barang', "Hapus: {$nama} (id: {$id})");

        return back()->with('success', 'Barang dihapus.');
    }

    //HiSTORY Barang
    public function transactionsJson($id)
    {
        // cek barangnya ada
        $barang = Barang::findOrFail($id);

        // ambil semua h_trans yang punya detail id_barang ini
        $transaksi = Htrans::with(['details' => function ($q) use ($id) {
            $q->where('id_barang', $id);
        }, 'unit'])
            ->whereHas('details', function ($q) use ($id) {
                $q->where('id_barang', $id);
            })
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
                    'qty'        => $d ? $d->qty : null,
                    'unit_kerja' => $t->unit->unit_kerja ?? '-',
                ];
            }),
        ]);
    }
    public function peminjamanJson($id)
    {
        $data = \App\Models\Peminjaman::with(['user'])
            ->where('id_barang', $id)
            ->orderBy('tanggal', 'desc')
            ->get();

        return response()->json([
            'data' => $data
        ]);
    }

}
