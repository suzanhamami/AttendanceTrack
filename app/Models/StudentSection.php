<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot; 

class StudentSection extends Pivot
{
    //
    protected $table = 'student_section';
    protected $primaryKey = 'student_section_id';
    public $incrementing = true;
    const UPDATED_AT = null;
    // public $timestamps = false;

    protected $fillable = [
        'student_id',
        'section_id',
        'registration_date'
    ];
    
    public function section() {
        return $this->belongsTo(Section::class, 'section_id', 'section_id');
    }

    public function student() {
        return $this->belongsTo(Student::class, 'student_id', 'student_id');
    }
}
