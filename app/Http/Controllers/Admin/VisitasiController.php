<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AksiAudit;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreVisitasiRequest;
use App\Http\Requests\Admin\UpdateVisitasiRequest;
use App\Models\IndikatorVisitasi;
use App\Models\PeriodePenilaian;
use App\Services\AuditTrailService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VisitasiController extends Controller
{
    public function index(Request $request): View
    {
        $periode = $this->resolvePeriode($request);

        $visitasi = $periode
            ? IndikatorVisitasi::query()
                ->where('periode_id', $periode->id)
                ->orderBy('kategori')
                ->orderBy('urutan')
                ->get()
            : collect();

        $totalBobot = (float) $visitasi->sum('bobot');

        return view('admin.visitasi.index', [
            'periode' => $periode,
            'periodeOptions' => PeriodePenilaian::query()->orderBy('tahun', 'desc')->get(['id', 'nama', 'tahun', 'status']),
            'visitasi' => $visitasi,
            'totalBobot' => $totalBobot,
        ]);
    }

    public function create(Request $request): View
    {
        $periode = $this->resolvePeriode($request);

        abort_unless($periode, 422, 'Pilih periode terlebih dahulu.');

        $totalBobot = (float) IndikatorVisitasi::query()
            ->where('periode_id', $periode->id)
            ->sum('bobot');

        $maxUrutan = (int) IndikatorVisitasi::query()
            ->where('periode_id', $periode->id)
            ->max('urutan');

        return view('admin.visitasi.create', [
            'periode' => $periode,
            'totalBobot' => $totalBobot,
            'sisaBobot' => max(0, round(100 - $totalBobot, 2)),
            'urutanBerikutnya' => $maxUrutan + 1,
        ]);
    }

    public function store(StoreVisitasiRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['is_active'] = (bool) ($data['is_active'] ?? true);

        $visitasi = IndikatorVisitasi::create($data);

        AuditTrailService::record(
            Auth::user(),
            AksiAudit::Create,
            "Menambah indikator visitasi [{$visitasi->kode}] pada periode #{$visitasi->periode_id}",
            $visitasi
        );

        return redirect()
            ->route('admin.visitasi.index', ['periode' => $visitasi->periode_id])
            ->with('success', "Indikator {$visitasi->kode} berhasil ditambahkan.");
    }

    public function edit(IndikatorVisitasi $visitasi): View
    {
        $totalBobot = (float) IndikatorVisitasi::query()
            ->where('periode_id', $visitasi->periode_id)
            ->sum('bobot');

        return view('admin.visitasi.edit', [
            'visitasi' => $visitasi,
            'periode' => $visitasi->periode,
            'totalBobot' => $totalBobot,
            'sisaBobot' => max(0, round(100 - ($totalBobot - (float) $visitasi->bobot), 2)),
        ]);
    }

    public function update(UpdateVisitasiRequest $request, IndikatorVisitasi $visitasi): RedirectResponse
    {
        $data = $request->validated();
        $data['is_active'] = (bool) ($data['is_active'] ?? false);

        $visitasi->update($data);

        AuditTrailService::record(
            Auth::user(),
            AksiAudit::Update,
            "Memperbarui indikator visitasi [{$visitasi->kode}]",
            $visitasi
        );

        return redirect()
            ->route('admin.visitasi.index', ['periode' => $visitasi->periode_id])
            ->with('success', "Indikator {$visitasi->kode} berhasil diperbarui.");
    }

    public function destroy(IndikatorVisitasi $visitasi): RedirectResponse
    {
        $kode = $visitasi->kode;
        $periodeId = $visitasi->periode_id;

        $visitasi->delete();

        AuditTrailService::record(
            Auth::user(),
            AksiAudit::Delete,
            "Menghapus indikator visitasi [{$kode}]",
            $visitasi
        );

        return redirect()
            ->route('admin.visitasi.index', ['periode' => $periodeId])
            ->with('success', "Indikator {$kode} berhasil dihapus.");
    }

    private function resolvePeriode(Request $request): ?PeriodePenilaian
    {
        if ($id = $request->integer('periode')) {
            return PeriodePenilaian::find($id);
        }

        return PeriodePenilaian::query()
            ->where('status', 'aktif')
            ->latest('tanggal_mulai')
            ->first()
            ?? PeriodePenilaian::query()->latest('id')->first();
    }
}
