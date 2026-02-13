<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Aset;

class AsetController extends Controller
{
    public function store(Request $r)
    {
        $r->validate([
            'nama_barang' => 'required',
            'jumlah' => 'required|integer|min:1',
            'status' => 'required',
            'kondisi' => 'required',
        ]);

        // Loop sebanyak jumlah yang dimasukkan
        for ($i = 0; $i < $r->jumlah; $i++) {
            Aset::create([
                'nama_barang' => $r->nama_barang,
                'status' => $r->status,
                'kondisi' => $r->kondisi,
                'keterangan' => $r->keterangan,
            ]);
        }

        return back()->with('success', 'Aset berhasil ditambahkan!');
    }
}
