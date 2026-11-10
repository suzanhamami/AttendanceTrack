<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    //
    protected $primaryKey = 'section_id';
    const UPDATED_AT = null;
    // public $timestamps = false;
    
    protected $fillable = [
        'section_name',
        'course_id',
        'instructor_id',
        'semester_id',
        'capacity'
    ];

    public function course() {
        return $this->belongsTo(Course::class, 'course_id');
    }

    public function instructor() {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    // public function students() {
    //     return $this->belongsToMany(Student:class, 'student_section');
    // }

    public function students() {
        return $this->belongsToMany(
            Student::class, 
            'student_section', // Pivot table name
            'section_id',      // Foreign key for Section in pivot
            'student_id'       // Foreign key for Student in pivot
        )
        ->using(StudentSection::class)
        // The pivot table only has `created_at` (no `updated_at`). withTimestamps() would also ask for the
        // pivot's updated-at column, which is null here (UPDATED_AT = null), so created_at is selected explicitly
        // and the database fills it (useCurrent) when a row is attached.
        ->withPivot('registration_date', 'student_section_id', 'created_at');
    }
}
