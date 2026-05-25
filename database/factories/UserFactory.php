<?php

namespace Database\Factories;

use App\Enums\RoleSlug;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected static ?string $password;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'username' => fake()->unique()->userName(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'is_active' => true,
            'remember_token' => Str::random(10),
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn () => ['email_verified_at' => null]);
    }

    public function nonaktif(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }

    public function superAdmin(): static
    {
        return $this->afterCreating(fn (User $u) => $u->syncRoles([RoleSlug::SuperAdmin->value]));
    }

    public function staffAdminDesa(): static
    {
        return $this->afterCreating(fn (User $u) => $u->syncRoles([RoleSlug::StaffAdminDesa->value]));
    }

    public function staffPenilaian(): static
    {
        return $this->afterCreating(fn (User $u) => $u->syncRoles([RoleSlug::StaffPenilaian->value]));
    }

    public function pimpinan(): static
    {
        return $this->afterCreating(fn (User $u) => $u->syncRoles([RoleSlug::Pimpinan->value]));
    }
}
