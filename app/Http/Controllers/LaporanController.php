<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Htrans;
use App\Models\UserLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Carbon;

class LaporanController extends Controller
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

    // ✅ Tampilkan laporan (filter tanggal / per bulan)
   public function index(Request $request)
    {
        $from  = $request->input('from');
        $to    = $request->input('to');
        $bulan = $request->input('bulan');
        $tahun = $request->input('tahun');
        $barangSearch = $request->input('barang');

        // ============= 1. OPENING BALANCE =============
        $beforeQuery = HTrans::with(['details'])
            ->when($from, fn($q) => $q->where('tanggal', '<', $from))
            ->when($barangSearch, fn($q) =>
                $q->whereHas('details.barang', fn($b) =>
                    $b->where('nama_barang', 'LIKE', "%$barangSearch%")
                )
            )
            ->get();

        $openings = [];
        foreach ($beforeQuery as $h) {
            foreach ($h->details as $d) {
                $id = $d->id_barang;

                $openings[$id] = ($openings[$id] ?? 0)
                    + ($h->jenis === 'masuk' ? $d->qty : -$d->qty);
            }
        }

        // ============= 2. TRANSAKSI PER PERIODE =============
        $query = HTrans::with(['details.barang', 'unit'])
            ->when($from && $to, fn($q) =>
                $q->whereBetween('tanggal', [$from, $to])
            )
            ->when(($bulan && $tahun) && !($from && $to), fn($q) =>
                $q->whereMonth('tanggal', $bulan)->whereYear('tanggal', $tahun)
            )
            ->when($barangSearch, fn($q) =>
                $q->whereHas('details.barang', fn($b) =>
                    $b->where('nama_barang', 'LIKE', "%$barangSearch%")
                )
            )
            ->orderBy('tanggal')
            ->orderBy('id_trans')
            ->get();

        // ============= 3. RUNNING BALANCE =============
        $running = [];
        foreach ($query as $h) {
            foreach ($h->details as $d) {

                $id = $d->id_barang;

                if (!isset($running[$id])) {
                    $running[$id] = $openings[$id] ?? 0;
                }

                $delta = $h->jenis === 'masuk' ? $d->qty : -$d->qty;
                $running[$id] += $delta;

                $d->running_balance = $running[$id];
            }
        }

        $barang = Barang::orderBy('nama_barang')->get();

        return view('admin.transaksi.laporan', compact(
            'query', 'from', 'to', 'bulan', 'tahun', 'barang', 'openings'
        ));
    }

    // ✅ Export Excel (filter per tanggal / bulan)
    public function export(Request $request){
    // ===============================
    // AMBIL FILTER
    // ===============================
    $fromInput = $request->input('from');
    $toInput   = $request->input('to');
    $bulan     = $request->input('bulan');
    $tahun     = $request->input('tahun');

    $from = $fromInput ? Carbon::parse($fromInput)->startOfDay() : null;
    $to   = $toInput ? Carbon::parse($toInput)->endOfDay() : null;

    // ===============================
    // QUERY DATA TRANSAKSI
    // ===============================
    $q = DB::table('h_trans as h')
        ->join('d_trans as d', 'h.id_trans', '=', 'd.id_trans')
        ->join('barang as b', 'b.id_barang', '=', 'd.id_barang')
        ->leftJoin('unit_kerja as u', 'u.id_unit', '=', 'h.id_unit')
        ->select(
            'd.id_barang',
            'b.nama_barang',
            'h.no_surat',
            'h.tanggal',
            'h.jenis',
            'd.qty',
            DB::raw('IF(h.jenis="masuk", d.qty, 0) as barang_masuk'),
            DB::raw('IF(h.jenis="keluar", d.qty, 0) as barang_keluar'),
            'u.unit_kerja',
            'b.qty as total_persediaan'
        );

    if ($from && $to) {
        $q->whereBetween('h.tanggal', [$from, $to]);
    } elseif ($bulan && $tahun) {
        $q->whereMonth('h.tanggal', $bulan)
          ->whereYear('h.tanggal', $tahun);
    }

    $rows = $q->orderBy('h.tanggal', 'asc')
              ->orderBy('h.id_trans', 'asc')
              ->get();

    // ===============================
    // HITUNG OPENING BALANCE
    // ===============================
    $barangIds = $rows->pluck('id_barang')->unique()->values()->all();

    $openings = [];
    if ($from) {
        $openingDB = DB::table('d_trans as d')
            ->join('h_trans as h', 'h.id_trans', '=', 'd.id_trans')
            ->select(
                'd.id_barang',
                DB::raw('SUM(IF(h.jenis="masuk", d.qty, -d.qty)) as saldo')
            )
            ->whereIn('d.id_barang', $barangIds)
            ->where('h.tanggal', '<', $from)
            ->groupBy('d.id_barang')
            ->pluck('saldo', 'id_barang')
            ->toArray();

        foreach ($barangIds as $id) {
            $openings[$id] = (int) ($openingDB[$id] ?? 0);
        }
    } else {
        foreach ($barangIds as $id) {
            $openings[$id] = 0;
        }
    }

    // ===============================
    // RUNNING BALANCE
    // ===============================
    $running = [];
    $exportRows = [];

    foreach ($rows as $r) {
        if (!isset($running[$r->id_barang])) {
            $running[$r->id_barang] = $openings[$r->id_barang];
        }

        $delta = $r->jenis === 'masuk'
            ? (int) $r->qty
            : -(int) $r->qty;

        $running[$r->id_barang] += $delta;

        $exportRows[] = [
            'nama_barang'      => $r->nama_barang,
            'no_surat'         => $r->no_surat,
            'tanggal'          => Carbon::parse($r->tanggal)->format('d/m/Y'),
            'jenis'            => ucfirst($r->jenis),
            'barang_masuk'     => $r->barang_masuk,
            'barang_keluar'    => $r->barang_keluar,
            'unit_kerja'       => $r->unit_kerja ?? '-',
            'total_persediaan' => $r->total_persediaan,
            'running_balance'  => $running[$r->id_barang],
        ];
    }

    // ===============================
    // BUAT EXCEL
    // ===============================
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    $headers = [
        'No', 'Nama Barang', 'No Surat', 'Tanggal', 'Jenis',
        'Barang Masuk', 'Barang Keluar', 'Unit Kerja',
        'Total Persediaan', 'Running Balance'
    ];

    $sheet->fromArray($headers, null, 'A1');

    $rowNum = 2;
    foreach ($exportRows as $i => $r) {
        $sheet->fromArray([
            $i + 1,
            $r['nama_barang'],
            $r['no_surat'],
            $r['tanggal'],
            $r['jenis'],
            $r['barang_masuk'],
            $r['barang_keluar'],
            $r['unit_kerja'],
            $r['total_persediaan'],
            $r['running_balance'],
        ], null, "A{$rowNum}");
        $rowNum++;
    }

    foreach (range('A', 'J') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }

    // ===============================
    // DOWNLOAD FILE (LARAVEL WAY)
    // ===============================
    $filename = 'Laporan_Transaksi_' . now()->format('Ymd_His') . '.xlsx';
    $path = storage_path("app/{$filename}");

    $writer = new Xlsx($spreadsheet);
    $writer->save($path);

    return response()->download($path, $filename)->deleteFileAfterSend(true);
}


    // ✅ Print PDF per bulan
    public function print(Request $request)
    {
        $bulan = $request->input('bulan');
        $tahun = $request->input('tahun');

        $query = Htrans::with(['details.barang', 'unit'])
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->orderBy('tanggal', 'asc')
            ->get();

        $barang = Barang::orderBy('nama_barang')->get();

        $pdf = Pdf::loadView('admin.transaksi.print', compact('query', 'bulan', 'tahun', 'barang'))
            ->setPaper('a4', 'landscape');

        return $pdf->stream("Laporan_Transaksi_{$bulan}_{$tahun}.pdf");
    }
}
