<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketLog extends Model
{
    protected $table = 'ticket_logs';
    protected $primaryKey = 'id_log';
    public $timestamps = false;

    protected $fillable = [
        'id_ticket',
        'id_staff',
        'status_from',
        'status_to',
        'catatan',
    ];

    public function ticket()
    {
        return $this->belongsTo(Ticket::class, 'id_ticket', 'id_ticket');
    }

    public function staff()
    {
        return $this->belongsTo(User::class, 'id_staff', 'id_user');
    }
}