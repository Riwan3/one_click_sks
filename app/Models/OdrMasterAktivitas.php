<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OdrMasterAktivitas extends Model
{
    protected $table = 'odr_master_aktivitas';
    protected $fillable = ['nama', 'kategori', 'keterangan', 'aktif'];
}
