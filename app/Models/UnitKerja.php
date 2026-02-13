<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UnitKerja extends Model
{
    protected $table = 'unit_kerja';
    protected $primaryKey = 'id_unit';
    public $timestamps = false;

    protected $fillable = ['unit_kerja', 'lokasi'];
}
