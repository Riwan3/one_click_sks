<?php

namespace App\Support;

use App\Enums\IzinJenis;
use App\Enums\IzinStatus;
use App\Models\IzinKaryawan;
use App\Models\User;

/**
 * Aturan akses Izin Karyawan, sama dengan V1 (database/izin_karyawan): di V1 aturan
 * ini hanya diterapkan di tampilan (tombol mana yang muncul); di sini juga ditegakkan
 * di server supaya tidak bisa dilewati lewat URL/API.
 */
class IzinKaryawanAccess
{
    /** Admin, pimpinan, manpower, atau medic melihat & mengelola semua data (medic: hanya sakit). */
    public static function isAdminOrManpower(?User $user): bool
    {
        if (! $user) {
            return false;
        }
        $user->loadMissing('employee');

        return $user->isAdmin() || $user->isPimpinan() || $user->isGlManpower() || $user->isMedic()
            || stripos($user->employee?->jabatan_ss6 ?? $user->jabatan ?? '', 'MANPOWER') !== false;
    }

    /** NRP & nama milik akun (nama dari data karyawan, cadangan nama akun). */
    public static function ownIdentity(?User $user): array
    {
        $user?->loadMissing('employee');

        return [
            'nrp' => $user?->employee?->nrp ?? $user?->username ?? '',
            'nama' => $user?->employee?->name ?? $user?->name ?? '',
        ];
    }

    /** Pembuat pengajuan, atau karyawan yang diajukan (daftar user biasa difilter per NRP). */
    public static function isOwner(?User $user, IzinKaryawan $izin): bool
    {
        if (! $user) {
            return false;
        }
        $nrp = $user->loadMissing('employee')->employee?->nrp ?? $user->username;

        return $izin->created_by === $user->id || ($nrp !== null && $nrp !== '' && $izin->nrp === $nrp);
    }

    public static function canView(?User $user, IzinKaryawan $izin): bool
    {
        if ($user?->isMedic() && $izin->jenis_izin !== IzinJenis::Sakit) {
            return false;
        }

        return self::isAdminOrManpower($user) || self::isOwner($user, $izin);
    }

    /** Form hanya bisa disimpan selama masih menunggu persetujuan. */
    public static function canUpdate(?User $user, IzinKaryawan $izin): bool
    {
        return $izin->status === IzinStatus::Awaiting && self::canView($user, $izin);
    }

    /** Batalkan: pembuat atau admin, selama masih menunggu persetujuan. */
    public static function canCancel(?User $user, IzinKaryawan $izin): bool
    {
        return $izin->status === IzinStatus::Awaiting
            && ($izin->created_by === $user?->id || (bool) $user?->isAdmin());
    }

    /** Hapus: hanya data yang sudah dibatalkan, oleh pembuat atau admin. */
    public static function canDelete(?User $user, IzinKaryawan $izin): bool
    {
        return $izin->status === IzinStatus::Cancelled
            && ($izin->created_by === $user?->id || (bool) $user?->isAdmin());
    }
}
