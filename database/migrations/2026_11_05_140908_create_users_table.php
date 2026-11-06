<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            // Explicit Primary Key: user_id
            $table->id('user_id'); 
            $table->string('full_name', 100);
            $table->string('username', 50)->unique();
            $table->string('email', 100)->unique();
            $table->string('password', 255);
            $table->string('phone', 15)->nullable();

            // Foreign Keys linking to custom PKs
            $table->unsignedBigInteger('role_id');
            $table->foreign('role_id')->references('role_id')->on('roles')->onDelete('cascade')->onUpdate('cascade');

            $table->unsignedBigInteger('department_id')->nullable();
            $table->foreign('department_id')->references('department_id')->on('departments')->nullOnDelete();

            $table->string('photo', 255)->nullable();
            $table->date('birth_date')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};