<?php

// Block 5 - Exam and Grades Management | Student name / number: ________

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

/**
 * An exam given to one section of a course. Table 13: exams.
 */
class Exam extends Model
{
    protected $table = 'exams';
    protected $primaryKey = 'exam_id';

    protected $fillable = [
        'exam_type_id',
        'exam_date',
        'exam_time',
        'duration',
        'max_grade',
        'min_grade',
        'section_id',
        'course_id',
        'room',
    ];

    protected $casts = [
        'exam_date' => 'date',
        'duration'  => 'integer',
        'max_grade' => 'decimal:2',
        'min_grade' => 'decimal:2',
    ];

    /* ---------------------------------------------------------------- relations */

    public function type(): BelongsTo
    {
        return $this->belongsTo(ExamType::class, 'exam_type_id', 'exam_type_id');
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class, 'section_id', 'section_id');
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'course_id', 'course_id');
    }

    public function grades(): HasMany
    {
        return $this->hasMany(Grade::class, 'exam_id', 'exam_id');
    }

    /**
     * Students registered in this exam's section (table student_section of the students module).
     * Queried directly so Block 5 does not depend on how the Section/Student relations are defined.
     */
    public function enrolledStudents(): Builder
    {
        return Student::query()->whereIn(
            'student_id',
            DB::table('student_section')->select('student_id')->where('section_id', $this->section_id)
        );
    }

    /* ------------------------------------------------------------------- scopes */

    /**
     * Advanced search used by the Grid View (all filters are optional):
     * type, section, course, exact date and a free-text search (course name/code, section, room).
     *
     * @param  array<string, mixed>  $filters
     */
    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['exam_type_id'] ?? null, fn (Builder $q, $v) => $q->where('exam_type_id', $v))
            ->when($filters['section_id'] ?? null, fn (Builder $q, $v) => $q->where('section_id', $v))
            ->when($filters['course_id'] ?? null, fn (Builder $q, $v) => $q->where('course_id', $v))
            ->when($filters['exam_date'] ?? null, fn (Builder $q, $v) => $q->whereDate('exam_date', $v))
            ->when(trim((string) ($filters['search'] ?? '')) !== '', function (Builder $q) use ($filters) {
                $term = '%' . trim($filters['search']) . '%';

                $q->where(function (Builder $w) use ($term) {
                    $w->where('room', 'like', $term)
                      ->orWhereHas('course', fn (Builder $c) => $c->where('course_name', 'like', $term)
                                                                 ->orWhere('course_code', 'like', $term))
                      ->orWhereHas('section', fn (Builder $s) => $s->where('section_name', 'like', $term));
                });
            });
    }

    /* ---------------------------------------------------------------- accessors */

    /** "09:30" instead of the raw "09:30:00" stored by the TIME column. */
    public function getTimeLabelAttribute(): string
    {
        return substr((string) $this->exam_time, 0, 5);
    }
}
