<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OdrMasterArea extends Model
{
    protected $table = 'odr_master_area';
    protected $fillable = ['nama', 'keterangan', 'aktif'];
}
