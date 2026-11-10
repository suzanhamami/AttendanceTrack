<?php

// Block 1 - Users, Roles and Permissions Management | Student name / number: ________

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Makes the shared `users` table match table 1 of the spec exactly:
 *   phone          VARCHAR(15)  NOT NULL
 *   department_id  INT          FK -> departments, NOT NULL
 *   birth_date     DATE         NOT NULL, CHECK (age >= 18)
 *   created_at     TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
 *   role_id        FK -> roles  ON DELETE RESTRICT (was CASCADE: deleting a role deleted its users)
 *
 * Kept as a separate migration (instead of editing the team's create_users_table file) so the
 * blocks merge without git conflicts.
 *
 * CHECK (age >= 18): MySQL / MariaDB do not allow CURDATE() inside a CHECK constraint, so the age is
 * checked against the row's own created_at date (the account creation date). The same rule is also
 * enforced by the form validation (UserRequest).
 */
return new class extends Migration
{
    public function up(): void
    {
        // Rows created before this migration with empty values get placeholders, otherwise NOT NULL fails.
        // (A fresh database has no rows, so this only matters for an existing team database.)
        $firstDepartment = DB::table('departments')->min('department_id');
        DB::table('users')->whereNull('phone')->update(['phone' => '0000000000']);
        DB::table('users')->whereNull('birth_date')->update(['birth_date' => '1970-01-01']);
        if ($firstDepartment) {
            DB::table('users')->whereNull('department_id')->update(['department_id' => $firstDepartment]);
        }

        Schema::table('users', function (Blueprint $table) {
            // ON DELETE SET NULL is impossible on a NOT NULL column: re-create the FK as RESTRICT.
            $table->dropForeign(['department_id']);
            // Deleting a role must never delete users (data integrity): re-create as RESTRICT.
            $table->dropForeign(['role_id']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('phone', 15)->nullable(false)->change();
            $table->unsignedBigInteger('department_id')->nullable(false)->change();
            $table->date('birth_date')->nullable(false)->change();
            $table->timestamp('created_at')->nullable()->useCurrent()->change();

            $table->foreign('department_id')->references('department_id')->on('departments')
                ->restrictOnDelete()->cascadeOnUpdate();
            $table->foreign('role_id')->references('role_id')->on('roles')
                ->restrictOnDelete()->cascadeOnUpdate();
        });

        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            DB::statement('ALTER TABLE users ADD CONSTRAINT chk_users_age_18
                           CHECK (birth_date <= DATE_SUB(DATE(created_at), INTERVAL 18 YEAR))');
        }
    }

    public function down(): void
    {
        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            DB::statement('ALTER TABLE users DROP CONSTRAINT chk_users_age_18');
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->dropForeign(['role_id']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('phone', 15)->nullable()->change();
            $table->unsignedBigInteger('department_id')->nullable()->change();
            $table->date('birth_date')->nullable()->change();
            $table->timestamp('created_at')->nullable()->change();

            $table->foreign('department_id')->references('department_id')->on('departments')
                ->nullOnDelete()->cascadeOnUpdate();
            $table->foreign('role_id')->references('role_id')->on('roles')
                ->cascadeOnDelete()->cascadeOnUpdate();
        });
    }
};
