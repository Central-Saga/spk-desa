<?php

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;

it('menampilkan form lupa password', function () {
    $this->get(route('password.request'))->assertOk()->assertSee('Lupa Password');
});

it('mengirim tautan reset untuk email terdaftar', function () {
    Notification::fake();
    $user = User::factory()->create(['email' => 'user@contoh.id']);

    $this->post(route('password.email'), ['email' => $user->email])
        ->assertRedirect()
        ->assertSessionHas('status');

    Notification::assertSentTo($user, ResetPassword::class);
});

it('menolak email yang tidak terdaftar dengan error', function () {
    $this->from(route('password.request'))
        ->post(route('password.email'), ['email' => 'tidakada@contoh.id'])
        ->assertSessionHasErrors('email');
});

it('reset password dengan token valid', function () {
    Notification::fake();
    $user = User::factory()->create();

    $this->post(route('password.email'), ['email' => $user->email]);

    $token = '';

    Notification::assertSentTo($user, ResetPassword::class, function ($notification) use (&$token) {
        $token = $notification->token;

        return true;
    });

    $this->post(route('password.update'), [
        'token' => $token,
        'email' => $user->email,
        'password' => 'PasswordBaru123!',
        'password_confirmation' => 'PasswordBaru123!',
    ])->assertRedirect(route('login'))->assertSessionHas('status');

    expect(Hash::check('PasswordBaru123!', $user->fresh()->password))->toBeTrue();
});
