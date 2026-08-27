<?php

namespace Database\Seeders;

use App\Models\Employee;
use Illuminate\Database\Seeder;

class EmployeeSeeder extends Seeder
{
    private const NAMES = ['Ahmad Fauzi', 'Budi Santoso', 'Candra Wijaya', 'Dedi Kurniawan', 'Eko Prasetyo', 'Fajar Nugroho', 'Gilang Ramadhan', 'Hendra Saputra', 'Irfan Hakim', 'Joko Susilo', 'Wahyu Ananda', 'Yusuf Maulana', 'Zaki Ramadhan', 'Putri Amelia', 'Sari Wulandari'];

    private const JABATANS = ['OPERATOR', 'GROUP LEADER', 'DATA ANALYST', 'ADMIN PRODUKSI', 'PIMPINAN', 'HELPER', 'TRAFFICMAN'];

    private const JABATAN_ROSTERS = ['Operator', 'Group Leader', 'Data Analyst', 'Admin Produksi', 'Pimpinan', 'Helper', 'Trafficman'];

    private const UNITS = ['EX2600', 'HD785', 'DZ155', 'MASTER', 'GD825'];

    private const LOKASIS = ['PIT 1', 'PIT 2', 'DISPOSAL', 'WORKSHOP', 'CSA SUPPORT'];

    private const STATUSES = ['FIT', 'FIT', 'FIT', 'FIT', 'UNFIT'];

    public function run(): void
    {
        foreach (self::NAMES as $index => $name) {
            $nrp = $index === 0 ? 'admin' : (string) (10000 + $index);

            Employee::updateOrCreate(
                ['nrp' => $nrp],
                [
                    'name' => $name,
                    'jabatan_ss6' => self::JABATANS[$index % count(self::JABATANS)],
                    'jabatan_roster' => self::JABATAN_ROSTERS[$index % count(self::JABATAN_ROSTERS)],
                    'status_karyawan' => self::STATUSES[$index % count(self::STATUSES)],
                    'lokasi_kerja' => self::LOKASIS[$index % count(self::LOKASIS)],
                    'unit' => self::UNITS[$index % count(self::UNITS)],
                    'no_telepon' => '0812'.str_pad((string) (1000000 + $index), 7, '0', STR_PAD_LEFT),
                    'email' => $nrp.'@oneclick.local',
                    'efektif_bekerja' => now()->subYears(1 + $index % 5)->subMonths($index)->format('Y-m-d'),
                ]
            );
        }
    }
}
