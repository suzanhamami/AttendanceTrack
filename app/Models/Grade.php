<?php

// Block 5 - Exam and Grades Management | Student name / number: ________

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * The grade a student got in an exam. Table 14: grades (unique per exam + student).
 */
class Grade extends Model
{
    protected $table = 'grades';
    protected $primaryKey = 'grade_id';

    protected $fillable = [
        'exam_id',
        'student_id',
        'grade',
        'recorded_date',
    ];

    protected $casts = [
        'grade'         => 'decimal:2',
        'recorded_date' => 'date',
    ];

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class, 'exam_id', 'exam_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id', 'student_id');
    }

    /** Accessor (chapter 3): $grade->percentage. Grade as a percentage of the exam's maximum grade (needs the exam relation). */
    public function getPercentageAttribute(): float
    {
        $max = (float) $this->exam->max_grade;

        return $max > 0 ? round(((float) $this->grade / $max) * 100, 2) : 0.0;
    }

    /** Accessor (chapter 3): $grade->is_passed. True when the grade reaches the exam's minimum (passing) grade. */
    public function getIsPassedAttribute(): bool
    {
        return round((float) $this->grade, 2) >= round((float) $this->exam->min_grade, 2);
    }
}
