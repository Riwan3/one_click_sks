<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ProsedurKerja extends Model
{
    use HasFactory;

    protected $table = 'prosedur_kerja';

    protected $fillable = [
        'jenis',
        'kode_dokumen',
        'judul',
        'tanggal',
        'file_dokumen',
        'dibuat_oleh',
        'jabatan_dibuat',
        'tanggal_dibuat',
        'ttd_dibuat',
        'ditinjau_oleh',
        'jabatan_ditinjau',
        'tanggal_ditinjau',
        'ttd_ditinjau',
        'disetujui_oleh',
        'jabatan_disetujui',
        'tanggal_disetujui',
        'ttd_disetujui',
        'status',
        'created_by',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'tanggal_dibuat' => 'date',
        'tanggal_ditinjau' => 'date',
        'tanggal_disetujui' => 'date',
    ];

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(
            ProsedurCategory::class,
            'prosedur_category_docs',
            'prosedur_id',
            'category_id'
        );
    }
}
