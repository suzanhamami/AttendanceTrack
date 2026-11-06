<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Course extends Model
{
    protected $primaryKey = 'course_id';
    protected $fillable = [
        'course_name',
        'course_code',
        'credit_hours',
        'department_id',
        'instructor_id',
        'semester_id',
        'academic_year',
        'description',
        'prerequisite_id',
        'status',
    ];

    protected $casts = [
        'credit_hours' => 'integer',
    ];

    public function department(): BelongsTo
{
    return $this->belongsTo(Department::class, 'department_id', 'department_id');
}

    public function instructor(): BelongsTo
{
    return $this->belongsTo(User::class, 'instructor_id', 'user_id');
}

public function semester(): BelongsTo
{
    return $this->belongsTo(Semester::class, 'semester_id', 'semester_id');
}

    public function prerequisite(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'prerequisite_id', 'course_id');
    }
}