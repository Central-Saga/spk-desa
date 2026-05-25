<?php

use App\Models\Desa;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

it('redirect ke login saat akses dashboard tanpa autentikasi', function () {
    $this->get('/dashboard')->assertRedirect('/login');
});

it('login sukses dengan credential valid + redirect ke dashboard role', function () {
    $user = User::factory()->superAdmin()->create([
        'username' => 'tester',
    ]);

    $this->post('/login', [
        'username' => 'tester',
        'password' => 'password',
    ])->assertRedirect('/admin');

    $this->assertAuthenticatedAs($user);
});

it('login gagal dengan credential salah', function () {
    User::factory()->superAdmin()->create(['username' => 'tester']);

    $this->post('/login', [
        'username' => 'tester',
        'password' => 'salah1234',
    ])->assertSessionHasErrors('username');

    $this->assertGuest();
});

it('login gagal saat akun nonaktif', function () {
    User::factory()->superAdmin()->nonaktif()->create(['username' => 'tester']);

    $this->post('/login', [
        'username' => 'tester',
        'password' => 'password',
    ])->assertSessionHasErrors('username');

    $this->assertGuest();
});

it('Super Admin dapat akses semua menu admin', function () {
    $user = User::factory()->superAdmin()->create();

    $this->actingAs($user);

    $this->get('/admin')->assertOk();
    $this->get('/admin/pengguna')->assertOk();
    $this->get('/admin/desa')->assertOk();
    $this->get('/admin/periode')->assertOk();
    $this->get('/admin/kuesioner')->assertOk();
    $this->get('/admin/nilai-akhir')->assertOk();
    $this->get('/admin/audit-trail')->assertOk();
});

it('Staff Admin Desa diblokir akses /admin/* dengan 403', function () {
    $desa = Desa::factory()->create();
    $user = User::factory()->staffAdminDesa()->create(['desa_id' => $desa->id]);

    $this->actingAs($user);

    $this->get('/admin/pengguna')->assertForbidden();
    $this->get('/admin/desa')->assertForbidden();
    $this->get('/admin/audit-trail')->assertForbidden();
});

it('Pimpinan diblokir akses /admin/* dan /penilai/* dengan 403', function () {
    $user = User::factory()->pimpinan()->create();

    $this->actingAs($user);

    $this->get('/admin/pengguna')->assertForbidden();
    $this->get('/penilai/jadwal-visitasi')->assertForbidden();
});

it('Pimpinan dapat akses dashboard pimpinan + hasil + laporan', function () {
    $user = User::factory()->pimpinan()->create();

    $this->actingAs($user);

    $this->get('/pimpinan')->assertOk();
    $this->get('/hasil-penilaian')->assertOk();
    $this->get('/laporan')->assertOk();
});

it('Staff Penilaian dapat akses jadwal + penilaian visitasi', function () {
    $user = User::factory()->staffPenilaian()->create();

    $this->actingAs($user);

    $this->get('/penilai')->assertOk();
    $this->get('/penilai/jadwal-visitasi')->assertOk();
    $this->get('/penilai/penilaian-visitasi')->assertOk();
});

it('logout invalidate session + redirect ke login', function () {
    $user = User::factory()->superAdmin()->create();

    $this->actingAs($user)
        ->post('/logout')
        ->assertRedirect('/login');

    $this->assertGuest();
});
