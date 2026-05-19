<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

final class EnsureRole
{
    public function handle(Request $request, Closure $next, string ...$rolesYangDiizinkan): Response
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        /** @var User $user */
        $user = Auth::user();

        if (! $user->hasAnyRole($rolesYangDiizinkan)) {
            abort(403, 'Anda tidak memiliki hak akses untuk halaman ini.');
        }

        return $next($request);
    }
}
