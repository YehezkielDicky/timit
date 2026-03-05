<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Dtrans;
use App\Models\Htrans;
use App\Models\UnitKerja;
use App\Models\UserLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class TransaksiController extends Controller
{
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

    public function index(Request $request)
    {
        $from = $request->input('from');
        $to   = $request->input('to');
        $no_surat = $request->input('no_surat');

        $sort  = $request->input('sort');      // no_surat
        $order = $request->input('order', 'asc'); // asc/desc

        $transaksi = HTrans::with(['details.barang', 'unit'])
            ->when($from, fn($q) => $q->whereDate('tanggal', '>=', $from))
            ->when($to, fn($q) => $q->whereDate('tanggal', '<=', $to))
            ->when($no_surat, fn($q) => $q->where('no_surat', 'LIKE', "%$no_surat%"))

            // 🔥 SORTING gabungan nomor urut + romawi + tahun
            ->when($sort === 'no_surat', function($q) use ($order) {

                // Nomor urut
                $nomorUrut = "CAST(SUBSTRING_INDEX(no_surat, '/', 1) AS UNSIGNED)";

                // Bulan romawi
                $romawi = "SUBSTRING_INDEX(SUBSTRING_INDEX(no_surat, '/', 3), '/', -1)";

                // Convert romawi to ordered month
                $romawiOrder = "
                    FIELD(
                        $romawi,
                        'I','II','III','IV','V','VI',
                        'VII','VIII','IX','X','XI','XII'
                    )
                ";

                // Tahun (bagian terakhir)
                $tahun = "CAST(SUBSTRING_INDEX(no_surat, '/', -1) AS UNSIGNED)";

                // Urut lengkap
                $q->orderByRaw("$tahun $order")
                ->orderByRaw("$romawiOrder $order")
                ->orderByRaw("$nomorUrut $order");
            })

            // Default (jika tidak klik sort)
            ->when(!$sort, fn($q) =>
                $q->orderBy('tanggal', 'desc')->orderBy('id_trans', 'desc')
            )

            ->paginate(20);

        $transaksi->appends($request->query());

        $barang = Barang::orderBy('nama_barang')->get();
        $units  = UnitKerja::orderBy('unit_kerja')->get();

        return view('admin.transaksi.index', compact('transaksi', 'barang', 'units'));
    }


    // ✅ Form tambah
    public function create()
    {
        $barang = Barang::orderBy('nama_barang')->get();
        $units  = UnitKerja::orderBy('unit_kerja')->get();
        return view('admin.transaksi.create', compact('barang', 'units'));
    }

    private function sanitizeFileName($name)
    {
        // hapus karakter ilegal untuk nama file
        $name = str_replace(['/', '\\', ':', '*', '?', '"', '<', '>', '|'], '-', $name);

        // ganti spasi menjadi underscore
        $name = preg_replace('/\s+/', '_', $name);

        return $name;
    }

    private function generateNoSurat($jenis)
    {
        $now   = now();
        $tahun = $now->year;
        $bulan = $now->month;

        // Convert bulan ke romawi
        $romawi = [
            1=>'I',2=>'II',3=>'III',4=>'IV',5=>'V',6=>'VI',
            7=>'VII',8=>'VIII',9=>'IX',10=>'X',11=>'XI',12=>'XII'
        ];
        $bulanRomawi = $romawi[$bulan];

        // Mapping kode surat
        $kode = match($jenis) {
            'masuk'  => 'SM',
            'keluar' => 'SK',
            default  => 'SM'
        };

        // Ambil nomor terakhir bulan ini + jenis ini
        $last = HTrans::whereYear('tanggal', $tahun)
            ->whereMonth('tanggal', $bulan)
            ->where('jenis', $jenis)
            ->orderBy('id_trans', 'desc')
            ->first();

        $noUrut = 1;

        if ($last) {
            $lastNumber = (int) explode('/', $last->no_surat)[0];
            $noUrut = $lastNumber + 1;
        }

        $noUrut = str_pad($noUrut, 2, '0', STR_PAD_LEFT);

        return "{$noUrut}/WM_IT/{$kode}/{$bulanRomawi}/{$tahun}";
    }

    public function store(Request $request)
    {
        $request->validate([
            'no_surat' => 'string|max:50|unique:h_trans,no_surat',
            'jenis'    => 'required|in:masuk,keluar',
            'tanggal'  => 'required|date',
            'id_unit'  => 'nullable|integer',
            'keterangan' => 'nullable|string|max:255',

            'items.*.id_barang' => 'required|integer|exists:barang,id_barang',
            'items.*.qty'       => 'required|integer|min:1',

            // validasi multi file
            'dokumen.*' => 'file|mimes:pdf,doc,docx|max:3072',
        ]);

        DB::transaction(function () use ($request) {

            $ttPath = null;
            $baPath = null;

            // ======================
            //  PROSES FILE UPLOAD
            // ======================
            if ($request->hasFile('dokumen')) {
                foreach ($request->file('dokumen') as $file) {

                    $original = $file->getClientOriginalName();
                    $lower    = strtolower($original);

                    // pastikan nama file aman
                    $safeName = $this->sanitizeFileName($original);

                    // DETEKSI FILE
                    if (!$baPath && str_contains($lower, 'ba')) {
                        $baPath = $file->storeAs(
                            'transaksi/berita_acara',
                            $safeName,
                            'public'
                        );
                    }
                    elseif (!$ttPath && str_contains($lower, 'tt')) {
                        $ttPath = $file->storeAs(
                            'transaksi/tanda_terima',
                            $safeName,
                            'public'
                        );
                    }
                }
            }

            // ======================
            //  SIMPAN HEADER
            // ======================
            $noSurat = $this->generateNoSurat($request->jenis);
            $payload = $request->only(['jenis', 'tanggal', 'id_unit', 'keterangan']);
            $payload['no_surat'] = $noSurat;
            $payload['tanda_terima'] = $ttPath;
            $payload['berita_acara'] = $baPath;

            $h = HTrans::create($payload);

            // ======================
            //  DETAIL + PERUBAHAN STOK
            // ======================
            foreach ($request->items as $item) {

                $barang = Barang::lockForUpdate()->find($item['id_barang']);

                if ($request->jenis === 'keluar' && $barang->qty < $item['qty']) {
                    throw new \Exception("Stok {$barang->nama_barang} tidak mencukupi.");
                }

                DTrans::create([
                    'id_trans'  => $h->id_trans,
                    'id_barang' => $item['id_barang'],
                    'qty'       => $item['qty'],
                ]);

                $barang->qty += ($request->jenis === 'masuk')
                    ? $item['qty']
                    : -$item['qty'];

                $barang->save();
            }

            // LOG
            $this->writeLog(
                $request,
                'Update',
                'Transaksi',
                "Edit Transaksi NoSurat: {$h->no_surat}"
            );
        });

        return redirect()->route('transaksi.index')->with('success', 'Transaksi berhasil ditambahkan.');
    }

    public function generateNoSuratAjax(Request $request)
    {
        $jenis   = $request->jenis;
        $tanggal = $request->tanggal;

        if (!$jenis || !$tanggal) {
            return response()->json(['no_surat' => '']);
        }

        $date  = \Carbon\Carbon::parse($tanggal);
        $tahun = $date->year;
        $bulan = $date->month;

        // Konversi bulan ke romawi
        $romawi = [
            1=>'I',2=>'II',3=>'III',4=>'IV',5=>'V',6=>'VI',
            7=>'VII',8=>'VIII',9=>'IX',10=>'X',11=>'XI',12=>'XII'
        ];

        $bulanRomawi = $romawi[$bulan];

        // kode surat
        $kode = $jenis === 'masuk' ? 'SM' : 'SK';

        // ambil nomor terakhir berdasarkan bulan, tahun dan jenis
        $last = HTrans::whereYear('tanggal', $tahun)
            ->whereMonth('tanggal', $bulan)
            ->where('jenis', $jenis)
            ->orderBy('id_trans','desc')
            ->first();

        $noUrut = 1;

        if ($last) {
            $lastNumber = (int) explode('/', $last->no_surat)[0];
            $noUrut = $lastNumber + 1;
        }

        $noUrut = str_pad($noUrut, 2, '0', STR_PAD_LEFT);

        $noSurat = "{$noUrut}/WM_IT/{$kode}/{$bulanRomawi}/{$tahun}";

        return response()->json([
            'no_surat' => $noSurat
        ]);
    }

    // ✅ Edit transaksi
    public function edit($id)
    {
        $trans = HTrans::with('details')->findOrFail($id);
        $barang = Barang::all();
        $units  = UnitKerja::orderBy('unit_kerja')->get();
        return view('admin.transaksi.edit', compact('trans', 'barang', 'units'));
    }

    // ✅ Update transaksi
    public function update(Request $request, $id)
    {
        $request->validate([
            'no_surat' => 'required|string|max:50',
            'jenis'    => 'required|in:masuk,keluar',
            'tanggal'  => 'required|date',
            'id_unit'  => 'nullable|integer',
            'keterangan' => 'nullable|string|max:255',

            'items.*.id_barang' => 'required|integer|exists:barang,id_barang',
            'items.*.qty'       => 'required|integer|min:1',

            'dokumen.*' => 'file|mimes:pdf,doc,docx|max:3072',
        ]);

        DB::transaction(function () use ($request, $id) {

            $h = HTrans::with('details')->findOrFail($id);

            // ======================
            //  ROLLBACK STOK LAMA
            // ======================
            foreach ($h->details as $d) {
                $barang = Barang::lockForUpdate()->find($d->id_barang);

                $barang->qty += ($h->jenis === 'masuk')
                    ? -$d->qty
                    : +$d->qty;

                $barang->save();
            }

            // ======================
            //  HANDLE FILE BARU
            // ======================
            $ttPath = $h->tanda_terima;
            $baPath = $h->berita_acara;

            if ($request->hasFile('dokumen')) {
                foreach ($request->file('dokumen') as $file) {

                    $original = $file->getClientOriginalName();
                    $lower    = strtolower($original);

                    // Nama file aman
                    $safeName = $this->sanitizeFileName($original);

                    // DETEKSI & SIMPAN FILE BARU
                    if (str_contains($lower, 'ba')) {

                        if ($baPath) Storage::disk('public')->delete($baPath);

                        $baPath = $file->storeAs(
                            'transaksi/berita_acara',
                            $safeName,
                            'public'
                        );
                    }
                    elseif (str_contains($lower, 'tt')) {

                        if ($ttPath) Storage::disk('public')->delete($ttPath);

                        $ttPath = $file->storeAs(
                            'transaksi/tanda_terima',
                            $safeName,
                            'public'
                        );
                    }
                }
            }

            // ======================
            //  UPDATE HEADER
            // ======================
            $payload = $request->only(['no_surat', 'jenis', 'tanggal', 'id_unit', 'keterangan']);
            $payload['tanda_terima'] = $ttPath;
            $payload['berita_acara'] = $baPath;
            $h->update($payload);

            // ======================
            //  REPLACE DETAIL
            // ======================
            DTrans::where('id_trans', $h->id_trans)->delete();

            foreach ($request->items as $item) {

                $barang = Barang::lockForUpdate()->find($item['id_barang']);

                if ($request->jenis === 'keluar' && $barang->qty < $item['qty']) {
                    throw new \Exception("Stok {$barang->nama_barang} tidak cukup.");
                }

                DTrans::create([
                    'id_trans'  => $h->id_trans,
                    'id_barang' => $item['id_barang'],
                    'qty'       => $item['qty'],
                ]);

                $barang->qty += ($request->jenis === 'masuk')
                    ? $item['qty']
                    : -$item['qty'];

                $barang->save();
            }

            // LOG
            $this->writeLog(
                $request,
                'Update',
                'Transaksi',
                "Edit Transaksi NoSurat: {$h->no_surat}"
            );
        });

        return redirect()->route('transaksi.index')->with('success', 'Transaksi berhasil diperbarui.');
    }



    // ✅ Hapus transaksi
    public function destroy(Request $request, $id)
    {
        DB::transaction(function () use ($request, $id) {
            $h = HTrans::with('details')->findOrFail($id);

            foreach ($h->details as $d) {
                $barang = Barang::lockForUpdate()->find($d->id_barang);
                if ($barang) {
                    $barang->qty += ($h->jenis === 'masuk' ? -$d->qty : +$d->qty);
                    $barang->save();
                }
            }

            $noSurat = $h->no_surat;
            $jenis   = $h->jenis;

            // hapus file kalau ada
            if ($h->tanda_terima) Storage::disk('public')->delete($h->tanda_terima);
            if ($h->berita_acara)  Storage::disk('public')->delete($h->berita_acara);

            $h->delete();

            $this->writeLog(
                $request,
                'Update',
                'Transaksi',
                "Edit Transaksi NoSurat: {$h->no_surat}"
            );
        });

        return back()->with('success', 'Transaksi berhasil dihapus.');
    }
}
