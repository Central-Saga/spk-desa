<?php

namespace App\Http\Controllers\Auth;

use App\Enums\AksiAudit;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use App\Services\AuditTrailService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function showLoginForm(): View
    {
        return view('auth.login');
    }

    public function authenticate(LoginRequest $request): RedirectResponse
    {
        $credentials = $request->validated();

        /** @var User|null $user */
        $user = User::query()
            ->where('username', $credentials['username'])
            ->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'username' => 'Kredensial tidak valid.',
            ])->redirectTo(route('login'));
        }

        if (! $user->is_active) {
            throw ValidationException::withMessages([
                'username' => 'Akun Anda telah dinonaktifkan. Hubungi administrator.',
            ])->redirectTo(route('login'));
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        AuditTrailService::record($user, AksiAudit::Login, 'Berhasil login ke sistem');

        return redirect()->intended($this->dashboardRouteFor($user));
    }

    public function logout(Request $request): RedirectResponse
    {
        $user = Auth::user();

        if ($user) {
            AuditTrailService::record($user, AksiAudit::Logout, 'Logout dari sistem');
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    private function dashboardRouteFor(User $user): string
    {
        $slug = $user->primaryRoleSlug();

        return route($slug?->dashboardRoute() ?? 'login');
    }
}
