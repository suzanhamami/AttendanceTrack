<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    //
    protected $fillable = [
        'full_name', 
        'university_id', 
        'national_id', 
        'birth_date', 
        'phone', 
        'email', 
        'department_id', 
        'academic_year', 
        'photo', 
        'status'
    ];

    protected $casts = [
        'year' => 'string'
    ];

    public function department(){
        return $this->belongsTo(Department::class);
    }

    public function sections() {
        return $this->belongsToMany(Section::class, 'student_section');
    }
}
