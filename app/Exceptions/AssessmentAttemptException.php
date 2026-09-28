<?php

namespace App\Exceptions;

use RuntimeException;

/** Karyawan tidak bisa memulai/melanjutkan attempt assessment (sudah lulus, atau batas percobaan habis). */
class AssessmentAttemptException extends RuntimeException
{
}
