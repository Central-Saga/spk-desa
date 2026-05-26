<?php

namespace App\Http\Controllers\Penilai;

use App\Enums\AksiAudit;
use App\Enums\StatusVisitasi;
use App\Http\Controllers\Controller;
use App\Http\Requests\Penilai\SimpanPenilaianVisitasiRequest;
use App\Models\IndikatorVisitasi;
use App\Models\JadwalVisitasi;
use App\Models\PenilaianVisitasi;
use App\Models\User;
use App\Services\AuditTrailService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PenilaianVisitasiController extends Controller
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

        return view('penilai.penilaian-visitasi.index', [
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
            abort(403, 'Anda hanya dapat menilai jadwal yang ditugaskan kepada Anda.');
        }

        $jadwalVisitasi->load(['desa', 'periode', 'petugas', 'penilaian']);

        $template = IndikatorVisitasi::query()
            ->where('periode_id', $jadwalVisitasi->periode_id)
            ->where('is_active', true)
            ->orderBy('urutan')
            ->get();

        $existing = $jadwalVisitasi->penilaian->keyBy('indikator_visitasi');

        return view('penilai.penilaian-visitasi.edit', [
            'jadwal' => $jadwalVisitasi,
            'template' => $template,
            'existing' => $existing,
            'sidebarTemplate' => 'penilai.partials.sidebar',
        ]);
    }

    public function update(
        SimpanPenilaianVisitasiRequest $request,
        JadwalVisitasi $jadwalVisitasi,
    ): RedirectResponse {
        /** @var User $user */
        $user = Auth::user();

        if (! $user->isSuperAdmin() && $jadwalVisitasi->petugas_id !== $user->id) {
            abort(403);
        }

        $items = (array) $request->input('penilaian', []);

        DB::transaction(function () use ($items, $jadwalVisitasi, $request, $user) {
            foreach ($items as $index => $item) {
                $penilaian = PenilaianVisitasi::query()
                    ->where('jadwal_id', $jadwalVisitasi->id)
                    ->where('indikator_visitasi', $item['indikator'])
                    ->first();

                $buktiGambar = $penilaian?->bukti_gambar;
                $gambar = $request->file("penilaian.{$index}.bukti_gambar");

                if ($gambar instanceof UploadedFile) {
                    if (is_string($buktiGambar) && $buktiGambar !== '') {
                        Storage::disk('public')->delete($buktiGambar);
                    }

                    $buktiGambar = $gambar->store('bukti-visitasi/'.$jadwalVisitasi->id, 'public');
                }

                PenilaianVisitasi::updateOrCreate(
                    [
                        'jadwal_id' => $jadwalVisitasi->id,
                        'indikator_visitasi' => $item['indikator'],
                    ],
                    [
                        'desa_id' => $jadwalVisitasi->desa_id,
                        'periode_id' => $jadwalVisitasi->periode_id,
                        'skor' => (float) $item['skor'],
                        'bobot' => (float) $item['bobot'],
                        'keterangan' => $item['keterangan'] ?? null,
                        'bukti_gambar' => $buktiGambar,
                        'dinilai_oleh' => $user->id,
                        'tanggal_input' => now(),
                    ]
                );
            }

            // Otomatis tandai jadwal selesai jika belum
            if ($jadwalVisitasi->status !== StatusVisitasi::Selesai) {
                $jadwalVisitasi->update(['status' => StatusVisitasi::Selesai]);
            }
        });

        AuditTrailService::record(
            $user,
            AksiAudit::Update,
            "Menyimpan penilaian visitasi {$jadwalVisitasi->desa->nama} ({$jadwalVisitasi->periode->nama})",
            $jadwalVisitasi,
            ['jumlah_indikator' => count($items)]
        );

        return redirect()
            ->route('penilai.penilaian-visitasi.edit', $jadwalVisitasi)
            ->with('success', 'Penilaian visitasi berhasil disimpan.');
    }
}
