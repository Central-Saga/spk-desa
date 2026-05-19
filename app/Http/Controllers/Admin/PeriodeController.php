<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AksiAudit;
use App\Enums\StatusPeriode;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePeriodeRequest;
use App\Http\Requests\Admin\UpdatePeriodeRequest;
use App\Models\PeriodePenilaian;
use App\Services\AuditTrailService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PeriodeController extends Controller
{
    public function index(Request $request): View
    {
        $query = PeriodePenilaian::query()
            ->withCount(['kuesioner', 'jawabanKuesioner', 'jadwalVisitasi', 'nilaiAkhir'])
            ->orderBy('tahun', 'desc')
            ->orderBy('tanggal_mulai', 'desc');

        if ($keyword = $request->string('q')->trim()->toString()) {
            $query->where('nama', 'like', "%{$keyword}%");
        }

        if ($status = $request->string('status')->trim()->toString()) {
            $query->where('status', $status);
        }

        $periode = $query->paginate(15)->withQueryString();

        return view('admin.periode.index', [
            'periode' => $periode,
            'statuses' => StatusPeriode::cases(),
            'filters' => [
                'q' => $request->input('q'),
                'status' => $request->input('status'),
            ],
        ]);
    }

    public function create(): View
    {
        return view('admin.periode.create', [
            'statuses' => StatusPeriode::cases(),
        ]);
    }

    public function store(StorePeriodeRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $periode = DB::transaction(function () use ($data) {
            // Hanya boleh 1 periode aktif pada satu waktu — turunkan periode aktif lain ke selesai
            if (($data['status'] ?? null) === StatusPeriode::Aktif->value) {
                PeriodePenilaian::query()
                    ->where('status', StatusPeriode::Aktif->value)
                    ->update(['status' => StatusPeriode::Selesai->value]);
            }

            return PeriodePenilaian::create($data);
        });

        AuditTrailService::record(
            Auth::user(),
            AksiAudit::Create,
            "Membuat periode {$periode->nama}",
            $periode
        );

        return redirect()
            ->route('admin.periode.index')
            ->with('success', "Periode {$periode->nama} berhasil dibuat.");
    }

    public function edit(PeriodePenilaian $periode): View
    {
        return view('admin.periode.edit', [
            'periode' => $periode,
            'statuses' => StatusPeriode::cases(),
        ]);
    }

    public function update(UpdatePeriodeRequest $request, PeriodePenilaian $periode): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($data, $periode) {
            if (($data['status'] ?? null) === StatusPeriode::Aktif->value) {
                PeriodePenilaian::query()
                    ->where('status', StatusPeriode::Aktif->value)
                    ->whereKeyNot($periode->id)
                    ->update(['status' => StatusPeriode::Selesai->value]);
            }

            $periode->update($data);
        });

        AuditTrailService::record(
            Auth::user(),
            AksiAudit::Update,
            "Memperbarui periode {$periode->nama}",
            $periode
        );

        return redirect()
            ->route('admin.periode.index')
            ->with('success', "Periode {$periode->nama} berhasil diperbarui.");
    }

    public function destroy(PeriodePenilaian $periode): RedirectResponse
    {
        $nama = $periode->nama;

        if ($periode->kuesioner()->exists() || $periode->jawabanKuesioner()->exists() || $periode->nilaiAkhir()->exists()) {
            return back()->with('error', "Periode {$nama} sudah memiliki data terkait dan tidak dapat dihapus.");
        }

        $periode->delete();

        AuditTrailService::record(
            Auth::user(),
            AksiAudit::Delete,
            "Menghapus periode {$nama}",
            $periode
        );

        return redirect()
            ->route('admin.periode.index')
            ->with('success', "Periode {$nama} berhasil dihapus.");
    }
}
