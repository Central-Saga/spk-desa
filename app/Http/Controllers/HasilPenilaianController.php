<?php

namespace App\Http\Controllers;

use App\Models\JawabanKuesioner;
use App\Models\NilaiAkhir;
use App\Models\PenilaianVisitasi;
use App\Models\PeriodePenilaian;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HasilPenilaianController extends Controller
{
    public function index(Request $request): View
    {
        /** @var User $user */
        $user = Auth::user();

        $periode = $this->resolvePeriode($request);

        $query = NilaiAkhir::query()
            ->with(['desa', 'periode'])
            ->when($periode, fn ($q) => $q->where('periode_id', $periode->id))
            ->orderBy('peringkat');

        // Scope berdasarkan role
        if ($user->isStaffAdminDesa() && $user->desa_id) {
            $query->where('desa_id', $user->desa_id);
        }

        $hasil = $query->get();

        return view('hasil.index', [
            'periode' => $periode,
            'periodeOptions' => PeriodePenilaian::query()
                ->orderBy('tahun', 'desc')->get(['id', 'nama', 'tahun', 'status']),
            'hasil' => $hasil,
            'sidebarTemplate' => $this->sidebarFor($user),
            'titleSuffix' => $user->isStaffAdminDesa() ? ' Desa' : '',
        ]);
    }

    public function show(Request $request, NilaiAkhir $nilai): View
    {
        /** @var User $user */
        $user = Auth::user();

        if ($user->isStaffAdminDesa() && $nilai->desa_id !== $user->desa_id) {
            abort(403, 'Anda hanya dapat melihat hasil desa Anda sendiri.');
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

        return view('hasil.show', [
            'nilai' => $nilai,
            'jawaban' => $jawaban,
            'visitasi' => $visitasi,
            'sidebarTemplate' => $this->sidebarFor($user),
        ]);
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
