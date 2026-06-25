<?php

namespace App\Http\Controllers;

use App\Enums\AksiAudit;
use App\Models\NilaiAkhir;
use App\Models\PeriodePenilaian;
use App\Models\User;
use App\Services\AuditTrailService;
use App\Services\LaporanService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class LaporanController extends Controller
{
    public function __construct(private LaporanService $laporanService) {}

    public function index(Request $request): View
    {
        /** @var User $user */
        $user = Auth::user();

        $periode = $this->resolvePeriode($request);
        $sidebarTemplate = $this->sidebarFor($user);

        return view('laporan.index', [
            'periode' => $periode,
            'periodeOptions' => PeriodePenilaian::query()
                ->orderBy('tahun', 'desc')->get(['id', 'nama', 'tahun', 'status']),
            'sidebarTemplate' => $sidebarTemplate,
            'canCetakAudit' => $user->isSuperAdmin(),
            'desaList' => $user->isStaffAdminDesa() && $user->desa
                ? collect([$user->desa])
                : NilaiAkhir::query()
                    ->where('periode_id', $periode?->id)
                    ->with('desa')
                    ->orderBy('peringkat')
                    ->get()
                    ->pluck('desa')
                    ->filter()
                    ->values(),
        ]);
    }

    public function rekapitulasi(Request $request, PeriodePenilaian $periode): Response
    {
        /** @var User $user */
        $user = Auth::user();

        $pdf = $this->laporanService->generateRekapitulasi($periode, $user);

        AuditTrailService::record(
            $user,
            AksiAudit::Print,
            "Mencetak laporan rekapitulasi periode {$periode->nama}",
            $periode
        );

        $filename = LaporanService::filenameRekapitulasi($periode);

        return $pdf->stream($filename);
    }

    public function perDesa(Request $request, NilaiAkhir $nilai): Response
    {
        /** @var User $user */
        $user = Auth::user();

        if ($user->isStaffAdminDesa() && $nilai->desa_id !== $user->desa_id) {
            abort(403, 'Anda hanya dapat mencetak laporan desa Anda sendiri.');
        }

        $pdf = $this->laporanService->generatePerDesa($nilai, $user);

        AuditTrailService::record(
            $user,
            AksiAudit::Print,
            'Mencetak laporan per desa '.($nilai->desa?->nama ?? 'Desa tidak tersedia')." periode {$nilai->periode->nama}",
            $nilai
        );

        $filename = LaporanService::filenamePerDesa($nilai);

        return $pdf->stream($filename);
    }

    public function auditTrail(Request $request): Response
    {
        /** @var User $user */
        $user = Auth::user();

        abort_unless($user->isSuperAdmin(), 403);

        $from = $request->date('from') ?? now()->subDays(30);
        $to = $request->date('to') ?? now();

        $pdf = $this->laporanService->generateAuditTrail($from, $to, $user);

        AuditTrailService::record(
            $user,
            AksiAudit::Print,
            "Mencetak laporan audit trail {$from->format('Y-m-d')} sd {$to->format('Y-m-d')}",
        );

        $filename = LaporanService::filenameAuditTrail($from, $to);

        return $pdf->stream($filename);
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

    private function sidebarFor(User $user): string
    {
        return match (true) {
            $user->isSuperAdmin() => 'admin.partials.sidebar',
            $user->isStaffAdminDesa() => 'desa.partials.sidebar',
            $user->isStaffPenilaian() => 'penilai.partials.sidebar',
            $user->isPimpinan() => 'pimpinan.partials.sidebar',
            default => 'admin.partials.sidebar',
        };
    }
}
