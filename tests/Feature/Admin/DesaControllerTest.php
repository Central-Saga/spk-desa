<?php

use App\Models\Desa;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    $this->admin = User::factory()->superAdmin()->create();
});

// Index
it('Super Admin dapat lihat list desa', function () {
    Desa::factory()->count(3)->create();

    $this->actingAs($this->admin)
        ->get('/admin/desa')
        ->assertOk()
        ->assertSee(Desa::first()->nama);
});

it('Staff Admin Desa diblokir akses /admin/desa', function () {
    $desa = Desa::factory()->create();
    $user = User::factory()->staffAdminDesa()->create(['desa_id' => $desa->id]);

    $this->actingAs($user)
        ->get('/admin/desa')
        ->assertForbidden();
});

// Store
it('Super Admin dapat tambah desa', function () {
    $this->actingAs($this->admin)
        ->post('/admin/desa', [
            'nama' => 'Desa Baru',
            'alamat' => 'Jl. Raya',
            'kecamatan' => 'Kuta',
            'kabupaten' => 'Badung',
            'is_active' => true,
        ])
        ->assertRedirect('/admin/desa')
        ->assertSessionHas('success');

    expect(Desa::query()->where('nama', 'Desa Baru')->exists())->toBeTrue();
});

it('validasi wajib saat tambah desa', function () {
    $this->actingAs($this->admin)
        ->post('/admin/desa', [])
        ->assertSessionHasErrors(['nama', 'alamat', 'kecamatan', 'kabupaten']);
});

// Update
it('Super Admin dapat update desa', function () {
    $desa = Desa::factory()->create(['nama' => 'Desa Lama']);

    $this->actingAs($this->admin)
        ->put("/admin/desa/{$desa->id}", [
            'nama' => 'Desa Updated',
            'alamat' => $desa->alamat,
            'kecamatan' => $desa->kecamatan,
            'kabupaten' => $desa->kabupaten,
            'is_active' => true,
        ])
        ->assertRedirect('/admin/desa')
        ->assertSessionHas('success');

    expect($desa->fresh()->nama)->toBe('Desa Updated');
});

// Destroy
it('Super Admin dapat hapus desa tanpa relasi', function () {
    $desa = Desa::factory()->create();

    $this->actingAs($this->admin)
        ->delete("/admin/desa/{$desa->id}")
        ->assertRedirect('/admin/desa')
        ->assertSessionHas('success');

    expect(Desa::query()->withTrashed()->find($desa->id)?->trashed())->toBeTrue();
});

it('blokir hapus desa yang punya user', function () {
    $desa = Desa::factory()->create();
    User::factory()->staffAdminDesa()->create(['desa_id' => $desa->id]);

    $this->actingAs($this->admin)
        ->delete("/admin/desa/{$desa->id}")
        ->assertRedirect()
        ->assertSessionHas('error');
});
