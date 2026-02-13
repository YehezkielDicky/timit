<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dtrans extends Model
{
    protected $table = 'd_trans';
    protected $primaryKey = 'id_dtrans';
    public $timestamps = false;

    protected $fillable = [
        'id_trans',
        'id_barang',
        'qty'
    ];

    public function barang()
    {
        return $this->belongsTo(Barang::class, 'id_barang');
    }
    public function header()
    {
        return $this->belongsTo(HTrans::class, 'id_trans');
    }
}
