<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProgresListPembangunan extends Model
{
    use HasFactory;
    protected $table = 'progres_list_pembangunan';
    public $timestamps = false;
    protected $primaryKey = 'id_list_pembangunan';
}
