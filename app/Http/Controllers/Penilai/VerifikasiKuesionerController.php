<?php

namespace App\Http\Controllers\Penilai;

use App\Enums\AksiAudit;
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
    /** @var array<int, array{value: string, label: string}> */
    private const STATUS_OPTIONS = [
        ['value' => 'disetujui', 'label' => 'Disetujui'],
        ['value' => 'ditolak', 'label' => 'Ditolak'],
        ['value' => 'perlu_perbaikan', 'label' => 'Perlu Perbaikan'],
    ];

    public function index(Request $request): View
    {
        /** @var User $user */
        $user = Auth::user();

        $query = JawabanKuesioner::query()
            ->select(
                'jawaban_kuesioner.id',
                'jawaban_kuesioner.desa_id',
                'jawaban_kuesioner.kuesioner_id',
                'jawaban_kuesioner.periode_id',
                'jawaban_kuesioner.jawaban',
                'jawaban_kuesioner.status_jawaban',
                'jawaban_kuesioner.skor',
                'jawaban_kuesioner.status',
            )
            ->with(['desa', 'kuesioner', 'periode'])
            ->whereHas('periode', fn ($q) => $q->where('status', 'aktif'))
            ->where('jawaban_kuesioner.status', 'final')
            ->leftJoin('jadwal_visitasi as jv', function ($join) {
                $join->on('jawaban_kuesioner.desa_id', '=', 'jv.desa_id')
                    ->on('jawaban_kuesioner.periode_id', '=', 'jv.periode_id');
            })
            ->leftJoin('verifikasi_kuesioner as vk', function ($join) {
                $join->on('jawaban_kuesioner.kuesioner_id', '=', 'vk.kuesioner_id')
                    ->on('jawaban_kuesioner.desa_id', '=', 'vk.desa_id')
                    ->on('jawaban_kuesioner.periode_id', '=', 'vk.periode_id');
            })
            ->addSelect(
                'jv.tanggal_visitasi',
                'jv.petugas_id',
                'vk.status_verifikasi as verifikasi_status',
                'vk.catatan as verifikasi_catatan',
                'vk.id as verifikasi_id',
            )
            ->with(['desa.jadwalVisitasi' => fn ($q) => $q->orderByDesc('tanggal_visitasi')]);

        if (! $user->isSuperAdmin()) {
            $query->where('jv.petugas_id', $user->id);
        }

        if ($status = $request->string('status')->trim()->toString()) {
            if ($status === 'belum') {
                $query->whereNull('vk.status_verifikasi');
            } else {
                $query->where('vk.status_verifikasi', $status);
            }
        }

        $items = $query
            ->orderBy('jv.tanggal_visitasi', 'desc')
            ->orderBy('jawaban_kuesioner.kuesioner_id')
            ->paginate(15)
            ->withQueryString();

        return view('penilai.verifikasi-kuesioner.index', [
            'items' => $items,
            'statusOptions' => self::STATUS_OPTIONS,
            'filters' => ['status' => $request->input('status')],
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

        $jawabanDesa = JawabanKuesioner::query()
            ->where('desa_id', $jadwalVisitasi->desa_id)
            ->where('periode_id', $jadwalVisitasi->periode_id)
            ->whereIn('kuesioner_id', $kuesionerList->pluck('id'))
            ->get()
            ->keyBy('kuesioner_id');

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
            'statusOptions' => self::STATUS_OPTIONS,
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
