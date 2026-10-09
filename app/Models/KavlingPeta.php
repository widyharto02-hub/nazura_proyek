<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class KavlingPeta extends Model
{
    use HasFactory;

    protected $table = 'kavling_peta';
    protected $primaryKey = 'id_kavling';

    protected $fillable = [
        'id_lokasi',
        'id_cluster',
        'kode_kavling',
        'panjang_kanan',
        'panjang_kiri',
        'lebar_depan',
        'lebar_belakang',
        'luas_tanah',
        'tipe_bangunan',
        'daya_listrik',
        'luas_bangunan',
        'hrg_meter',
        'hrg_jual',
        'id_rumah_sikumbang',
        'no_sertifikat',
        'jenis_map',
        'map',
        'matrik',
        'status',
        'keterangan',
        'atas_nama_surat',
        'id_customer',
        'tgl_jatuh_tempo',
        'tgl_jatuh_tempo',
        'stt_cicilan',
    ];

    public function lokasi()
    {
        return $this->belongsTo(LokasiKavling::class, 'id_lokasi');
    }

    public function kavling()
    {
        return $this->belongsTo(KavlingPeta::class, 'id_kavling');
    }

    public function settingProyek()
    {
        return $this->hasOne(SettingProyek::class, 'id_kavling', 'id_kavling');
    }

    public $timestamps = false;
}
