<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Semester extends Model
{
    protected $primaryKey = 'semester_id';
    public $timestamps = false;
    protected $fillable = ['semester_name', 'start_date', 'end_date'];

    public function courses(): HasMany
    {
        return $this->hasMany(Course::class);
    }
}