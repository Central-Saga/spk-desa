<?php

namespace App\Http\Controllers\Penilai;

use App\Enums\AksiAudit;
use App\Enums\RoleSlug;
use App\Enums\StatusVisitasi;
use App\Http\Controllers\Controller;
use App\Http\Requests\Penilai\StoreJadwalVisitasiRequest;
use App\Http\Requests\Penilai\UpdateJadwalVisitasiRequest;
use App\Models\Desa;
use App\Models\JadwalVisitasi;
use App\Models\PeriodePenilaian;
use App\Models\User;
use App\Services\AuditTrailService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class JadwalVisitasiController extends Controller
{
    public function index(Request $request): View
    {
        $query = JadwalVisitasi::query()
            ->with(['desa', 'periode', 'petugas'])
            ->orderBy('tanggal_visitasi', 'desc')
            ->orderBy('waktu_mulai');

        if ($status = $request->string('status')->trim()->toString()) {
            $query->where('status', $status);
        }

        if ($periode = $request->integer('periode')) {
            $query->where('periode_id', $periode);
        }

        $jadwal = $query->paginate(15)->withQueryString();

        return view('penilai.jadwal-visitasi.index', [
            'jadwal' => $jadwal,
            'statuses' => StatusVisitasi::cases(),
            'periodeOptions' => PeriodePenilaian::query()->orderBy('tahun', 'desc')->get(['id', 'nama', 'tahun']),
            'filters' => [
                'status' => $request->input('status'),
                'periode' => $request->input('periode'),
            ],
        ]);
    }

    public function create(Request $request): View
    {
        $data = $this->formData();

        if ($desaId = $request->integer('desa_id')) {
            $data['defaultDesaId'] = $desaId;
        }

        return view('penilai.jadwal-visitasi.create', $data);
    }

    public function store(StoreJadwalVisitasiRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['dibuat_oleh'] = Auth::id();

        $jadwal = JadwalVisitasi::create($data);

        AuditTrailService::record(
            Auth::user(),
            AksiAudit::Create,
            "Membuat jadwal visitasi {$jadwal->desa->nama} pada {$jadwal->tanggal_visitasi->format('d M Y')}",
            $jadwal
        );

        return redirect()
            ->route('penilai.jadwal-visitasi.index')
            ->with('success', 'Jadwal visitasi berhasil dibuat.');
    }

    public function edit(JadwalVisitasi $jadwalVisitasi): View
    {
        return view('penilai.jadwal-visitasi.edit', [
            ...$this->formData(),
            'jadwal' => $jadwalVisitasi,
        ]);
    }

    public function update(
        UpdateJadwalVisitasiRequest $request,
        JadwalVisitasi $jadwalVisitasi,
    ): RedirectResponse {
        $jadwalVisitasi->update($request->validated());

        AuditTrailService::record(
            Auth::user(),
            AksiAudit::Update,
            "Memperbarui jadwal visitasi {$jadwalVisitasi->desa->nama}",
            $jadwalVisitasi
        );

        return redirect()
            ->route('penilai.jadwal-visitasi.index')
            ->with('success', 'Jadwal visitasi berhasil diperbarui.');
    }

    public function destroy(JadwalVisitasi $jadwalVisitasi): RedirectResponse
    {
        if ($jadwalVisitasi->penilaian()->exists()) {
            return back()->with('error', 'Jadwal sudah memiliki penilaian visitasi dan tidak dapat dihapus.');
        }

        $namaDesa = $jadwalVisitasi->desa->nama;
        $jadwalVisitasi->delete();

        AuditTrailService::record(
            Auth::user(),
            AksiAudit::Delete,
            "Menghapus jadwal visitasi {$namaDesa}",
            $jadwalVisitasi
        );

        return redirect()
            ->route('penilai.jadwal-visitasi.index')
            ->with('success', 'Jadwal visitasi berhasil dihapus.');
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(): array
    {
        return [
            'desaList' => Desa::query()->where('is_active', true)->orderBy('nama')->get(['id', 'nama']),
            'periodeList' => PeriodePenilaian::query()->orderBy('tahun', 'desc')->get(['id', 'nama', 'tahun', 'status']),
            'petugasList' => User::query()
                ->whereHas('roles', fn ($q) => $q->where('name', RoleSlug::StaffPenilaian->value))
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name']),
            'statuses' => StatusVisitasi::cases(),
        ];
    }
}
