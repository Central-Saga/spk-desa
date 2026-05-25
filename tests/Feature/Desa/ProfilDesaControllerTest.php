<?php

use App\Models\Desa;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    $this->admin = User::factory()->superAdmin()->create();
});

it('Staff Admin Desa dapat lihat profil desanya', function () {
    $desa = Desa::factory()->create();
    $user = User::factory()->staffAdminDesa()->create(['desa_id' => $desa->id]);

    $this->actingAs($user)
        ->get('/desa/profil')
        ->assertOk()
        ->assertSee($desa->nama);
});

it('Staff Admin Desa dapat update profil desanya', function () {
    $desa = Desa::factory()->create(['nama' => 'Lama']);
    $user = User::factory()->staffAdminDesa()->create(['desa_id' => $desa->id]);

    $this->actingAs($user)
        ->put('/desa/profil', [
            'nama' => 'Updated',
            'alamat' => $desa->alamat,
            'kecamatan' => $desa->kecamatan,
            'kabupaten' => $desa->kabupaten,
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    expect($desa->fresh()->nama)->toBe('Updated');
});

it('Staff Admin Desa tanpa desa diblokir akses profil', function () {
    $user = User::factory()->staffAdminDesa()->create();

    $this->actingAs($user)
        ->get('/desa/profil')
        ->assertForbidden();
});

it('Staff Admin Desa tidak boleh edit desa lain', function () {
    $desaA = Desa::factory()->create(['nama' => 'Desa A']);
    $desaB = Desa::factory()->create(['nama' => 'Desa B']);
    $user = User::factory()->staffAdminDesa()->create(['desa_id' => $desaA->id]);

    // User terhubung ke desaA, coba akses desaB via URL admin
    $this->actingAs($user)
        ->get("/admin/desa/{$desaB->id}/edit")
        ->assertForbidden();
});
