<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Desa;
use App\Models\JadwalVisitasi;
use App\Models\NilaiAkhir;
use App\Models\PeriodePenilaian;
use App\Models\User;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'total_pengguna' => User::query()->count(),
            'total_desa' => Desa::query()->where('is_active', true)->count(),
            'total_periode_aktif' => PeriodePenilaian::query()->where('status', 'aktif')->count(),
            'total_visitasi_terjadwal' => JadwalVisitasi::query()->where('status', 'terjadwal')->count(),
        ];

        $rankingTerbaru = NilaiAkhir::query()
            ->with(['desa', 'periode'])
            ->orderBy('peringkat')
            ->limit(5)
            ->get();

        return view('admin.dashboard', compact('stats', 'rankingTerbaru'));
    }
}
