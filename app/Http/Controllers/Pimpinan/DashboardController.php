<?php

namespace App\Http\Controllers\Pimpinan;

use App\Http\Controllers\Controller;
use App\Models\NilaiAkhir;
use App\Models\PeriodePenilaian;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $periodeAktif = PeriodePenilaian::query()->where('status', 'aktif')->latest('tanggal_mulai')->first();

        $ranking = $periodeAktif
            ? NilaiAkhir::query()
                ->with('desa')
                ->where('periode_id', $periodeAktif->id)
                ->orderBy('peringkat')
                ->get()
            : collect();

        $stats = [
            'total_periode' => PeriodePenilaian::query()->count(),
            'periode_aktif' => $periodeAktif?->nama ?? 'Belum ada periode aktif',
            'total_desa_dinilai' => $ranking->count(),
            'rata_rata_nilai' => round((float) $ranking->avg('nilai_akhir'), 2),
        ];

        return view('pimpinan.dashboard', compact('stats', 'ranking', 'periodeAktif'));
    }
}
