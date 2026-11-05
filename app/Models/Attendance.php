<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
     protected $fillable = [
        'lecture_id',
        'student_id',
        'status'
    ];
     public function lecture()
    {
        return $this->belongsTo(Lecture::class);
    }
     public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
