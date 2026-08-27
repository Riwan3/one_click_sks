<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RosterData extends Model
{
    use HasFactory;

    protected $table = 'roster_data';

    protected $fillable = [
        'nrp',
        'tanggal',
        'unit',
        'kode',
        'csa_type',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'nrp', 'nrp');
    }
}
