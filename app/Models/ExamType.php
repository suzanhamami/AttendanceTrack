<?php

// Block 5 - Exam and Grades Management | Student name / number: ________

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Exam type (Midterm, Final, Practical, Oral ...). Table 15: exam_types.
 */
class ExamType extends Model
{
    protected $table = 'exam_types';
    protected $primaryKey = 'exam_type_id';

    // The table only has created_at (see the spec).
    const UPDATED_AT = null;

    protected $fillable = ['type_name'];

    public function exams(): HasMany
    {
        return $this->hasMany(Exam::class, 'exam_type_id', 'exam_type_id');
    }
}
