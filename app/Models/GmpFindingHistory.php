<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Audit trail temuan GMP. */
class GmpFindingHistory extends Model
{
    protected $table = 'gmp_temuan_history';

    protected $fillable = ['finding_id', 'action_type', 'old_status', 'new_status', 'description', 'created_by'];

    public function actor()
    {
        return $this->belongsTo(Employee::class, 'created_by', 'nrp');
    }
}
