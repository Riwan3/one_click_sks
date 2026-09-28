<?php

namespace Database\Seeders;

use App\Models\MppSetting;
use App\Models\Unit;
use Illuminate\Database\Seeder;

class MppUnitSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Settings
        MppSetting::set('mpp_target_1', '2.7');
        MppSetting::set('mpp_target_2', '2.8');

        // 2. Staff Units
        $staff = [
            [
                'name' => 'DEPARTEMENT HEAD PRODUKSI',
                'category_type' => 'staff',
                'index_pengawas' => 1,
                'patterns' => 'DEPT HEAD PRODUKSI,DEPARTEMENT HEAD PRODUKSI',
                'sort_order' => 1,
            ],
            [
                'name' => 'SECTION HEAD PRODUKSI',
                'category_type' => 'staff',
                'index_pengawas' => 4,
                'patterns' => 'SECTION HEAD PRODUKSI,SEC HEAD PRODUKSI',
                'sort_order' => 2,
            ],
            [
                'name' => 'GROUP LEADER PRODUKSI',
                'category_type' => 'staff',
                'index_pengawas' => 63,
                'patterns' => 'GROUP LEADER PRODUKSI,GROUP LEADER PRODUCTION,Group Leader Produksi',
                'sort_order' => 3,
            ],
            [
                'name' => 'ACT GROUP LEADER PRODUKSI',
                'category_type' => 'staff',
                'index_pengawas' => 10,
                'patterns' => 'ACT. GROUP LEADER PRODUKSI,ACT GROUP LEADER PRODUKSI',
                'sort_order' => 4,
            ],
            [
                'name' => 'PLDP PRODUKSI',
                'category_type' => 'staff',
                'index_pengawas' => 3,
                'patterns' => 'PLDP PRODUCTION,PLDP PRODUKSI,PLDP',
                'sort_order' => 5,
            ],
        ];

        foreach ($staff as $item) {
            Unit::updateOrCreate(
                ['name' => $item['name'], 'category_type' => 'staff'],
                $item
            );
        }

        // 3. Non-Staff Units
        $nonStaff = [
            [
                'name' => 'PC 1250 / PC8018 / PC200',
                'category_type' => 'nonStaff',
                'jumlah_unit' => 17,
                'patterns' => 'PC 1250,PC1250,PC 200,PC200,PC 2000,PC 210,CAT 395,PC8018',
                'sort_order' => 1,
            ],
            [
                'name' => 'PC 300',
                'category_type' => 'nonStaff',
                'jumlah_unit' => 3,
                'patterns' => 'PC 300,PC300',
                'sort_order' => 2,
            ],
            [
                'name' => 'PC 500',
                'category_type' => 'nonStaff',
                'jumlah_unit' => 8,
                'patterns' => 'PC 500,PC500',
                'sort_order' => 3,
            ],
            [
                'name' => 'PC 5040',
                'category_type' => 'nonStaff',
                'jumlah_unit' => 4,
                'patterns' => 'PC 5040,PC5040',
                'sort_order' => 4,
            ],
            [
                'name' => 'HD785-7 / CAT777',
                'category_type' => 'nonStaff',
                'jumlah_unit' => 105,
                'patterns' => 'HD 785,HD785,CAT 777,CAT777,DT 31 - 50T',
                'sort_order' => 5,
            ],
            [
                'name' => 'WT 773',
                'category_type' => 'nonStaff',
                'jumlah_unit' => 3,
                'patterns' => 'CAT773,WT 20KL,WATER TRUCK',
                'sort_order' => 6,
            ],
            [
                'name' => 'D375A',
                'category_type' => 'nonStaff',
                'jumlah_unit' => 9,
                'patterns' => 'D375',
                'sort_order' => 7,
            ],
            [
                'name' => 'D155A',
                'category_type' => 'nonStaff',
                'jumlah_unit' => 12,
                'patterns' => 'D155',
                'sort_order' => 8,
            ],
            [
                'name' => 'D85ESS',
                'category_type' => 'nonStaff',
                'jumlah_unit' => 8,
                'patterns' => 'D85',
                'sort_order' => 9,
            ],
            [
                'name' => 'WA600-3',
                'category_type' => 'nonStaff',
                'jumlah_unit' => 1,
                'patterns' => 'WA 480,WA600',
                'sort_order' => 10,
            ],
            [
                'name' => 'COMPACT',
                'category_type' => 'nonStaff',
                'jumlah_unit' => 2,
                'patterns' => 'BW,COMPACT',
                'sort_order' => 11,
            ],
            [
                'name' => 'Mercy 4040 K',
                'category_type' => 'nonStaff',
                'jumlah_unit' => 4,
                'patterns' => '4040,Mercy,DRIVER FT',
                'sort_order' => 12,
            ],
            [
                'name' => 'WT Mercy 3836',
                'category_type' => 'nonStaff',
                'jumlah_unit' => 1,
                'patterns' => '3836,WT DT',
                'sort_order' => 13,
            ],
            [
                'name' => 'HELPER PIT SERVICE',
                'category_type' => 'nonStaff',
                'jumlah_unit' => 5,
                'patterns' => 'HELPER PIT SERVICE,HELPER QUALITY COAL',
                'sort_order' => 14,
            ],
            [
                'name' => 'ADMIN PRODUKSI',
                'category_type' => 'nonStaff',
                'jumlah_unit' => 4,
                'patterns' => 'ADMIN PRODUKSI',
                'sort_order' => 15,
            ],
            [
                'name' => 'TRAFFICMAN',
                'category_type' => 'nonStaff',
                'jumlah_unit' => 2,
                'patterns' => 'TRAFFICMAN',
                'sort_order' => 16,
            ],
        ];

        foreach ($nonStaff as $item) {
            Unit::updateOrCreate(
                ['name' => $item['name'], 'category_type' => 'nonStaff'],
                $item
            );
        }
    }
}
