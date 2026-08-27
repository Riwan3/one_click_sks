<?php

namespace Database\Seeders;

use App\Models\FleetAllocation;
use App\Models\RosterData;
use App\Models\Setting;
use App\Models\Unit;
use Illuminate\Database\Seeder;

class OneSettingSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Seed Master Fleet Units
        $units = [
            ['name' => 'HD785-012', 'category_type' => 'nonStaff', 'jumlah_unit' => 5],
            ['name' => 'HD785-018', 'category_type' => 'nonStaff', 'jumlah_unit' => 4],
            ['name' => 'PC2000-004', 'category_type' => 'nonStaff', 'jumlah_unit' => 2],
            ['name' => 'D375-007', 'category_type' => 'nonStaff', 'jumlah_unit' => 3],
            ['name' => 'GD675-002', 'category_type' => 'nonStaff', 'jumlah_unit' => 2],
            ['name' => 'WA500-009', 'category_type' => 'nonStaff', 'jumlah_unit' => 3],
            ['name' => 'GL-SAFETY-01', 'category_type' => 'staff', 'jumlah_unit' => 1],
            ['name' => 'GL-PRODUKSI-02', 'category_type' => 'staff', 'jumlah_unit' => 1],
        ];

        foreach ($units as $u) {
            Unit::updateOrCreate(['name' => $u['name']], $u);
        }

        // 2. Seed Roster Data
        $today = date('Y-m-d');
        $nrps = ['20045', '20046', '20047', '20048', '20049', '20050', '20051', '20052'];
        $unitCodes = ['HD785-012', 'HD785-018', 'PC2000-004', 'D375-007', 'GD675-002', 'WA500-009', 'HD785-012', 'HD785-018'];
        $csas = ['CSA GIBSON', 'CSA MONTE BARU', 'CSA MONTE SUPORT', 'CSA GIBSON', 'CSA GENERAL', 'CSA MONTE BARU', 'CSA GENERAL', 'CSA MONTE SUPORT'];

        foreach ($nrps as $idx => $nrp) {
            RosterData::updateOrCreate(
                ['nrp' => $nrp, 'tanggal' => $today, 'kode' => 'D'],
                ['unit' => $unitCodes[$idx], 'csa_type' => $csas[$idx]]
            );

            RosterData::updateOrCreate(
                ['nrp' => $nrp, 'tanggal' => $today, 'kode' => 'N'],
                ['unit' => $unitCodes[($idx + 2) % count($unitCodes)], 'csa_type' => $csas[$idx]]
            );
        }

        // 3. Seed Fleet Allocations
        $fleetItems = [
            [
                'tanggal' => $today,
                'shift' => 1,
                'group_name' => 'FRONT DISPOSAL A',
                'loader_id' => 'EX-2000-004',
                'area' => 'DISPOSAL PIT SOUTH',
                'front' => 'FRONT 1',
                'refueling_time' => '11:30 - 12:30',
                'unit_nrp' => 'HD785-012',
                'unit_status' => 'M',
                'distance' => 1200,
            ],
            [
                'tanggal' => $today,
                'shift' => 1,
                'group_name' => 'FRONT DISPOSAL A',
                'loader_id' => 'EX-2000-004',
                'area' => 'DISPOSAL PIT SOUTH',
                'front' => 'FRONT 1',
                'refueling_time' => '11:30 - 12:30',
                'unit_nrp' => 'HD785-018',
                'unit_status' => 'N',
                'distance' => 1200,
            ],
            [
                'tanggal' => $today,
                'shift' => 1,
                'group_name' => 'FRONT COAL BING',
                'loader_id' => 'WA500-009',
                'area' => 'COAL PIT NORTH',
                'front' => 'FRONT 2',
                'refueling_time' => '12:00 - 13:00',
                'unit_nrp' => 'D375-007',
                'unit_status' => 'G',
                'distance' => 850,
            ],
            [
                'tanggal' => $today,
                'shift' => 1,
                'group_name' => 'STATUS / SUMMARY',
                'loader_id' => 'BREAKDOWN WORKSHOP',
                'area' => 'MAIN WORKSHOP',
                'front' => 'BAY 3',
                'refueling_time' => '-',
                'unit_nrp' => 'GD675-002',
                'unit_status' => 'R',
                'distance' => 0,
            ],
        ];

        foreach ($fleetItems as $item) {
            FleetAllocation::create($item);
        }

        // 4. Seed Default Settings
        Setting::setValue('google_sheet_manpower_url_day', 'https://docs.google.com/spreadsheets/d/sample-manpower-day');
        Setting::setValue('google_sheet_manpower_url_night', 'https://docs.google.com/spreadsheets/d/sample-manpower-night');
        Setting::setValue('fleet_spreadsheet_url_day', 'https://docs.google.com/spreadsheets/d/sample-fleet-day');
        Setting::setValue('fleet_spreadsheet_url_night', 'https://docs.google.com/spreadsheets/d/sample-fleet-night');
    }
}
