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
        'major', 
        'year'
    ];

    protected $casts = [
        'year' => 'string'
    ];
}
