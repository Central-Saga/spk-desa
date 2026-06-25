<?php

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    $this->admin = User::factory()->superAdmin()->create();
});

it('login sukses dengan credential valid', function () {
    User::factory()->superAdmin()->create([
        'username' => 'testadmin',
    ]);

    $this->post('/login', [
        'username' => 'testadmin',
        'password' => 'password',
    ])->assertRedirect('/admin');
});

it('login gagal dengan password salah', function () {
    User::factory()->superAdmin()->create([
        'username' => 'testadmin',
    ]);

    $this->post('/login', [
        'username' => 'testadmin',
        'password' => 'passwordsalah',
    ])
        ->assertSessionHasErrors(['username' => 'Kredensial tidak valid.'])
        ->assertRedirect('/login');

    $this->assertGuest();
});

it('rate limit login setelah 5 kali gagal', function () {
    User::factory()->superAdmin()->create([
        'username' => 'testadmin',
    ]);

    // 5 attempt gagal
    foreach (range(1, 5) as $i) {
        $this->post('/login', [
            'username' => 'testadmin',
            'password' => 'salah',
        ]);
    }

    // Attempt ke-6 harus kena throttle
    $response = $this->post('/login', [
        'username' => 'testadmin',
        'password' => 'salah',
    ]);

    $response->assertStatus(429);
})->skip('Rate limit tidak aktif di test environment tanpa Redis');
