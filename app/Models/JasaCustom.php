<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JasaCustom extends Model
{
    use HasFactory;

    protected $table = 'jasa_custom';

    protected $fillable = [
        'id_setting_proyek',
        'nama_jasa',
        'satuan',
        'harga_satuan',
        'keterangan',
    ];

    public function settingProyek()
    {
        return $this->belongsTo(SettingProyek::class, 'id_setting_proyek');
    }

    public function pembayaranJasa()
    {
        return $this->hasMany(PembayaranJasa::class, 'id_jasa_custom');
    }
}
