<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Kategori;

class KategoriController extends Controller
{
    public function storeAjax(Request $request)
    {
        $request->validate([
            'nama_kategori' => 'required|max:100|unique:kategori,nama_kategori'
        ]);

        $kategori = Kategori::create([
            'nama_kategori' => $request->nama_kategori
        ]);

        return response()->json([
            'status' => 'ok',
            'data' => $kategori   // ← WAJIB! DAN BENAR!
        ]);
    }

    public function listAjax()
    {
        return response()->json([
            'status' => 'ok',
            'data' => Kategori::orderBy('nama_kategori')->get()
        ]);
    }

    public function updateAjax(Request $request, $id)
    {
        $kategori = Kategori::findOrFail($id);

        $request->validate([
            'nama_kategori' => 'required|max:100|unique:kategori,nama_kategori,' . $id . ',id_kategori'
        ]);

        $kategori->nama_kategori = $request->nama_kategori;
        $kategori->save();

        return response()->json(['status' => 'ok']);
    }

    public function deleteAjax($id)
    {
        Kategori::where('id_kategori', $id)->delete();

        return response()->json(['status' => 'ok']);
    }
}
