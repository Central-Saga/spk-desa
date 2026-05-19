<?php

namespace App\Http\Controllers\Desa;

use App\Http\Controllers\Controller;
use App\Models\JawabanKuesioner;
use App\Models\Kuesioner;
use App\Models\NilaiAkhir;
use App\Models\PeriodePenilaian;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(): View
    {
        /** @var User $user */
        $user = Auth::user();
        $desa = $user->desa;

        $periodeAktif = PeriodePenilaian::query()->where('status', 'aktif')->latest('tanggal_mulai')->first();

        $totalIndikator = $periodeAktif
            ? Kuesioner::query()->where('periode_id', $periodeAktif->id)->where('is_active', true)->count()
            : 0;

        $sudahDijawab = ($periodeAktif && $desa)
            ? JawabanKuesioner::query()
                ->where('desa_id', $desa->id)
                ->where('periode_id', $periodeAktif->id)
                ->whereNotNull('jawaban')
                ->count()
            : 0;

        $nilaiAkhir = ($periodeAktif && $desa)
            ? NilaiAkhir::query()
                ->where('desa_id', $desa->id)
                ->where('periode_id', $periodeAktif->id)
                ->first()
            : null;

        return view('desa.dashboard', compact('desa', 'periodeAktif', 'totalIndikator', 'sudahDijawab', 'nilaiAkhir'));
    }
}
