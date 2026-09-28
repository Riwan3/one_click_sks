<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class OdrAssignment extends Model
{
    public const SHIFTS = ['Shift 1', 'Shift 2'];

    public const MAX_TASKS = 5;

    protected $table = 'odr_assignments';

    protected $fillable = [
        'tanggal', 'shift', 'area', 'unit', 'pengawas',
        'aktivitas', 'task_done', 'keterangan', 'status',
        'gmp', 'inspeksi', 'high_risk', 'gl_synced',
    ];

    /** Daftar task dari kolom aktivitas (JSON array, atau teks tunggal data lama). */
    public function tasks(): array
    {
        if (! $this->aktivitas) return [];
        $decoded = json_decode($this->aktivitas, true);

        return is_array($decoded) ? array_values($decoded) : [$this->aktivitas];
    }

    /** Flag selesai per task, panjangnya selalu sama dengan jumlah task. */
    public function doneFlags(): array
    {
        $decoded = json_decode((string) $this->task_done, true);
        $decoded = is_array($decoded) ? $decoded : [];

        return array_map(fn ($i) => ! empty($decoded[$i]), array_keys($this->tasks()));
    }

    /** Persentase & status compliance dari checklist task (belum / progress / selesai). */
    public function compliance(): array
    {
        $total = count($this->tasks());
        $done = count(array_filter($this->doneFlags()));

        return [
            'pct' => $total > 0 ? (int) round($done / $total * 100) : 0,
            'done' => $done,
            'total' => $total,
            'status' => match (true) {
                $total === 0, $done === 0 => 'belum',
                $done === $total => 'selesai',
                default => 'progress',
            },
        ];
    }

    /** Persentase task selesai dengan 1 desimal (dipakai Laporan & Rekap Compliance v1). */
    public function compliancePercent(): float
    {
        ['done' => $done, 'total' => $total] = $this->compliance();

        return $total > 0 ? round($done / $total * 100, 1) : 0.0;
    }

    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->whereDate('tanggal', $filters['tanggal'])
            ->when($filters['shift'] ?? '', fn ($q, $v) => $q->where('shift', $v))
            ->when($filters['area'] ?? '', fn ($q, $v) => $q->where('area', $v))
            ->when($filters['status'] ?? '', fn ($q, $v) => $q->where('status', $v))
            ->when($filters['pengawas'] ?? '', fn ($q, $v) => $q->where('pengawas', 'like', "%{$v}%"))
            ->orderBy('area')
            ->orderBy('pengawas');
    }
}
