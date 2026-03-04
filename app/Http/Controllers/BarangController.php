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

    public function printAll()
    {
        $aktif = Barang::with('kategori')
            ->where('status_barang','aktif')
            ->orderBy('nama_barang','asc')
            ->get();

        $nonaktif = Barang::with('kategori')
            ->where('status_barang','nonaktif')
            ->orderBy('nama_barang','asc')
            ->get();

        $html = '
        <html>
        <head>
            <title>Print Daftar Barang</title>
            <style>
                body{font-family:Arial;padding:20px}
                h2{text-align:center;margin-bottom:20px}
                h3{margin-top:30px}
                table{width:100%;border-collapse:collapse;margin-top:10px}
                th,td{border:1px solid #000;padding:6px;text-align:center}
                th{background:#eee}
            </style>
        </head>
        <body>

        <h2>Daftar Barang Inventaris</h2>

        <h3>Barang Aktif</h3>
        <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Barang</th>
                <th>Kategori</th>
                <th>Qty</th>
            </tr>
        </thead>
        <tbody>
        ';

        foreach($aktif as $i => $b){

            $kategori = $b->kategori->nama_kategori ?? '-';

            $html .= "
            <tr>
                <td>".($i+1)."</td>
                <td>{$b->nama_barang}</td>
                <td>{$kategori}</td>
                <td>{$b->qty}</td>
            </tr>";
        }

        $html .= '
        </tbody>
        </table>

        <h3>Barang Nonaktif</h3>
        <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Barang</th>
                <th>Kategori</th>
                <th>Qty</th>
            </tr>
        </thead>
        <tbody>
        ';

        foreach($nonaktif as $i => $b){

            $kategori = $b->kategori->nama_kategori ?? '-';

            $html .= "
            <tr>
                <td>".($i+1)."</td>
                <td>{$b->nama_barang}</td>
                <td>{$kategori}</td>
                <td>{$b->qty}</td>
            </tr>";
        }

        $html .= '
        </tbody>
        </table>

        <script>
            window.onload = function(){
                window.print();
            }
        </script>

        </body>
        </html>
        ';

        return response($html);
    }

    // LIST
    public function index(Request $request)
    {
        $q = trim($request->get('q',''));
        $kategori = $request->get('kategori');

        $data = Barang::where('status_barang','aktif')

            ->when($q, function($w) use ($q){
                $w->where('nama_barang','like',"%{$q}%");
            })

            ->when($kategori, function($w) use ($kategori){
                $w->where('id_kategori',$kategori);
            })

            ->with('kategori')
            ->orderBy('id_barang','asc')
            ->paginate(10,['*'],'aktif_page');

        $nonaktif = Barang::where('status_barang','nonaktif')

            ->when($q, function($w) use ($q){
                $w->where('nama_barang','like',"%{$q}%");
            })

            ->when($kategori, function($w) use ($kategori){
                $w->where('id_kategori',$kategori);
            })

            ->with('kategori')
            ->orderBy('id_barang','asc')
            ->paginate(10,['*'],'nonaktif_page');

        $kategori = Kategori::orderBy('nama_kategori')->get();

        return view('admin.barang.index', compact(
            'data','nonaktif','q','kategori'
        ));
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

    public function printRiwayat($id)
    {
        $barang = Barang::findOrFail($id);

        $transaksi = Htrans::with(['details' => function ($q) use ($id) {
            $q->where('id_barang', $id);
        }, 'unit'])
        ->whereHas('details', fn($q) => $q->where('id_barang', $id))
        ->orderBy('tanggal','asc')
        ->get();

        $peminjaman = \App\Models\Peminjaman::with('user')
            ->where('id_barang',$id)
            ->orderBy('tanggal','desc')
            ->get();

        return view('admin.barang.print-riwayat', compact(
            'barang',
            'transaksi',
            'peminjaman'
        ));
    }
    
}
