<?php

namespace App\Http\Controllers\Penilai;

use App\Http\Controllers\Controller;
use App\Models\JadwalVisitasi;
use App\Models\PenilaianVisitasi;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(): View
    {
        /** @var User $user */
        $user = Auth::user();

        $jadwalSaya = JadwalVisitasi::query()
            ->with(['desa', 'periode'])
            ->where('petugas_id', $user->id)
            ->orderBy('tanggal_visitasi', 'desc')
            ->limit(5)
            ->get();

        $stats = [
            'total_jadwal' => JadwalVisitasi::query()->where('petugas_id', $user->id)->count(),
            'jadwal_terjadwal' => JadwalVisitasi::query()->where('petugas_id', $user->id)->where('status', 'terjadwal')->count(),
            'jadwal_selesai' => JadwalVisitasi::query()->where('petugas_id', $user->id)->where('status', 'selesai')->count(),
            'total_penilaian' => PenilaianVisitasi::query()->where('dinilai_oleh', $user->id)->count(),
        ];

        return view('penilai.dashboard', compact('jadwalSaya', 'stats'));
    }
}
