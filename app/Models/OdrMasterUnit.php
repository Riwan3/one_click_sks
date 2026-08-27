<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OdrMasterUnit extends Model
{
    protected $table = 'odr_master_unit';
    protected $fillable = ['kode', 'nama', 'area', 'keterangan', 'aktif'];
}
