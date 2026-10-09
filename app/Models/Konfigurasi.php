<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Konfigurasi extends Model
{
    protected $table = 'konfigurasi';
    public $timestamps = false;

    protected $fillable = [
        'nama_perusahaan',
        'alamat',
        'email',
        'telp',
        'kota_penandatanganan',
        'nama_penandatangan_1',
        'jabatan_penandatangan_1',
    ];
}
