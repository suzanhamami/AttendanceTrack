<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use HasFactory;
    use Notifiable;
    protected $primaryKey = 'user_id';
    protected $fillable = [
        'full_name',
        'username',
        'email',
        'password',
        'phone',
        'role_id',
        'department_id',
        'photo',
        'birth_date',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function role()
{
    return $this->belongsTo(Role::class, 'role_id', 'role_id');
}

    public function department(): BelongsTo
    {
        // Explicit keys: the default guess for the custom primary key would be `department_department_id`.
        return $this->belongsTo(Department::class, 'department_id', 'department_id');
    }

    public function courses(): HasMany
    {
        return $this->hasMany(Course::class, 'instructor_id', 'user_id');
    }
}