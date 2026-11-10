<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    protected $primaryKey = 'role_id';
    public $timestamps = false;
    protected $fillable = ['role_name', 'description'];

    public function users(): HasMany
    {
        // Explicit keys: with the custom primary key `role_id`, Laravel's default guess would be `role_role_id`.
        return $this->hasMany(User::class, 'role_id', 'role_id');
    }
}