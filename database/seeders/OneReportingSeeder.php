<?php

namespace Database\Seeders;

use App\Models\MppUnitPhoto;
use App\Models\OdrAssignment;
use App\Models\OdrMasterAktivitas;
use App\Models\OdrMasterArea;
use App\Models\OdrMasterUnit;
use Illuminate\Database\Seeder;

class OneReportingSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Master Area
        $areas = ['MFOB', 'Disposal A', 'Disposal B', 'Dewatering', 'MFOC', 'Workshop Central', 'Hauling Road KM 12'];
        foreach ($areas as $a) {
            OdrMasterArea::firstOrCreate(['nama' => $a], ['keterangan' => 'Area operasional utama', 'aktif' => true]);
        }

        // 2. Master Unit
        $units = [
            ['kode' => 'HD-785-012', 'nama' => 'Dump Truck HD785-012', 'area' => 'MFOB'],
            ['kode' => 'EX-2000-004', 'nama' => 'Excavator PC2000-004', 'area' => 'MFOB'],
            ['kode' => 'DZ-375-007', 'nama' => 'Dozer D375-007', 'area' => 'Disposal A'],
            ['kode' => 'WL-500-009', 'nama' => 'Wheel Loader WA500-009', 'area' => 'Disposal B'],
            ['kode' => 'GD-675-002', 'nama' => 'Grader GD675-002', 'area' => 'Hauling Road KM 12'],
            ['kode' => 'PM-001', 'nama' => 'Pompa Dewatering 01', 'area' => 'Dewatering'],
        ];
        foreach ($units as $u) {
            OdrMasterUnit::firstOrCreate(['nama' => $u['nama']], [
                'kode' => $u['kode'],
                'area' => $u['area'],
                'keterangan' => 'Unit aktif lapangan',
                'aktif' => true
            ]);
        }

        // 3. Master Aktivitas
        $aktivitas = [
            ['nama' => 'P5M & Briefing Keselamatan', 'kategori' => 'Safety'],
            ['nama' => 'Pemeriksaan APD & Fit to Work', 'kategori' => 'Safety'],
            ['nama' => 'Inspeksi Housekeeping Area', 'kategori' => 'GMP'],
            ['nama' => 'Pemeriksaan Unit Pre-Start Check', 'kategori' => 'Operasional'],
            ['nama' => 'Pemantauan Dust Control & Water Truck', 'kategori' => 'Lingkungan'],
        ];
        foreach ($aktivitas as $ak) {
            OdrMasterAktivitas::firstOrCreate(['nama' => $ak['nama']], [
                'kategori' => $ak['kategori'],
                'keterangan' => 'Aktivitas rutin harian',
                'aktif' => true
            ]);
        }

        // 4. Assignments (past 7 days + today)
        $supervisors = ['Agus Setiawan', 'Budi Harjo', 'Chandra Kirana', 'Dedi Supriyadi', 'Eko Prasetyo'];
        $tasksList = [
            ['P5M', 'Pemeriksaan APD', 'Cek Fatigue', 'Housekeeping Area'],
            ['Pre-Start Check Unit', 'Inspeksi Housekeeping', 'Cek APD Driver'],
            ['Safety Briefing', 'Cek Jalur Hauling', 'Pengawasan Dumping'],
        ];

        for ($i = 6; $i >= 0; $i--) {
            $tgl = date('Y-m-d', strtotime("-$i days"));
            foreach ($areas as $idx => $areaName) {
                $spv = $supervisors[$idx % count($supervisors)];
                $unitCode = $units[$idx % count($units)]['nama'];
                $tasks = $tasksList[$idx % count($tasksList)];
                
                // Done tasks
                $dones = [];
                foreach ($tasks as $tIdx => $tName) {
                    $dones[] = ($i === 0 && $idx > 3) ? false : (bool) (($tIdx + $idx) % 2 === 0 || $i > 2);
                }
                
                $doneCount = count(array_filter($dones));
                $totalTasks = count($tasks);
                $status = $doneCount === $totalTasks ? 'selesai' : ($doneCount > 0 ? 'progress' : 'belum');

                OdrAssignment::updateOrCreate(
                    ['tanggal' => $tgl, 'area' => $areaName, 'pengawas' => $spv],
                    [
                        'shift' => $idx % 2 === 0 ? 'Shift 1' : 'Shift 2',
                        'unit' => $unitCode,
                        'aktivitas' => json_encode($tasks),
                        'task_done' => json_encode($dones),
                        'keterangan' => 'Pengawasan operasional rutin',
                        'status' => $status,
                    ]
                );
            }
        }

        // 5. MPP Unit Photos (OJK)
        $ojkUnits = ['HD785-012', 'PC2000-004', 'D375-007', 'WA500-009', 'GD675-002'];
        foreach ($ojkUnits as $uCode) {
            MppUnitPhoto::updateOrCreate(
                ['jenis' => 'OJK', 'unit_code' => $uCode, 'tanggal' => date('Y-m-d'), 'shift' => 'D'],
                [
                    'nama' => 'Operator ' . $uCode,
                    'url' => 'https://picsum.photos/400/300?random=' . rand(1, 100),
                ]
            );
        }
    }
}
