<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    //
    protected $fillable = [
        'section_name',
        'course_id',
        'instructor_id',
        'semester_id',
        'capacity'
    ];

    public function course() {
        return $this->belongsTo(Course::class);
    }

    public function instructor() {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    public function students() {
        return $this->belongsToMany(Student::class, 'student_section');
    }
}
