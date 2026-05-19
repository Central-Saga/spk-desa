<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AksiAudit;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreKuesionerRequest;
use App\Http\Requests\Admin\UpdateKuesionerRequest;
use App\Models\Kuesioner;
use App\Models\PeriodePenilaian;
use App\Services\AuditTrailService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KuesionerController extends Controller
{
    public function index(Request $request): View
    {
        $periode = $this->resolvePeriode($request);

        $kuesioner = $periode
            ? Kuesioner::query()
                ->where('periode_id', $periode->id)
                ->orderBy('kategori')
                ->orderBy('urutan')
                ->get()
            : collect();

        $totalBobot = (float) $kuesioner->sum('bobot_indikator');

        return view('admin.kuesioner.index', [
            'periode' => $periode,
            'periodeOptions' => PeriodePenilaian::query()->orderBy('tahun', 'desc')->get(['id', 'nama', 'tahun', 'status']),
            'kuesioner' => $kuesioner,
            'totalBobot' => $totalBobot,
        ]);
    }

    public function create(Request $request): View
    {
        $periode = $this->resolvePeriode($request);

        abort_unless($periode, 422, 'Pilih periode terlebih dahulu.');

        $totalBobot = (float) Kuesioner::query()
            ->where('periode_id', $periode->id)
            ->sum('bobot_indikator');

        $maxUrutan = (int) Kuesioner::query()
            ->where('periode_id', $periode->id)
            ->max('urutan');

        return view('admin.kuesioner.create', [
            'periode' => $periode,
            'totalBobot' => $totalBobot,
            'sisaBobot' => max(0, round(100 - $totalBobot, 2)),
            'urutanBerikutnya' => $maxUrutan + 1,
        ]);
    }

    public function store(StoreKuesionerRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['is_active'] = (bool) ($data['is_active'] ?? true);

        $kuesioner = Kuesioner::create($data);

        AuditTrailService::record(
            Auth::user(),
            AksiAudit::Create,
            "Menambah indikator kuesioner [{$kuesioner->kode_indikator}] pada periode #{$kuesioner->periode_id}",
            $kuesioner
        );

        return redirect()
            ->route('admin.kuesioner.index', ['periode' => $kuesioner->periode_id])
            ->with('success', "Indikator {$kuesioner->kode_indikator} berhasil ditambahkan.");
    }

    public function edit(Kuesioner $kuesioner): View
    {
        $totalBobot = (float) Kuesioner::query()
            ->where('periode_id', $kuesioner->periode_id)
            ->sum('bobot_indikator');

        return view('admin.kuesioner.edit', [
            'kuesioner' => $kuesioner,
            'periode' => $kuesioner->periode,
            'totalBobot' => $totalBobot,
            'sisaBobot' => max(0, round(100 - ($totalBobot - (float) $kuesioner->bobot_indikator), 2)),
        ]);
    }

    public function update(UpdateKuesionerRequest $request, Kuesioner $kuesioner): RedirectResponse
    {
        $data = $request->validated();
        $data['is_active'] = (bool) ($data['is_active'] ?? false);

        $kuesioner->update($data);

        AuditTrailService::record(
            Auth::user(),
            AksiAudit::Update,
            "Memperbarui indikator [{$kuesioner->kode_indikator}]",
            $kuesioner
        );

        return redirect()
            ->route('admin.kuesioner.index', ['periode' => $kuesioner->periode_id])
            ->with('success', "Indikator {$kuesioner->kode_indikator} berhasil diperbarui.");
    }

    public function destroy(Kuesioner $kuesioner): RedirectResponse
    {
        $kode = $kuesioner->kode_indikator;
        $periodeId = $kuesioner->periode_id;

        if ($kuesioner->jawaban()->exists()) {
            return back()->with('error', "Indikator {$kode} sudah memiliki jawaban dari desa dan tidak dapat dihapus.");
        }

        $kuesioner->delete();

        AuditTrailService::record(
            Auth::user(),
            AksiAudit::Delete,
            "Menghapus indikator [{$kode}]",
            $kuesioner
        );

        return redirect()
            ->route('admin.kuesioner.index', ['periode' => $periodeId])
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
