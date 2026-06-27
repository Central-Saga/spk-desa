<?php

namespace App\Http\Controllers\Penilai;

use App\Enums\AksiAudit;
use App\Http\Controllers\Controller;
use App\Http\Requests\Penilai\SimpanVerifikasiKuesionerRequest;
use App\Models\Desa;
use App\Models\JawabanKuesioner;
use App\Models\Kuesioner;
use App\Models\PeriodePenilaian;
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
                'jawaban_kuesioner.skor',
                'jawaban_kuesioner.status',
            )
            ->with(['desa', 'kuesioner', 'periode'])
            ->whereHas('periode', fn ($q) => $q->where('status', 'aktif'))
            ->where('jawaban_kuesioner.status', 'final')
            ->leftJoin('verifikasi_kuesioner as vk', function ($join) {
                $join->on('jawaban_kuesioner.kuesioner_id', '=', 'vk.kuesioner_id')
                    ->on('jawaban_kuesioner.desa_id', '=', 'vk.desa_id')
                    ->on('jawaban_kuesioner.periode_id', '=', 'vk.periode_id');
            })
            ->leftJoin('kuesioner', 'jawaban_kuesioner.kuesioner_id', '=', 'kuesioner.id')
            ->whereNull('kuesioner.deleted_at')
            ->addSelect(
                'vk.status_verifikasi as verifikasi_status',
                'vk.catatan as verifikasi_catatan',
                'vk.id as verifikasi_id',
                'vk.tanggal_verifikasi',
            );

        // Verifikasi kuesioner tidak terikat petugas jadwal; seluruh staff penilaian
        // dan super admin bisa melihat/mengverifikasi jawaban final desa.
        if (! $user->isSuperAdmin() && ! $user->isStaffPenilaian()) {
            abort(403, 'Anda tidak memiliki hak akses untuk halaman ini.');
        }

        if ($status = $request->string('status')->trim()->toString()) {
            if ($status === 'belum') {
                $query->whereNull('vk.status_verifikasi');
            } else {
                $query->where('vk.status_verifikasi', $status);
            }
        }

        $items = $query
            ->orderBy('kuesioner.urutan')
            ->paginate(15)
            ->withQueryString();

        return view('penilai.verifikasi-kuesioner.index', [
            'items' => $items,
            'statusOptions' => self::STATUS_OPTIONS,
            'filters' => ['status' => $request->input('status')],
        ]);
    }

    public function edit(Desa $desa, PeriodePenilaian $periode): View|RedirectResponse
    {
        $kuesionerList = Kuesioner::query()
            ->where('periode_id', $periode->id)
            ->where('is_active', true)
            ->orderBy('urutan')
            ->get();

        if ($kuesionerList->isEmpty()) {
            return redirect()
                ->route('penilai.verifikasi-kuesioner.index')
                ->with('warning', 'Belum ada pertanyaan kuesioner untuk periode ini.');
        }

        $jawabanDesa = JawabanKuesioner::query()
            ->where('desa_id', $desa->id)
            ->where('periode_id', $periode->id)
            ->whereIn('kuesioner_id', $kuesionerList->pluck('id'))
            ->get()
            ->keyBy('kuesioner_id');

        $verifikasiExisting = VerifikasiKuesioner::query()
            ->where('desa_id', $desa->id)
            ->where('periode_id', $periode->id)
            ->get()
            ->keyBy('kuesioner_id');

        $totalBobot = $kuesionerList->sum('bobot_indikator');

        return view('penilai.verifikasi-kuesioner.edit', [
            'desa' => $desa,
            'periode' => $periode,
            'kuesionerList' => $kuesionerList,
            'jawabanDesa' => $jawabanDesa,
            'verifikasiExisting' => $verifikasiExisting,
            'totalBobot' => $totalBobot,
            'statusOptions' => self::STATUS_OPTIONS,
        ]);
    }

    public function update(
        SimpanVerifikasiKuesionerRequest $request,
        Desa $desa,
        PeriodePenilaian $periode,
    ): RedirectResponse {
        /** @var User $user */
        $user = Auth::user();

        $items = (array) $request->input('verifikasi', []);

        DB::transaction(function () use ($items, $desa, $periode, $user) {
            foreach ($items as $item) {
                VerifikasiKuesioner::updateOrCreate(
                    [
                        'desa_id' => $desa->id,
                        'periode_id' => $periode->id,
                        'kuesioner_id' => $item['kuesioner_id'],
                    ],
                    [
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
            "Menyimpan verifikasi kuesioner {$desa->nama} ({$periode->nama})",
            $desa,
            ['jumlah_pertanyaan' => count($items)]
        );

        return redirect()
            ->route('penilai.verifikasi-kuesioner.edit', [$desa, $periode])
            ->with('success', 'Verifikasi kuesioner berhasil disimpan.');
    }
}
