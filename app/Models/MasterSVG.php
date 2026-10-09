<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MasterSVG extends Model
{
    use HasFactory;

    protected $table = 'master_svg';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $fillable = [
        'id_lokasi',
        'header_xml',
        'header_svg',
        'polygon_svg',
        'path_svg',
        'footer_svg',
        'lebar',
        'tinggi',
        'ukuran_dashboard',
    ];
}
