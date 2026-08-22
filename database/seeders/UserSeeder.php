<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    private const NAMES = ['Ahmad Fauzi', 'Budi Santoso', 'Candra Wijaya', 'Dedi Kurniawan', 'Eko Prasetyo', 'Fajar Nugroho', 'Gilang Ramadhan', 'Hendra Saputra', 'Irfan Hakim', 'Joko Susilo', 'Wahyu Ananda', 'Yusuf Maulana', 'Zaki Ramadhan', 'Putri Amelia', 'Sari Wulandari'];

    private const JABATANS = ['Operator', 'Group Leader', 'Data Analyst', 'Admin Produksi', 'Pimpinan', 'Helper', 'Trafficman'];

    private const UNITS = ['Pit A', 'Pit B', 'Workshop', 'MASTER', 'Front Loading'];

    private const LOKASIS = ['Front Loading', 'Disposal', 'Workshop Area', 'Office', 'CSA Support'];

    private const ROLES = ['admin', 'pimpinan', 'group leader', 'helper', 'trafficman', 'tamu', 'operator'];

    public function run(): void
    {
        foreach (self::NAMES as $index => $name) {
            $username = $index === 0 ? 'admin' : (string) (10000 + $index);

            User::updateOrCreate(
                ['username' => $username],
                [
                    'name' => $name,
                    'email' => $username.'@oneclick.local',
                    'jabatan' => self::JABATANS[$index % count(self::JABATANS)],
                    'unit' => self::UNITS[$index % count(self::UNITS)],
                    'lokasi' => self::LOKASIS[$index % count(self::LOKASIS)],
                    'role' => $index === 0 ? 'admin' : self::ROLES[$index % count(self::ROLES)],
                    'password' => Hash::make($username),
                ]
            );
        }
    }
}
