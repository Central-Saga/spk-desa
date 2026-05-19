<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AksiAudit;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreDesaRequest;
use App\Http\Requests\Admin\UpdateDesaRequest;
use App\Models\Desa;
use App\Services\AuditTrailService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DesaController extends Controller
{
    public function index(Request $request): View
    {
        $query = Desa::query()->withCount('users')->latest('id');

        if ($keyword = $request->string('q')->trim()->toString()) {
            $query->where(function ($q) use ($keyword) {
                $q->where('nama', 'like', "%{$keyword}%")
                    ->orWhere('kecamatan', 'like', "%{$keyword}%")
                    ->orWhere('kabupaten', 'like', "%{$keyword}%");
            });
        }

        $desa = $query->paginate(15)->withQueryString();

        return view('admin.desa.index', [
            'desa' => $desa,
            'filters' => ['q' => $request->input('q')],
        ]);
    }

    public function create(): View
    {
        return view('admin.desa.create');
    }

    public function store(StoreDesaRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['is_active'] = (bool) ($data['is_active'] ?? true);

        $desa = Desa::create($data);

        AuditTrailService::record(
            Auth::user(),
            AksiAudit::Create,
            "Membuat desa {$desa->nama}",
            $desa
        );

        return redirect()
            ->route('admin.desa.index')
            ->with('success', "Desa {$desa->nama} berhasil dibuat.");
    }

    public function edit(Desa $desa): View
    {
        return view('admin.desa.edit', compact('desa'));
    }

    public function update(UpdateDesaRequest $request, Desa $desa): RedirectResponse
    {
        $data = $request->validated();
        $data['is_active'] = (bool) ($data['is_active'] ?? false);

        $desa->update($data);

        AuditTrailService::record(
            Auth::user(),
            AksiAudit::Update,
            "Memperbarui desa {$desa->nama}",
            $desa
        );

        return redirect()
            ->route('admin.desa.index')
            ->with('success', "Desa {$desa->nama} berhasil diperbarui.");
    }

    public function destroy(Desa $desa): RedirectResponse
    {
        $nama = $desa->nama;

        if ($desa->users()->exists()) {
            return back()->with('error', "Desa {$nama} masih terhubung dengan pengguna. Pindahkan pengguna terlebih dahulu.");
        }

        $desa->delete();

        AuditTrailService::record(
            Auth::user(),
            AksiAudit::Delete,
            "Menghapus desa {$nama}",
            $desa
        );

        return redirect()
            ->route('admin.desa.index')
            ->with('success', "Desa {$nama} berhasil dihapus.");
    }
}
