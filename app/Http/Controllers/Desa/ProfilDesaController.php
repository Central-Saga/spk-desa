<?php

namespace App\Http\Controllers\Desa;

use App\Enums\AksiAudit;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateDesaRequest;
use App\Models\User;
use App\Services\AuditTrailService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class ProfilDesaController extends Controller
{
    public function edit(): View
    {
        /** @var User $user */
        $user = Auth::user();

        abort_unless($user->desa, 403, 'Akun Anda belum terhubung ke desa manapun.');

        $desa = $user->desa;

        return view('desa.profil', compact('desa'));
    }

    public function update(UpdateDesaRequest $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $desa = $user->desa;

        abort_unless($desa, 403, 'Akun Anda belum terhubung ke desa manapun.');

        // Inject parameter route agar UpdateDesaRequest->authorize() bisa cek
        $request->setRouteResolver(function () use ($desa) {
            return tap(request()->route(), function ($route) use ($desa) {
                $route?->setParameter('desa', $desa);
            });
        });

        $data = $request->validated();
        $data['is_active'] = (bool) ($data['is_active'] ?? $desa->is_active);

        $desa->update($data);

        AuditTrailService::record(
            $user,
            AksiAudit::Update,
            "Memperbarui profil desa {$desa->nama}",
            $desa
        );

        return redirect()
            ->route('desa.profil.edit')
            ->with('success', 'Profil desa berhasil diperbarui.');
    }
}
