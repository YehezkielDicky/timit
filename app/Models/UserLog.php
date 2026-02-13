<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserLog extends Model
{
    protected $table = 'user_logs';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'activity',
        'module',
        'description',
        'ip_address',
        'user_agent',
        'created_at'
    ];

    public function user()
    {
        // FK = user_id, PK di users = id_user
        return $this->belongsTo(User::class, 'user_id', 'id_user');
    }
}
