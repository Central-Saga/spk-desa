<?php

namespace App\Services;

use App\Enums\AksiAudit;
use App\Models\AuditTrail;
use App\Models\JawabanKuesioner;
use App\Models\NilaiAkhir;
use App\Models\PenilaianVisitasi;
use App\Models\PeriodePenilaian;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Str;

final class LaporanService
{
    /**
     * Generate PDF rekapitulasi periode.
     */
    public function generateRekapitulasi(PeriodePenilaian $periode, User $pencetak): \Barryvdh\DomPDF\PDF
    {
        $hasil = NilaiAkhir::query()
            ->with('desa')
            ->where('periode_id', $periode->id)
            ->orderBy('peringkat')
            ->get();

        return Pdf::loadView('laporan.pdf.rekapitulasi', [
            'periode' => $periode,
            'hasil' => $hasil,
            'pencetak' => $pencetak,
            'tanggalCetak' => now(),
        ])->setPaper('a4', 'landscape');
    }

    /**
     * Generate PDF laporan per desa.
     */
    public function generatePerDesa(NilaiAkhir $nilai, User $pencetak): \Barryvdh\DomPDF\PDF
    {
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

        return Pdf::loadView('laporan.pdf.per-desa', [
            'nilai' => $nilai,
            'jawaban' => $jawaban,
            'visitasi' => $visitasi,
            'pencetak' => $pencetak,
            'tanggalCetak' => now(),
        ])->setPaper('a4', 'portrait');
    }

    /**
     * Generate PDF audit trail.
     */
    public function generateAuditTrail(Carbon $from, Carbon $to, User $pencetak): \Barryvdh\DomPDF\PDF
    {
        $audit = AuditTrail::query()
            ->with('user')
            ->whereBetween('created_at', [$from->startOfDay(), $to->endOfDay()])
            ->orderByDesc('created_at')
            ->get();

        return Pdf::loadView('laporan.pdf.audit-trail', [
            'audit' => $audit,
            'from' => $from,
            'to' => $to,
            'pencetak' => $pencetak,
            'tanggalCetak' => now(),
        ])->setPaper('a4', 'landscape');
    }

    /**
     * Catat aksi cetak ke audit trail.
     */
    public function catatAudit(User $pencetak, AksiAudit $aksi, string $deskripsi, ?object $subject = null): void
    {
        AuditTrailService::record($pencetak, $aksi, $deskripsi, $subject);
    }

    /**
     * Build filename for laporan.
     */
    public static function filenameRekapitulasi(PeriodePenilaian $periode): string
    {
        return 'laporan-rekapitulasi-'.Str::slug($periode->nama).'-'.now()->format('Ymd-His').'.pdf';
    }

    public static function filenamePerDesa(NilaiAkhir $nilai): string
    {
        return 'laporan-per-desa-'.Str::slug($nilai->desa?->nama ?? 'unknown').'-'.Str::slug($nilai->periode->nama).'.pdf';
    }

    public static function filenameAuditTrail(Carbon $from, Carbon $to): string
    {
        return 'laporan-audit-trail-'.$from->format('Ymd').'-'.$to->format('Ymd').'.pdf';
    }
}
