<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class CoachingClinicExport implements FromArray, WithHeadings
{
    public function __construct(private array $data) {}

    public function array(): array
    {
        return $this->data;
    }

    public function headings(): array
    {
        return ['NRP', 'Nama', 'Tanggal', 'Coach', 'Materi', 'Status'];
    }
}
