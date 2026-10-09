<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KategoriTransaksi extends Model
{
    protected $table = 'kategori_transaksi';

    public $timestamps = false;

    protected $fillable = [
        'kode',
        'kategori',
        'jenis_kategori',
        'stt_fix',
        'app_source',
    ];
}