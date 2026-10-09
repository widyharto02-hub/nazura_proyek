<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory;

    protected $table = 'users_proyek';

    protected $fillable = [
        'surname',
        'username',
        'password',
        'email',
        'status',
        'role',
        'user_from',
    ];

    public $timestamps = false;
}
