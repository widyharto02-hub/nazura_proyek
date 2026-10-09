<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Marketing extends Model
{
    use HasFactory;
    protected $table = 'marketing';
    protected $primaryKey = 'id_marketing';

    protected $fillable = [
        'kode_marketing',
        'nama_marketing',
        'jenis_kelamin',
        'alamat',
        'pekerjaan',
        'no_telp',
        'foto',
        'status',
        'nama_bank',
        'no_rekening',
        'atas_nama',
        'id_level',
        'stt_marketing',
    ];

    public $timestamps = false;

}
