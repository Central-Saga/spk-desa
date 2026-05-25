<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AksiAudit;
use App\Enums\StatusPeriode;
use App\Http\Controllers\Controller;
use App\Models\Desa;
use App\Models\NilaiAkhir;
use App\Models\PeriodePenilaian;
use App\Models\User;
use App\Services\AuditTrailService;
use App\Services\PerhitunganNilaiService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NilaiAkhirController extends Controller
{
    public function __construct(private PerhitunganNilaiService $service) {}

    public function index(Request $request): View
    {
        $periode = $this->resolvePeriode($request);

        $kelengkapan = collect();
        $hasil = collect();

        if ($periode) {
            $desa = Desa::query()->where('is_active', true)->orderBy('nama')->get();

            $kelengkapan = $desa->mapWithKeys(fn (Desa $d) => [
                $d->id => array_merge(
                    ['desa' => $d],
                    $this->service->cekKelengkapan($d, $periode)
                ),
            ]);

            $hasil = NilaiAkhir::query()
                ->with('desa')
                ->where('periode_id', $periode->id)
                ->has('desa')
                ->orderBy('peringkat')
                ->get();
        }

        return view('admin.nilai-akhir.index', [
            'periode' => $periode,
            'periodeOptions' => PeriodePenilaian::query()->orderBy('tahun', 'desc')->get(['id', 'nama', 'tahun', 'status']),
            'kelengkapan' => $kelengkapan,
            'hasil' => $hasil,
            'sidebarTemplate' => 'admin.partials.sidebar',
        ]);
    }

    public function hitung(Request $request, PeriodePenilaian $periode): RedirectResponse
    {
        if ($periode->status === StatusPeriode::Selesai) {
            return back()->with('error', 'Periode sudah selesai. Recompute tidak diizinkan.');
        }

        /** @var User $admin */
        $admin = Auth::user();

        $hasil = $this->service->hitungSemuaDesa($periode, $admin);

        AuditTrailService::record(
            $admin,
            AksiAudit::ComputeNilai,
            "Menghitung nilai akhir periode {$periode->nama}",
            $periode,
            ['jumlah_desa' => $hasil->count()]
        );

        return redirect()
            ->route('admin.nilai-akhir.index', ['periode' => $periode->id])
            ->with('success', "Perhitungan nilai akhir selesai untuk {$hasil->count()} desa.");
    }

    private function resolvePeriode(Request $request): ?PeriodePenilaian
    {
        if ($id = $request->integer('periode')) {
            return PeriodePenilaian::find($id);
        }

        return PeriodePenilaian::query()
            ->where('status', StatusPeriode::Aktif->value)
            ->latest('tanggal_mulai')
            ->first()
            ?? PeriodePenilaian::query()->latest('id')->first();
    }
}
