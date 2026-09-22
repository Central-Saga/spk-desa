<?php

use App\Models\Desa;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    $this->admin = User::factory()->superAdmin()->create();
});

it('menampilkan dropdown kabupaten/kecamatan dan input kode pos', function () {
    $html = $this->actingAs($this->admin)->get('/admin/desa/create')->assertOk()->getContent();

    expect($html)
        ->toContain('id="kabupaten"')
        ->toContain('id="kecamatan"')
        ->toContain('id="kode_pos"')
        ->toContain('— Pilih Kabupaten/Kota —')
        ->toContain('— Pilih Kecamatan —')
        ->toContain('var regions =')
        ->toContain('Kota Denpasar');
});

it('menampilkan kecamatan sesuai kabupaten terpilih beserta nilai tersimpan saat edit', function () {
    $desa = Desa::factory()->create([
        'kecamatan' => 'Baturiti',
        'kabupaten' => 'Tabanan',
        'kode_pos' => '82191',
    ]);

    $html = $this->actingAs($this->admin)->get("/admin/desa/{$desa->id}/edit")->assertOk()->getContent();

    // Kabupaten Tabanan terpilih, dan daftar kecamatan berisi kecamatan Tabanan.
    expect($html)->toMatch('/<option value="Tabanan" selected>/')
        ->toContain('Baturiti')
        ->toMatch('/<option value="Baturiti" selected>/')
        ->toContain('Selemadeg Timur')
        // Kode pos tersimpan ikut terisi.
        ->toMatch('/id="kode_pos"[^>]*value="82191"/');
});

it('menampilkan nilai historis di luar daftar resmi agar edit desa lama tidak kehilangan data', function () {
    $desa = Desa::factory()->create([
        'kecamatan' => 'Kecamatan Lama',
        'kabupaten' => 'Kabupaten Lama',
    ]);

    $html = $this->actingAs($this->admin)->get("/admin/desa/{$desa->id}/edit")->assertOk()->getContent();

    expect($html)->toContain('Kecamatan Lama')
        ->toContain('Kabupaten Lama')
        ->toMatch('/<option value="Kecamatan Lama" selected>/');
});
