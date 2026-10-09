<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PembayaranJasa extends Model
{
    use HasFactory;

    protected $table = 'pembayaran_jasa';
    public $timestamps = false;

    protected $fillable = [
        'id_setting_proyek',
        'id_master_project_detail',
        'id_jasa_custom',
        'id_tukang',
        'tanggal_pembayaran',
        'jumlah_bayar',
        'keterangan',
        'bukti_bayar',
        'bukti_bayar_original_name',
        'bukti_bayar_extension',
    ];

    public function settingProyek()
    {
        return $this->belongsTo(SettingProyek::class, 'id_setting_proyek');
    }

    public function jasaDetail()
    {
        return $this->belongsTo(MasterProjectDetail::class, 'id_master_project_detail');
    }

    public function jasaCustom()
    {
        return $this->belongsTo(JasaCustom::class, 'id_jasa_custom');
    }

    public function tukang()
    {
        return $this->belongsTo(Tukang::class, 'id_tukang');
    }
}
