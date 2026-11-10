<?php

// Block 1 - Users, Roles and Permissions Management | Student name / number: ________

namespace App\Models;

use App\Support\Block1\Access;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** One row = one permission granted to one role, stored as "<role_name>:<permission>". */
class Permission extends Model
{
    protected $primaryKey = 'permission_id';
    public $timestamps = false;
    protected $fillable = ['permission_name', 'role_id'];

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id', 'role_id');
    }

    /** The permission without its role prefix, e.g. "users.view". */
    public function shortName(): string
    {
        $parts = explode(Access::SEPARATOR, $this->permission_name, 2);

        return end($parts);
    }
}
