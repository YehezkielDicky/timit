<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Aset extends Model
{
    protected $table = 'aset';
    protected $primaryKey = 'id_aset';

    protected $fillable = [
        'id_barang',
        'status',
        'kondisi',
        'keterangan'
    ];
}
