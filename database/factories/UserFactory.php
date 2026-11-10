<?php

namespace Database\Factories;

use App\Models\Department;
use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        return [
            'full_name'     => fake()->name(),
            'username'      => fake()->unique()->userName() . Str::random(3),
            'email'         => fake()->unique()->safeEmail(),
            'password'      => static::$password ??= Hash::make('password1'),
            'phone'         => '09' . fake()->numerify('########'),
            'role_id'       => fn () => Role::firstOrCreate(['role_name' => 'employee'])->role_id,
            'department_id' => fn () => Department::firstOrCreate(['department_name' => 'Software Engineering'])->department_id,
            'birth_date'    => fake()->dateTimeBetween('-50 years', '-20 years')->format('Y-m-d'),
            'status'        => 'active',
            'remember_token'=> Str::random(10),
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['status' => 'inactive']);
    }
}
