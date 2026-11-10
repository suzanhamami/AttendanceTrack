<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    protected $primaryKey = 'department_id';
    public $timestamps = false;
    protected $fillable = ['department_name'];

    public function courses(): HasMany
    {
        return $this->hasMany(Course::class);
    }
}