<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Htrans extends Model
{
    protected $table = 'h_trans';
    protected $primaryKey = 'id_trans';
    public $timestamps = false;

    protected $fillable = [
        'no_surat',
        'jenis',
        'tanggal',
        'id_unit',
        'keterangan',
        'tanda_terima',
        'berita_acara',
    ];

    public function details()
    {
        return $this->hasMany(DTrans::class, 'id_trans');
    }
    public function unit()
    {
        return $this->belongsTo(\App\Models\UnitKerja::class, 'id_unit');
    }
}
