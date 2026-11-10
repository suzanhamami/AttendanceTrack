<?php

// Block 1 - Users, Roles and Permissions Management | Student name / number: ________

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Table 3 of the spec: permissions
 *   id               PK, AUTO_INCREMENT, NOT NULL
 *   permission_name  VARCHAR(100) UNIQUE, NOT NULL
 *   role_id          FK -> roles, NOT NULL
 *   created_at       DEFAULT CURRENT_TIMESTAMP
 * Each row grants one permission to one role and is stored as "<role_name>:<permission>"
 * (e.g. "employee:users.view"), so permission_name stays UNIQUE as the spec requires.
 * The primary key is named permission_id, following the team's convention (user_id, role_id, ...).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permissions', function (Blueprint $table) {
            $table->id('permission_id');
            $table->string('permission_name', 100)->unique();

            $table->unsignedBigInteger('role_id');
            $table->foreign('role_id')->references('role_id')->on('roles')->cascadeOnDelete()->cascadeOnUpdate();

            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permissions');
    }
};
