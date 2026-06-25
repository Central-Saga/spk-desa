<?php

namespace App\Http\Controllers\Penilai;

use App\Enums\AksiAudit;
use App\Enums\StatusVisitasi;
use App\Http\Controllers\Controller;
use App\Http\Requests\Penilai\SimpanPenilaianVisitasiRequest;
use App\Models\BuktiVisitasiGambar;
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

        // Load indikator: jika desa punya indikator khusus, pakai itu saja (replace global)
        // Jika tidak, pakai indikator global (desa_id = null)
        $hasSpecific = IndikatorVisitasi::query()
            ->where('periode_id', $jadwalVisitasi->periode_id)
            ->where('desa_id', $jadwalVisitasi->desa_id)
            ->where('is_active', true)
            ->exists();

        $template = IndikatorVisitasi::query()
            ->where('periode_id', $jadwalVisitasi->periode_id)
            ->where(function ($q) use ($jadwalVisitasi, $hasSpecific) {
                if ($hasSpecific) {
                    // Hanya indikator khusus desa ini
                    $q->where('desa_id', $jadwalVisitasi->desa_id);
                } else {
                    // Indikator global (berlaku untuk semua desa)
                    $q->whereNull('desa_id');
                }
            })
            ->where('is_active', true)
            ->orderBy('urutan')
            ->get();

        $existing = $jadwalVisitasi->penilaian->keyBy('indikator_visitasi');

        return view('penilai.penilaian-visitasi.edit', [
            'jadwal' => $jadwalVisitasi,
            'template' => $template,
            'existing' => $existing,
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
                $penilaian = PenilaianVisitasi::updateOrCreate(
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
                        'dinilai_oleh' => $user->id,
                        'tanggal_input' => now(),
                    ]
                );

                // Hapus gambar yang ditandai (cuma yang milik penilaian ini)
                $hapusIds = array_filter(array_map('intval', $item['hapus_gambar'] ?? []));
                if (! empty($hapusIds)) {
                    $gambarHapus = BuktiVisitasiGambar::query()
                        ->whereIn('id', $hapusIds)
                        ->where('penilaian_visitasi_id', $penilaian->id)
                        ->get();

                    foreach ($gambarHapus as $g) {
                        Storage::disk('public')->delete($g->path);
                        $g->delete();
                    }
                }

                // Simpan gambar baru (array UploadedFile)
                $files = $request->file("penilaian.{$index}.bukti_gambar");
                $files = is_array($files) ? $files : array_filter([$files], fn ($f) => $f instanceof UploadedFile);

                if (! empty($files)) {
                    $urutanNext = ($penilaian->buktiGambar()->max('urutan') ?? 0) + 1;
                    foreach ($files as $file) {
                        $path = $file->store('bukti-visitasi/'.$jadwalVisitasi->id, 'public');
                        $penilaian->buktiGambar()->create([
                            'path' => $path,
                            'urutan' => $urutanNext++,
                        ]);
                    }
                }
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
