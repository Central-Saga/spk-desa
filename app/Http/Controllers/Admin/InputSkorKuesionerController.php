<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Desa;
use App\Models\JawabanKuesioner;
use App\Models\Kuesioner;
use App\Models\PeriodePenilaian;
use Illuminate\Contracts\View\View;

class InputSkorKuesionerController extends Controller
{
    public function index(): View
    {
        $periode = PeriodePenilaian::query()
            ->where('status', 'aktif')
            ->latest('tanggal_mulai')
            ->first() ?? PeriodePenilaian::query()->latest('id')->first();

        $desaList = Desa::query()->where('is_active', true)->orderBy('nama')->get();

        $data = $desaList->map(fn (Desa $d) => [
            'desa' => $d,
            'total' => Kuesioner::where('periode_id', $periode?->id)->where('is_active', true)->count(),
            'terisi' => JawabanKuesioner::where('desa_id', $d->id)
                ->where('periode_id', $periode?->id)
                ->where('skor', '>', 0)
                ->count(),
        ]);

        return view('admin.input-skor.index', ['periode' => $periode, 'data' => $data]);
    }
}
