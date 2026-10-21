<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OpdPengajuanGrade extends Model
{
    public const TERMINS = [1, 2, 3, 4];

    public const REMARK_COLORS = ['merah', 'kuning', 'hijau'];

    /** Satu-satunya kolom yang diisi manual di site => panjang maksimum. Remark dari OneClick HO (API), sisanya dari Monitoring Grade. */
    public const EDITABLE_FIELDS = ['pos_tujuan' => 150, 'grade_tujuan' => 50, 'mor' => 50, 'sp' => 50];

    /** Kolom pengajuan => kolom Monitoring Grade (odp_mg_grade_v4) yang ditarik otomatis. */
    public const MONITORING_COLUMNS = [
        'site' => 'sub_area',
        'doh' => 'join_date',
        'masa_kerja' => 'masa_kerja',
        'pos_saat_ini' => 'posisi',
        'pos_tgl_naik' => 'efektif_position',
        'pos_lama_naik' => 'masa_kerja_posisi_sekarang',
        'grade_saat_ini' => 'grade_now',
        'grade_tgl_naik' => 'efektif_grade',
        'grade_lama_naik' => 'masa_kerja_grade_sekarang',
        'vers_saat_ini' => 'jumlah_versatility',
        'vers_seharusnya' => 'requirement_versatility',
        'atr' => 'atr_6_bulan',
    ];

    protected $table = 'opd_pengajuan_grade';

    protected $fillable = [
        'nrp', 'tahun', 'termin', 'nama',
        'site', 'doh', 'masa_kerja',
        'pos_saat_ini', 'pos_tgl_naik', 'pos_lama_naik', 'pos_tujuan',
        'grade_saat_ini', 'grade_tgl_naik', 'grade_lama_naik', 'grade_tujuan',
        'vers_saat_ini', 'vers_seharusnya', 'mor', 'atr', 'sp',
        'remark', 'remark_warna', 'updated_by',
    ];

    protected function casts(): array
    {
        return ['tahun' => 'integer', 'termin' => 'integer'];
    }
}
