<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pengeluaran extends Model
{
    protected $table = 'pengeluaran';

    public $timestamps = false;

    protected $fillable = [
        'app_source',
        'id_project',
        'sumber',
        'ref_id',
        'tanggal',
        'nominal',
        'id_kategori_transaksi',
        'id_metode_bayar',
        'id_bank',
        'id_lokasi',
        'lampiran',
        'keterangan',
    ];

    public function kategori()
    {
        return $this->belongsTo(KategoriTransaksi::class, 'id_kategori_transaksi');
    }
}