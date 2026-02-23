<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Barang extends Model
{
    protected $table = 'barang';
    protected $primaryKey = 'id_barang';
    public $timestamps = false;

    protected $fillable = [
        'nama_barang',
        'id_kategori',
        'qty',
        'status_barang',
    ];

    // Relasi ke detail transaksi
    public function details()
    {
        return $this->hasMany(DTrans::class, 'id_barang');
    }

    // Hitung stok saat ini berdasarkan mutasi (opsional)
    public function getStokAttribute()
    {
        $masuk = $this->details()
            ->whereHas('header', fn($q) => $q->where('jenis', 'masuk'))
            ->sum('qty');

        $keluar = $this->details()
            ->whereHas('header', fn($q) => $q->where('jenis', 'keluar'))
            ->sum('qty');

        return ($this->stok_awal ?? 0) + $masuk - $keluar;
    }
    public function kategori()
    {
        return $this->belongsTo(Kategori::class, 'id_kategori');
    }
}
