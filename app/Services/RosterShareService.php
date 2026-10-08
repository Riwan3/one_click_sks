<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\RosterV2Repository;
use Throwable;

/** Data "Share Roster": lembar roster pribadi satu karyawan dari roster Roster V2 berstatus PUBLISHED. */
class RosterShareService
{
    /** NRP dengan akses admin penuh (sama seperti V1 AuthMiddleware). */
    private const FULL_ADMIN_NRPS = ['260104', '260110', '230256'];

    private const MANAGEMENT_JABATAN = ['MANPOWER', 'DEPT HEAD', 'SECTION HEAD', 'MANAGER', 'ADMIN'];

    private const DEFAULT_PIC = ['nama' => 'Radhitya HS', 'jabatan' => 'Group Leader Manpower'];

    public function __construct(private RosterV2Repository $repository) {}

    /**
     * Admin (definisi V1: admin/demo/master, pimpinan, Dept/Section Head, Manager, MANPOWER, Admin) boleh memilih karyawan mana pun
     * dan selalu melihat semua bulan; role lain hanya melihat roster miliknya pada bulan yang dibuka PIC.
     */
    public function canPickEmployee(User $user): bool
    {
        $role = strtolower((string) $user->role);
        $jabatan = strtoupper((string) $user->jabatan);
        $isPrivilegedRole = in_array($role, ['demo', 'master', 'pimpinan', 'admin'], true);
        $isPrivilegedNrp = $user->username === 'admin' || in_array($user->username, self::FULL_ADMIN_NRPS, true);

        return $isPrivilegedRole || $isPrivilegedNrp || $this->jabatanMatches($jabatan);
    }

    public function build(User $user, int $year, ?int $requestedMonth, string $requestedNrp): array
    {
        $canPick = $this->canPickEmployee($user);
        $nrp = $canPick ? $requestedNrp : trim((string) $user->username);
        $month = $requestedMonth ?? (int) date('n');
        $state = [
            'canPick' => $canPick, 'nrp' => $nrp, 'bulan' => $month, 'tahun' => $year,
            'employees' => [], 'openMonths' => [], 'state' => 'ok', 'error' => '',
            'employee' => null, 'cells' => [], 'pic' => self::DEFAULT_PIC,
        ];

        try {
            $state['employees'] = $canPick ? $this->repository->shareEmployees() : [];
            $state['openMonths'] = $canPick || $nrp === '' ? [] : $this->repository->shareVisibleMonths($nrp, $year);
            $state['bulan'] = $month = $this->resolveMonth($month, $requestedMonth, $state['openMonths']);
            $isMonthClosed = ! $canPick && $nrp !== '' && ! in_array($month, $state['openMonths'], true);
            $monthData = $nrp === '' ? null : $this->repository->shareMonth($nrp, $year, $month, ! $canPick);
        } catch (Throwable $e) {
            report($e);

            return ['state' => 'error', 'error' => 'Data Roster V2 tidak dapat dimuat (tabel belum tersedia atau terjadi kesalahan).'] + $state;
        }

        $state['employee'] = $monthData ? $this->employeeInfo($nrp, $monthData) : null;
        $state['cells'] = $monthData['cells'] ?? [];
        $state['pic'] = $this->resolvePic($monthData);
        $state['state'] = $this->resolveState($canPick, $nrp, $isMonthClosed, $state['openMonths'], ! empty($state['cells']));

        return $state;
    }

    /** Tanpa pilihan bulan eksplisit, role terbatas dibuka ke bulan pertama yang tersedia. */
    private function resolveMonth(int $month, ?int $requestedMonth, array $openMonths): int
    {
        $needsFallback = $openMonths && $requestedMonth === null && ! in_array($month, $openMonths, true);

        return $needsFallback ? $openMonths[0] : $month;
    }

    /** Status tampilan: pick (admin belum memilih), closed (bulan belum dibuka), empty (tanpa roster), ok. */
    private function resolveState(bool $canPick, string $nrp, bool $isMonthClosed, array $openMonths, bool $hasRoster): string
    {
        return match (true) {
            $nrp === '' && $canPick => 'pick',
            $isMonthClosed => $openMonths ? 'closed_other_months' : 'closed',
            $nrp !== '' && ! $hasRoster => 'empty',
            default => 'ok',
        };
    }

    private function employeeInfo(string $nrp, array $monthData): array
    {
        $profile = $this->repository->shareEmployeeInfo($nrp);

        return [
            'nrp' => $nrp,
            'nama' => $profile['nama'] ?? ($monthData['nama'] ?? ''),
            'jabatan' => $profile['jabatan'] ?? ($monthData['jabatan'] ?? ''),
            'unit' => $monthData['unit'] ?: ($profile['unit'] ?? ''),
        ];
    }

    private function resolvePic(?array $monthData): array
    {
        return empty($monthData['pic_name'])
            ? self::DEFAULT_PIC
            : ['nama' => $monthData['pic_name'], 'jabatan' => $monthData['pic_jabatan'] ?: ''];
    }

    private function jabatanMatches(string $jabatan): bool
    {
        foreach (self::MANAGEMENT_JABATAN as $keyword) {
            if (str_contains($jabatan, $keyword)) {
                return true;
            }
        }

        return false;
    }
}
