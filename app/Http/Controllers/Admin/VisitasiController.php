<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AksiAudit;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreVisitasiRequest;
use App\Http\Requests\Admin\UpdateVisitasiRequest;
use App\Models\Desa;
use App\Models\IndikatorVisitasi;
use App\Models\PeriodePenilaian;
use App\Services\AuditTrailService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VisitasiController extends Controller
{
    /**
     * Resolve filter desa dari request.
     * - null/empty string → semua indikator (tanpa filter)
     * - "0" → hanya indikator global (desa_id = null)
     * - angka → hanya indikator untuk desa tertentu
     */
    private function resolveDesaFilter(string|int|null $input): array
    {
        // [filterValue, queryScope]
        return match (true) {
            $input === '' || $input === null => [null, null],
            $input === '0' || $input === 0 => [0, 'global'],
            default => [(int) $input, 'desa'],
        };
    }

    public function index(Request $request): View
    {
        $periode = $this->resolvePeriode($request);
        [$filterDesaId, $filterScope] = $this->resolveDesaFilter($request->input('desa_id'));

        $visitasi = $periode
            ? IndikatorVisitasi::query()
                ->where('periode_id', $periode->id)
                ->when($filterScope === 'global', fn ($q) => $q->whereNull('desa_id'))
                ->when($filterScope === 'desa', fn ($q) => $q->where('desa_id', $filterDesaId))
                ->orderBy('kategori')
                ->orderBy('urutan')
                ->get()
            : collect();

        $totalBobot = (float) $visitasi->sum('bobot');

        return view('admin.visitasi.index', [
            'periode' => $periode,
            'periodeOptions' => PeriodePenilaian::query()->orderBy('tahun', 'desc')->get(['id', 'nama', 'tahun', 'status']),
            'desaOptions' => Desa::query()->where('is_active', true)->orderBy('nama')->get(['id', 'nama']),
            'visitasi' => $visitasi,
            'totalBobot' => $totalBobot,
            'filterDesaId' => $filterDesaId,
        ]);
    }

    public function create(Request $request): View
    {
        $periode = $this->resolvePeriode($request);

        abort_unless($periode, 422, 'Pilih periode terlebih dahulu.');

        [$desaId] = $this->resolveDesaFilter($request->input('desa_id'));

        $totalBobot = (float) IndikatorVisitasi::query()
            ->where('periode_id', $periode->id)
            ->when($desaId === 0, fn ($q) => $q->whereNull('desa_id'))
            ->when($desaId > 0, fn ($q) => $q->where('desa_id', $desaId))
            ->sum('bobot');

        $maxUrutan = (int) IndikatorVisitasi::query()
            ->where('periode_id', $periode->id)
            ->when($desaId === 0, fn ($q) => $q->whereNull('desa_id'))
            ->when($desaId > 0, fn ($q) => $q->where('desa_id', $desaId))
            ->max('urutan');

        return view('admin.visitasi.create', [
            'periode' => $periode,
            'desaOptions' => Desa::query()->where('is_active', true)->orderBy('nama')->get(['id', 'nama']),
            'defaultDesaId' => $desaId,
            'totalBobot' => $totalBobot,
            'sisaBobot' => max(0, round(100 - $totalBobot, 2)),
            'urutanBerikutnya' => $maxUrutan + 1,
        ]);
    }

    public function store(StoreVisitasiRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['is_active'] = (bool) ($data['is_active'] ?? true);

        if (! array_key_exists('desa_id', $data) || $data['desa_id'] === '' || $data['desa_id'] === null) {
            $data['desa_id'] = null;
        }

        $visitasi = IndikatorVisitasi::create($data);

        AuditTrailService::record(
            Auth::user(),
            AksiAudit::Create,
            "Menambah indikator visitasi [{$visitasi->kode}] pada periode #{$visitasi->periode_id}",
            $visitasi
        );

        $params = ['periode' => $visitasi->periode_id];
        if ($visitasi->desa_id) {
            $params['desa_id'] = $visitasi->desa_id;
        }

        return redirect()
            ->route('admin.visitasi.index', $params)
            ->with('success', "Indikator {$visitasi->kode} berhasil ditambahkan.");
    }

    public function edit(IndikatorVisitasi $visitasi): View
    {
        $totalBobot = (float) IndikatorVisitasi::query()
            ->where('periode_id', $visitasi->periode_id)
            ->where('desa_id', $visitasi->desa_id)
            ->sum('bobot');

        return view('admin.visitasi.edit', [
            'visitasi' => $visitasi,
            'periode' => $visitasi->periode,
            'desaOptions' => Desa::query()->where('is_active', true)->orderBy('nama')->get(['id', 'nama']),
            'totalBobot' => $totalBobot,
            'sisaBobot' => max(0, round(100 - ($totalBobot - (float) $visitasi->bobot), 2)),
        ]);
    }

    public function update(UpdateVisitasiRequest $request, IndikatorVisitasi $visitasi): RedirectResponse
    {
        $data = $request->validated();
        $data['is_active'] = (bool) ($data['is_active'] ?? false);

        if (! array_key_exists('desa_id', $data) || $data['desa_id'] === '' || $data['desa_id'] === null) {
            $data['desa_id'] = null;
        }

        $visitasi->update($data);

        AuditTrailService::record(
            Auth::user(),
            AksiAudit::Update,
            "Memperbarui indikator visitasi [{$visitasi->kode}]",
            $visitasi
        );

        $params = ['periode' => $visitasi->periode_id];
        if ($visitasi->desa_id) {
            $params['desa_id'] = $visitasi->desa_id;
        }

        return redirect()
            ->route('admin.visitasi.index', $params)
            ->with('success', "Indikator {$visitasi->kode} berhasil diperbarui.");
    }

    public function destroy(IndikatorVisitasi $visitasi): RedirectResponse
    {
        $kode = $visitasi->kode;
        $periodeId = $visitasi->periode_id;
        $desaId = $visitasi->desa_id;

        $visitasi->delete();

        AuditTrailService::record(
            Auth::user(),
            AksiAudit::Delete,
            "Menghapus indikator visitasi [{$kode}]",
            $visitasi
        );

        $params = ['periode' => $periodeId];
        if ($desaId) {
            $params['desa_id'] = $desaId;
        }

        return redirect()
            ->route('admin.visitasi.index', $params)
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
