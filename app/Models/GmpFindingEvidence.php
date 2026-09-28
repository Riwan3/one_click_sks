<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Foto evidence temuan GMP: before, progress, after, verification. */
class GmpFindingEvidence extends Model
{
    public const TYPES = ['before', 'progress', 'after', 'verification'];

    protected $table = 'gmp_temuan_evidence';

    protected $fillable = ['finding_id', 'evidence_type', 'file_path', 'original_name', 'description', 'uploaded_by'];
}
