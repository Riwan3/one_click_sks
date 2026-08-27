<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class EmployeeExport implements FromCollection, WithHeadings, WithMapping
{
    public function __construct(private readonly Collection $employees) {}

    public function collection(): Collection
    {
        return $this->employees;
    }

    public function headings(): array
    {
        return [
            'NO', 'NRP', 'PASSWORD', 'NAMA', 'JABATAN SS6', 'JABATAN', 'STATUS KARYAWAN',
            'LOKASI KERJA', 'UNIT', 'NO TELEPON', 'EFEKTIF BERKERJA DI SITE ADARO INDONESIA',
            'INPUT HERE!!!', 'EMAIL',
        ];
    }

    public function map($employee): array
    {
        static $no = 0;
        $no++;

        return [
            $no,
            $employee->nrp,
            $employee->nrp,
            $employee->name,
            $employee->jabatan_ss6,
            $employee->jabatan_roster,
            $employee->status_karyawan?->value,
            $employee->lokasi_kerja,
            $employee->unit,
            $employee->no_telepon,
            $employee->efektif_bekerja?->format('d/m/Y'),
            $employee->input_here,
            $employee->email,
        ];
    }
}
