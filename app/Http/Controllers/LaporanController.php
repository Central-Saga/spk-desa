<?php

namespace App\Http\Controllers;

use App\Enums\AksiAudit;
use App\Models\AuditTrail;
use App\Models\JawabanKuesioner;
use App\Models\NilaiAkhir;
use App\Models\PenilaianVisitasi;
use App\Models\PeriodePenilaian;
use App\Models\User;
use App\Services\AuditTrailService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class LaporanController extends Controller
{
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

        $hasil = NilaiAkhir::query()
            ->with('desa')
            ->where('periode_id', $periode->id)
            ->when(
                $user->isStaffAdminDesa() && $user->desa_id,
                fn ($q) => $q->where('desa_id', $user->desa_id)
            )
            ->orderBy('peringkat')
            ->get();

        $pdf = Pdf::loadView('laporan.pdf.rekapitulasi', [
            'periode' => $periode,
            'hasil' => $hasil,
            'pencetak' => $user,
            'tanggalCetak' => now(),
        ])->setPaper('a4', 'landscape');

        AuditTrailService::record(
            $user,
            AksiAudit::Print,
            "Mencetak laporan rekapitulasi periode {$periode->nama}",
            $periode
        );

        $filename = 'laporan-rekapitulasi-'.Str::slug($periode->nama).'-'.now()->format('Ymd-His').'.pdf';

        return $pdf->stream($filename);
    }

    public function perDesa(Request $request, NilaiAkhir $nilai): Response
    {
        /** @var User $user */
        $user = Auth::user();

        if ($user->isStaffAdminDesa() && $nilai->desa_id !== $user->desa_id) {
            abort(403, 'Anda hanya dapat mencetak laporan desa Anda sendiri.');
        }

        $nilai->load(['desa', 'periode']);

        $jawaban = JawabanKuesioner::query()
            ->where('desa_id', $nilai->desa_id)
            ->where('periode_id', $nilai->periode_id)
            ->with('kuesioner')
            ->get()
            ->sortBy(fn ($j) => $j->kuesioner?->urutan ?? 999);

        $visitasi = PenilaianVisitasi::query()
            ->where('desa_id', $nilai->desa_id)
            ->where('periode_id', $nilai->periode_id)
            ->orderBy('indikator_visitasi')
            ->get();

        $pdf = Pdf::loadView('laporan.pdf.per-desa', [
            'nilai' => $nilai,
            'jawaban' => $jawaban,
            'visitasi' => $visitasi,
            'pencetak' => $user,
            'tanggalCetak' => now(),
        ])->setPaper('a4', 'portrait');

        AuditTrailService::record(
            $user,
            AksiAudit::Print,
            "Mencetak laporan per desa {$nilai->desa->nama} periode {$nilai->periode->nama}",
            $nilai
        );

        $filename = 'laporan-per-desa-'.Str::slug($nilai->desa->nama).'-'.Str::slug($nilai->periode->nama).'.pdf';

        return $pdf->stream($filename);
    }

    public function auditTrail(Request $request): Response
    {
        /** @var User $user */
        $user = Auth::user();

        abort_unless($user->isSuperAdmin(), 403);

        $from = $request->date('from') ?? now()->subDays(30);
        $to = $request->date('to') ?? now();

        $audit = AuditTrail::query()
            ->with('user')
            ->whereBetween('created_at', [$from->startOfDay(), $to->endOfDay()])
            ->orderByDesc('created_at')
            ->get();

        $pdf = Pdf::loadView('laporan.pdf.audit-trail', [
            'audit' => $audit,
            'from' => $from,
            'to' => $to,
            'pencetak' => $user,
            'tanggalCetak' => now(),
        ])->setPaper('a4', 'landscape');

        AuditTrailService::record(
            $user,
            AksiAudit::Print,
            "Mencetak laporan audit trail {$from->format('Y-m-d')} sd {$to->format('Y-m-d')}",
        );

        $filename = 'laporan-audit-trail-'.$from->format('Ymd').'-'.$to->format('Ymd').'.pdf';

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
