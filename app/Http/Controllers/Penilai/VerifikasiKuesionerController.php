<?php

namespace App\Http\Controllers\Penilai;

use App\Enums\AksiAudit;
use App\Enums\StatusVisitasi;
use App\Http\Controllers\Controller;
use App\Http\Requests\Penilai\SimpanVerifikasiKuesionerRequest;
use App\Models\JadwalVisitasi;
use App\Models\JawabanKuesioner;
use App\Models\Kuesioner;
use App\Models\User;
use App\Models\VerifikasiKuesioner;
use App\Services\AuditTrailService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class VerifikasiKuesionerController extends Controller
{
    public function index(Request $request): View
    {
        /** @var User $user */
        $user = Auth::user();

        $query = JadwalVisitasi::query()
            ->with(['desa', 'periode', 'petugas'])
            ->withCount('penilaian')
            ->orderBy('tanggal_visitasi', 'desc');

        if (! $user->isSuperAdmin()) {
            $query->where('petugas_id', $user->id);
        }

        if ($status = $request->string('status')->trim()->toString()) {
            $query->where('status', $status);
        }

        $jadwal = $query->paginate(15)->withQueryString();

        return view('penilai.verifikasi-kuesioner.index', [
            'jadwal' => $jadwal,
            'statuses' => StatusVisitasi::cases(),
            'filters' => ['status' => $request->input('status')],
            'sidebarTemplate' => 'penilai.partials.sidebar',
        ]);
    }

    public function edit(JadwalVisitasi $jadwalVisitasi): View|RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        if (! $user->isSuperAdmin() && $jadwalVisitasi->petugas_id !== $user->id) {
            abort(403, 'Anda hanya dapat memverifikasi jadwal yang ditugaskan kepada Anda.');
        }

        $jadwalVisitasi->load(['desa', 'periode', 'petugas']);

        $kuesionerList = Kuesioner::query()
            ->where('periode_id', $jadwalVisitasi->periode_id)
            ->where('is_active', true)
            ->orderBy('urutan')
            ->get();

        // Load jawaban desa untuk kuesioner
        $jawabanDesa = JawabanKuesioner::query()
            ->where('desa_id', $jadwalVisitasi->desa_id)
            ->where('periode_id', $jadwalVisitasi->periode_id)
            ->whereIn('kuesioner_id', $kuesionerList->pluck('id'))
            ->get()
            ->keyBy('kuesioner_id');

        // Load existing verifikasi
        $verifikasiExisting = VerifikasiKuesioner::query()
            ->where('jadwal_id', $jadwalVisitasi->id)
            ->get()
            ->keyBy('kuesioner_id');

        $totalBobot = $kuesionerList->sum('bobot_indikator');

        return view('penilai.verifikasi-kuesioner.edit', [
            'jadwal' => $jadwalVisitasi,
            'kuesionerList' => $kuesionerList,
            'jawabanDesa' => $jawabanDesa,
            'verifikasiExisting' => $verifikasiExisting,
            'totalBobot' => $totalBobot,
            'sidebarTemplate' => 'penilai.partials.sidebar',
        ]);
    }

    public function update(
        SimpanVerifikasiKuesionerRequest $request,
        JadwalVisitasi $jadwalVisitasi,
    ): RedirectResponse {
        /** @var User $user */
        $user = Auth::user();

        if (! $user->isSuperAdmin() && $jadwalVisitasi->petugas_id !== $user->id) {
            abort(403);
        }

        $items = (array) $request->input('verifikasi', []);

        DB::transaction(function () use ($items, $jadwalVisitasi, $user) {
            foreach ($items as $item) {
                VerifikasiKuesioner::updateOrCreate(
                    [
                        'jadwal_id' => $jadwalVisitasi->id,
                        'kuesioner_id' => $item['kuesioner_id'],
                    ],
                    [
                        'desa_id' => $jadwalVisitasi->desa_id,
                        'periode_id' => $jadwalVisitasi->periode_id,
                        'status_verifikasi' => $item['status_verifikasi'],
                        'catatan' => $item['catatan'] ?? null,
                        'diverifikasi_oleh' => $user->id,
                        'tanggal_verifikasi' => now(),
                    ]
                );
            }
        });

        AuditTrailService::record(
            $user,
            AksiAudit::Update,
            "Menyimpan verifikasi kuesioner {$jadwalVisitasi->desa->nama} ({$jadwalVisitasi->periode->nama})",
            $jadwalVisitasi,
            ['jumlah_pertanyaan' => count($items)]
        );

        return redirect()
            ->route('penilai.verifikasi-kuesioner.edit', $jadwalVisitasi)
            ->with('success', 'Verifikasi kuesioner berhasil disimpan.');
    }
}
