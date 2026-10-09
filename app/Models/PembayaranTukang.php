<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PembayaranTukang extends Model
{
    use HasFactory;

    protected $table = 'pembayaran_tukang';
    public $timestamps = false;

    protected $fillable = [
        'tanggal',
        'id_tukang',
        'nama_pekerjaan',
        'keterangan',
        'nominal',
        'bukti_bayar',
        'bukti_bayar_original_name',
        'bukti_bayar_extension',
    ];

    public function tukang()
    {
        return $this->belongsTo(Tukang::class, 'id_tukang');
    }
}
