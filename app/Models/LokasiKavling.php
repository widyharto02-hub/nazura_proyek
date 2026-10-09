<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LokasiKavling extends Model
{
    use HasFactory;

    protected $table = 'lokasi_kavling';

    protected $primaryKey = 'id_lokasi';

    public $timestamps = false;

    protected $fillable = [
        'id_lokasi',
        'nama_kavling',
        'alamat',
        'urutan',
        'nama_perusahaan',
        'alamat_perusahaan',
        'telp_perusahaan',
        'bg_kwitansi',
        'kop_surat',
        'kota_penandatangan',
        'nama_penandatangan',
        'jabatan_penandatangan',
    ];

    public function kavlingPeta()
    {
        return $this->hasMany(KavlingPeta::class, 'id_lokasi');
    }

    public function masterSvg()
    {
        return $this->hasOne(MasterSVG::class, 'id_lokasi');
    }
}
