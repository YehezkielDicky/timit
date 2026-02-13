<?php

namespace App\Http\Controllers;

use App\Models\UnitKerja;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UnitController extends Controller
{
    // LIST
    public function index(Request $request)
    {
        $q = trim((string) $request->get('q', ''));

        $data = UnitKerja::when($q, function ($w) use ($q) {
            $w->where(function ($x) use ($q) {
                $x->where('unit_kerja', 'like', "%{$q}%")
                    ->orWhere('lokasi', 'like', "%{$q}%");
            });
        })
            ->orderBy('id_unit', 'asc')
            ->get();

        return view('admin.unit.index', compact('data', 'q'));
    }

    // CREATE (pakai modal di index, jadi tidak dipakai)
    public function create()
    {
        abort(404);
    }

    // STORE
    public function store(Request $request)
    {
        $val = $request->validate([
            'unit_kerja' => [
                'required',
                'string',
                'max:100',
                // TABEL YANG BENAR: unit_kerja
                // Rule::unique('unit_kerja', 'unit_kerja'),
            ],
            'lokasi' => ['nullable', 'string', 'max:100'],
        ], [
            'unit_kerja.unique' => 'Nama unit sudah ada.',
        ]);

        UnitKerja::create($val);

        return redirect()->route('unit.index')->with('success', 'Unit kerja berhasil ditambahkan.');
    }

    // UPDATE
    public function update(Request $request, $id)
    {
        $unit = UnitKerja::findOrFail($id);

        $val = $request->validate([
            'unit_kerja' => [
                'required',
                'string',
                'max:100',
                // ignore record sekarang berdasarkan PK id_unit
                // Rule::unique('unit_kerja', 'unit_kerja')->ignore($unit->id_unit, 'id_unit'),
            ],
            'lokasi' => ['nullable', 'string', 'max:100'],
        ], [
            'unit_kerja.unique' => 'Nama unit sudah ada.',
        ]);

        $unit->update($val);

        return redirect()->route('unit.index')->with('success', 'Unit kerja berhasil diperbarui.');
    }

    // DESTROY
    public function destroy($id)
    {
        UnitKerja::findOrFail($id)->delete();
        return back()->with('success', 'Unit kerja berhasil dihapus.');
    }
}
