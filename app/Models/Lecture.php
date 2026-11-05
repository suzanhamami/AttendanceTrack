<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lecture extends Model
{
    protected $fillable = [
        'title',
        'lecture_date',
        'section_id'
    ];
    public function section()
{
    return $this->belongsTo(Section::class);
}
public function attendances()
{
    return $this->hasMany(Attendance::class);
}
}
