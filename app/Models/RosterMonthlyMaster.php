<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RosterMonthlyMaster extends Model
{
    protected $table = 'roster_monthly_master';

    public $timestamps = false;

    protected $fillable = [
        'nrp',
        'tanggal',
        'kode',
        'unit',
        'jabatan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
        ];
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'nrp', 'nrp');
    }
}
