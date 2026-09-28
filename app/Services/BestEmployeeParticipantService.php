<?php

namespace App\Services;

use App\Enums\EmployeeStatus;
use App\Models\BestEmployeeCampaign;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class BestEmployeeParticipantService
{
    public function __construct(private BestEmployeeActivityLogger $logger) {}

    public function filterOptions(): array
    {
        $col = fn (string $column) => Employee::query()
            ->where('status_karyawan', 'FIT')
            ->whereNotNull($column)
            ->where($column, '<>', '')
            ->distinct()
            ->orderBy($column)
            ->pluck($column);

        return [
            'jabatan' => $col('jabatan_ss6'),
            'unit' => $col('unit'),
            'lokasi' => $col('lokasi_kerja'),
        ];
    }

    /** Kandidat peserta (karyawan aktif) untuk modal Inject Peserta, dgn status assigned bila ada. */
    public function searchCandidates(BestEmployeeCampaign $campaign, array $filters, int $page, int $perPage = 20): array
    {
        $query = Employee::query()
            ->where('employees.status_karyawan', 'FIT')
            ->join('users', 'users.employee_id', '=', 'employees.id')
            ->leftJoin('training_be_participants', function ($join) use ($campaign) {
                $join->on('training_be_participants.user_id', '=', 'users.id')
                    ->where('training_be_participants.campaign_id', $campaign->id);
            })
            ->when($filters['q'] ?? null, fn ($q, $v) => $q->where(fn ($sub) => $sub->where('employees.nrp', 'like', "%{$v}%")->orWhere('employees.name', 'like', "%{$v}%")))
            ->when($filters['jabatan'] ?? null, fn ($q, $v) => $q->where('employees.jabatan_ss6', $v))
            ->when($filters['unit'] ?? null, fn ($q, $v) => $q->where('employees.unit', $v))
            ->when($filters['lokasi'] ?? null, fn ($q, $v) => $q->where('employees.lokasi_kerja', $v))
            ->select([
                'employees.nrp', 'employees.name', 'employees.jabatan_ss6', 'employees.unit', 'employees.lokasi_kerja',
                'users.id as user_id', 'training_be_participants.status as participant_status',
            ])
            ->orderBy('employees.name');

        $paginator = $query->paginate($perPage, ['*'], 'page', max(1, $page));

        return [
            'rows' => $paginator->items(),
            'total' => $paginator->total(),
            'page' => $paginator->currentPage(),
            'per_page' => $paginator->perPage(),
        ];
    }

    /** Semua user_id karyawan aktif yang cocok filter - dipakai "select all hasil filter". */
    public function resolveActiveUserIdsByFilter(array $filters): array
    {
        return Employee::query()
            ->where('employees.status_karyawan', 'FIT')
            ->join('users', 'users.employee_id', '=', 'employees.id')
            ->when($filters['q'] ?? null, fn ($q, $v) => $q->where(fn ($sub) => $sub->where('employees.nrp', 'like', "%{$v}%")->orWhere('employees.name', 'like', "%{$v}%")))
            ->when($filters['jabatan'] ?? null, fn ($q, $v) => $q->where('employees.jabatan_ss6', $v))
            ->when($filters['unit'] ?? null, fn ($q, $v) => $q->where('employees.unit', $v))
            ->when($filters['lokasi'] ?? null, fn ($q, $v) => $q->where('employees.lokasi_kerja', $v))
            ->pluck('users.id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    /** Assign satu peserta dari master user. Return 'ok' | 'not_found' | 'inactive'. */
    public function assign(BestEmployeeCampaign $campaign, int $userId, int $actorId): string
    {
        $user = User::with('employee')->find($userId);
        if (! $user) {
            return 'not_found';
        }
        if ($user->employee?->status_karyawan !== EmployeeStatus::Fit) {
            return 'inactive';
        }

        $campaign->participants()->updateOrCreate(
            ['user_id' => $userId],
            [
                'user_nrp' => $user->employee?->nrp ?? $user->username,
                'user_nama' => $user->name,
                'user_jabatan' => $user->employee?->jabatan_ss6 ?? $user->jabatan,
                'user_unit' => $user->employee?->unit ?? $user->unit,
                'status' => 'assigned',
                'assigned_by' => $actorId,
                'assigned_at' => now(),
                'removed_by' => null,
                'removed_at' => null,
            ]
        );

        return 'ok';
    }

    /** Assign banyak peserta sekaligus. */
    public function assignBulk(BestEmployeeCampaign $campaign, array $userIds, int $actorId): array
    {
        $result = ['assigned' => 0, 'already' => 0, 'inactive' => 0, 'not_found' => 0];

        DB::transaction(function () use ($campaign, $userIds, $actorId, &$result) {
            foreach (array_unique(array_map('intval', $userIds)) as $userId) {
                if ($userId <= 0) {
                    continue;
                }
                $existing = $campaign->participants()->where('user_id', $userId)->first();
                $outcome = $this->assign($campaign, $userId, $actorId);

                match (true) {
                    $outcome === 'ok' && $existing && $existing->status === 'assigned' => $result['already']++,
                    $outcome === 'ok' => $result['assigned']++,
                    $outcome === 'inactive' => $result['inactive']++,
                    default => $result['not_found']++,
                };
            }
        });

        $this->logger->log($campaign->id, $actorId, 'participant_assigned',
            "Inject peserta massal: {$result['assigned']} baru, {$result['already']} sudah ada, {$result['inactive']} nonaktif, {$result['not_found']} tidak ditemukan",
            $result);

        return $result;
    }

    public function remove(BestEmployeeCampaign $campaign, int $userId, int $actorId, ?string $reason = null): void
    {
        $campaign->participants()->where('user_id', $userId)->where('status', 'assigned')->update([
            'status' => 'removed',
            'removed_by' => $actorId,
            'removed_at' => now(),
        ]);

        $this->logger->log($campaign->id, $actorId, 'participant_removed', 'Remove peserta user_id='.$userId, [
            'target_user_id' => $userId,
            'forced' => $reason !== null,
            'reason' => $reason,
        ]);
    }
}
