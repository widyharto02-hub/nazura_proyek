<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Aktifitas extends Model
{
    protected $table = 'aktifitas';

    public $timestamps = false;

    protected $fillable = [
        'id_user',
        'aktivitas',
        'keterangan',
        'method',
        'url',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user');
    }
}
