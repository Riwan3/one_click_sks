<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    private const NAMES = ['Ahmad Fauzi', 'Budi Santoso', 'Candra Wijaya'];

    private const ROLES = ['admin', 'pimpinan', 'group-leader'];

    public function run(): void
    {
        foreach (self::NAMES as $index => $name) {
            $nrp = $index === 0 ? 'admin' : (string) (10000 + $index);

            $employee = Employee::where('nrp', $nrp)->first();

            User::updateOrCreate(
                ['username' => $nrp],
                [
                    'employee_id' => $employee?->id,
                    'name' => $name,
                    'email' => $nrp.'@oneclick.local',
                    'role' => self::ROLES[$index % count(self::ROLES)],
                    'password' => Hash::make($nrp),
                ]
            );
        }
    }
}
