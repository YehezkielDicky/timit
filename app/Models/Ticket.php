<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    protected $table = 'tickets';
    protected $primaryKey = 'id_ticket';

    protected $fillable = [
        'ticket_number',
        'judul',
        'deskripsi',
        'nama_pelapor',
        'kontak_pelapor',
        'id_unit',
        'status',
        'id_staff',
    ];

    public function unit()
    {
        return $this->belongsTo(UnitKerja::class, 'id_unit', 'id_unit');
    }

    public function staff()
    {
        return $this->belongsTo(User::class, 'id_staff', 'id_user');
    }

    public function logs()
    {
        return $this->hasMany(TicketLog::class, 'id_ticket', 'id_ticket');
    }
}