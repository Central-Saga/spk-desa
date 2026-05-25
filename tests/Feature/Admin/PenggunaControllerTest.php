<?php

use App\Models\Desa;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    $this->admin = User::factory()->superAdmin()->create();
});

// Index
it('Super Admin dapat lihat list pengguna', function () {
    User::factory()->count(3)->create();

    $this->actingAs($this->admin)
        ->get('/admin/pengguna')
        ->assertOk();
});

// Store
it('Super Admin dapat tambah pengguna baru', function () {
    $this->actingAs($this->admin)
        ->post('/admin/pengguna', [
            'name' => 'Pengguna Baru',
            'username' => 'penggunabaru',
            'email' => 'baru@test.id',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'super_admin',
            'is_active' => true,
        ])
        ->assertRedirect('/admin/pengguna')
        ->assertSessionHas('success');

    expect(User::query()->where('username', 'penggunabaru')->exists())->toBeTrue();
});

// Store Staff Admin Desa wajib desa_id
it('validasi desa_id wajib untuk role staff_admin_desa', function () {
    $this->actingAs($this->admin)
        ->post('/admin/pengguna', [
            'name' => 'Staff Tanpa Desa',
            'username' => 'stafftanpadesa',
            'email' => 'staff@test.id',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'staff_admin_desa',
            'is_active' => true,
        ])
        ->assertSessionHasErrors(['desa_id']);
});

// Update
it('Super Admin dapat update pengguna', function () {
    $user = User::factory()->create(['name' => 'Lama']);

    $this->actingAs($this->admin)
        ->put("/admin/pengguna/{$user->id}", [
            'name' => 'Updated',
            'username' => $user->username,
            'email' => $user->email,
            'role' => 'super_admin',
            'is_active' => true,
        ])
        ->assertRedirect('/admin/pengguna')
        ->assertSessionHas('success');

    expect($user->fresh()->name)->toBe('Updated');
});

// Destroy
it('Super Admin dapat hapus pengguna', function () {
    $user = User::factory()->create();

    $this->actingAs($this->admin)
        ->delete("/admin/pengguna/{$user->id}")
        ->assertRedirect('/admin/pengguna')
        ->assertSessionHas('success');
});

it('blokir hapus diri sendiri', function () {
    $this->actingAs($this->admin)
        ->delete("/admin/pengguna/{$this->admin->id}")
        ->assertRedirect()
        ->assertSessionHas('error');
});
