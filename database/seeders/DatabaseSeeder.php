<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Block 1 - roles, permissions, departments and user accounts
        $this->call(UserManagementSeeder::class);
    }
}
