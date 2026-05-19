<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AksiAudit;
use App\Enums\RoleSlug;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePenggunaRequest;
use App\Http\Requests\Admin\UpdatePenggunaRequest;
use App\Models\Desa;
use App\Models\User;
use App\Services\AuditTrailService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class PenggunaController extends Controller
{
    public function index(Request $request): View
    {
        $query = User::query()
            ->with(['roles', 'desa'])
            ->latest('id');

        if ($keyword = $request->string('q')->trim()->toString()) {
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                    ->orWhere('username', 'like', "%{$keyword}%")
                    ->orWhere('email', 'like', "%{$keyword}%");
            });
        }

        if ($role = $request->string('role')->trim()->toString()) {
            $query->whereHas('roles', fn ($r) => $r->where('name', $role));
        }

        $pengguna = $query->paginate(15)->withQueryString();

        return view('admin.pengguna.index', [
            'pengguna' => $pengguna,
            'roles' => RoleSlug::cases(),
            'filters' => [
                'q' => $request->input('q'),
                'role' => $request->input('role'),
            ],
        ]);
    }

    public function create(): View
    {
        return view('admin.pengguna.create', [
            'roles' => RoleSlug::cases(),
            'desa' => Desa::query()->where('is_active', true)->orderBy('nama')->get(['id', 'nama']),
        ]);
    }

    public function store(StorePenggunaRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $user = User::create([
            'name' => $data['name'],
            'username' => $data['username'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'is_active' => (bool) ($data['is_active'] ?? true),
            'desa_id' => $data['desa_id'] ?? null,
        ]);

        $user->syncRoles([$data['role']]);

        AuditTrailService::record(
            Auth::user(),
            AksiAudit::Create,
            "Membuat pengguna {$user->username}",
            $user,
            ['role' => $data['role']]
        );

        return redirect()
            ->route('admin.pengguna.index')
            ->with('success', "Pengguna {$user->username} berhasil dibuat.");
    }

    public function edit(User $pengguna): View
    {
        $pengguna->load('roles', 'desa');

        return view('admin.pengguna.edit', [
            'pengguna' => $pengguna,
            'roles' => RoleSlug::cases(),
            'desa' => Desa::query()->where('is_active', true)->orderBy('nama')->get(['id', 'nama']),
        ]);
    }

    public function update(UpdatePenggunaRequest $request, User $pengguna): RedirectResponse
    {
        $data = $request->validated();

        $pengguna->fill([
            'name' => $data['name'],
            'username' => $data['username'],
            'email' => $data['email'],
            'is_active' => (bool) ($data['is_active'] ?? false),
            'desa_id' => $data['desa_id'] ?? null,
        ]);

        if (! empty($data['password'])) {
            $pengguna->password = Hash::make($data['password']);
        }

        $pengguna->save();
        $pengguna->syncRoles([$data['role']]);

        AuditTrailService::record(
            Auth::user(),
            AksiAudit::Update,
            "Memperbarui pengguna {$pengguna->username}",
            $pengguna,
            ['role' => $data['role']]
        );

        return redirect()
            ->route('admin.pengguna.index')
            ->with('success', "Pengguna {$pengguna->username} berhasil diperbarui.");
    }

    public function destroy(User $pengguna): RedirectResponse
    {
        if ($pengguna->id === Auth::id()) {
            return back()->with('error', 'Anda tidak dapat menghapus akun sendiri.');
        }

        $username = $pengguna->username;
        $pengguna->delete();

        AuditTrailService::record(
            Auth::user(),
            AksiAudit::Delete,
            "Menghapus pengguna {$username}",
            $pengguna
        );

        return redirect()
            ->route('admin.pengguna.index')
            ->with('success', "Pengguna {$username} berhasil dihapus.");
    }
}
