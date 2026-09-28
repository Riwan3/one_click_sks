<?php

namespace App\Models;

use App\Models\Concerns\HasHashid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class FatigueInterview extends Model
{
    use HasHashid;
    // NOTE: Future migration needed for V1 parity snapshot columns:
    // - nrp_operator (denormalisasi dari employee.nrp saat create)
    // - nama_operator (denormalisasi dari employee.name saat create)
    // - nrp_pengawas (denormalisasi dari employee.nrp saat create)
    // - nama_pengawas (denormalisasi dari employee.name saat create)
    // Kolom snapshot belum ada di DB, akan ditambahkan di migration mendatang.
    // Service layer akan auto-snapshot nama/nrp saat columns tersedia.
    protected $fillable = [
        'doc_rank',
        'no_surat',
        'employee_id',
        'nomor_unit',
        'area',
        'pengawas_id',
        'tanggal',
        'jam',
        'q1_shift_day',
        'q2_body_condition',
        'q3_reason',
        'q3_background',
        'q4_rest_hours',
        'q5_rest_quality',
        'q5_bad_quality_reason',
        'q6_home_comfort',
        'q6_discomfort_reason',
        'q7_other_activities',
        'q7_activity_details',
        'q8_education',
        'q9_heavy_fatigue_day',
        'q10_fit_plan',
        'q11_suggestions',
        'ttd_pengawas',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
        ];
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function pengawas()
    {
        return $this->belongsTo(Employee::class, 'pengawas_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function scopeSearch(Builder $query, ?string $keyword): Builder
    {
        if (! $keyword) {
            return $query;
        }

        return $query->where(
            fn (Builder $q) => $q->where('no_surat', 'like', "%{$keyword}%")
                ->orWhereHas('employee', fn (Builder $eq) => $eq->where('nrp', 'like', "%{$keyword}%")->orWhere('name', 'like', "%{$keyword}%"))
        );
    }

    public function scopeDateBetween(Builder $query, ?string $from, ?string $to): Builder
    {
        return $query
            ->when($from, fn (Builder $q) => $q->whereDate('tanggal', '>=', $from))
            ->when($to, fn (Builder $q) => $q->whereDate('tanggal', '<=', $to));
    }

    public static function nextDocRank(string $tanggal): array
    {
        $year = date('Y', strtotime($tanggal));
        $month = (int) date('n', strtotime($tanggal));

        $docRank = (int) static::query()->whereYear('tanggal', $year)->max('doc_rank') + 1;

        $monthRomans = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];
        $siteCode = Setting::getValue('fatigue_interview_site_code', 'ADW') ?: 'ADW';
        $noSurat = sprintf('%03d', $docRank).'/FI/PPA/'.$siteCode.'/PRO/'.$monthRomans[$month - 1].'/'.$year;

        return [$docRank, $noSurat];
    }

    public static function updateSiteCode(string $newSiteCode): void
    {
        $rows = static::query()->whereNotNull('no_surat')->get();
        foreach ($rows as $row) {
            $updated = preg_replace('#(/FI/PPA/)[^/]+(/PRO/)#i', '$1'.$newSiteCode.'$2', $row->no_surat);
            if ($updated !== $row->no_surat) {
                $row->no_surat = $updated;
                $row->save();
            }
        }
    }
}
