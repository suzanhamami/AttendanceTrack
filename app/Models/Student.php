<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    //
    protected $primaryKey = 'student_id';
    
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
        return $this->belongsTo(Department::class, 'department_id');
    }

    // public function sections() {
    //     return $this->belongsToMany(Section::class, 'student_section');
    // }

    public function sections() {
        return $this->belongsToMany(
            Section::class, 
            'student_section', // Pivot table name
            'student_id',      // Foreign key for Student in pivot
            'section_id'       // Foreign key for Section in pivot
        )
        ->using(StudentSection::class) // Links it to your custom pivot model if needed
        ->withPivot('registration_date', 'student_section_id')
        ->withTimestamps();
    }
}
