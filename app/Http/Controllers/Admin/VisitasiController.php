<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AksiAudit;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SalinIndikatorVisitasiRequest;
use App\Http\Requests\Admin\StoreVisitasiRequest;
use App\Http\Requests\Admin\UpdateVisitasiRequest;
use App\Models\Desa;
use App\Models\IndikatorVisitasi;
use App\Models\PeriodePenilaian;
use App\Services\AuditTrailService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class VisitasiController extends Controller
{
    public function index(Request $request): View
    {
        $periode = $this->resolvePeriode($request);
        $desa = $this->resolveDesa($request);

        $visitasi = $periode
            ? IndikatorVisitasi::query()
                ->with('desa')
                ->where('periode_id', $periode->id)
                ->when($desa, fn ($q) => $q->where('desa_id', $desa->id))
                ->orderBy('desa_id')
                ->orderBy('urutan')
                ->get()
            : collect();

        // Total bobot hanya dihitung untuk desa terpilih; jika "Semua Desa", tidak dihitung gabungan.
        $totalBobot = ($periode && $desa)
            ? (float) IndikatorVisitasi::query()
                ->where('periode_id', $periode->id)
                ->where('desa_id', $desa->id)
                ->where('is_active', true)
                ->sum('bobot')
            : null;

        // Indikator umum (global) dipakai sebagai fallback bila desa belum punya indikator khusus.
        $globalAktifCount = $periode
            ? IndikatorVisitasi::query()
                ->where('periode_id', $periode->id)
                ->whereNull('desa_id')
                ->where('is_active', true)
                ->count()
            : 0;

        return view('admin.visitasi.index', [
            'periode' => $periode,
            'desa' => $desa,
            'periodeOptions' => PeriodePenilaian::query()->orderBy('tahun', 'desc')->get(['id', 'nama', 'tahun', 'status']),
            'desaOptions' => Desa::query()->where('is_active', true)->orderBy('nama')->get(['id', 'nama']),
            'visitasi' => $visitasi,
            'totalBobot' => $totalBobot,
            'globalAktifCount' => $globalAktifCount,
            'statusBobot' => $this->statusBobot($totalBobot),
        ]);
    }

    public function create(Request $request): View
    {
        $periode = $this->resolvePeriode($request);
        $desa = $this->resolveDesa($request);

        abort_unless($periode, 422, 'Pilih periode terlebih dahulu.');

        $totalBobot = $desa
            ? (float) IndikatorVisitasi::query()
                ->where('periode_id', $periode->id)
                ->where('desa_id', $desa->id)
                ->sum('bobot')
            : 0.0;

        $maxUrutan = $desa
            ? (int) IndikatorVisitasi::query()
                ->where('periode_id', $periode->id)
                ->where('desa_id', $desa->id)
                ->max('urutan')
            : 0;

        return view('admin.visitasi.create', [
            'periode' => $periode,
            'desa' => $desa,
            'desaOptions' => Desa::query()->where('is_active', true)->orderBy('nama')->get(['id', 'nama']),
            'totalBobot' => $totalBobot,
            'sisaBobot' => max(0, round(100 - $totalBobot, 2)),
            'urutanBerikutnya' => $maxUrutan + 1,
            'kodeSaran' => $desa ? IndikatorVisitasi::generateKode($periode->id, $desa) : '',
        ]);
    }

    public function store(StoreVisitasiRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['is_active'] = (bool) ($data['is_active'] ?? true);
        $data['kategori'] = Desa::find($data['desa_id'])?->nama ?? 'Visitasi';

        $visitasi = IndikatorVisitasi::create($data);

        AuditTrailService::record(
            Auth::user(),
            AksiAudit::Create,
            "Menambah indikator visitasi [{$visitasi->kode}] untuk desa #{$visitasi->desa_id} pada periode #{$visitasi->periode_id}",
            $visitasi
        );

        return redirect()
            ->route('admin.visitasi.index', ['periode' => $visitasi->periode_id, 'desa' => $visitasi->desa_id])
            ->with('success', "Indikator {$visitasi->kode} berhasil ditambahkan.");
    }

    public function edit(IndikatorVisitasi $visitasi): View
    {
        $totalBobot = (float) IndikatorVisitasi::query()
            ->where('periode_id', $visitasi->periode_id)
            ->where('desa_id', $visitasi->desa_id)
            ->sum('bobot');

        return view('admin.visitasi.edit', [
            'visitasi' => $visitasi,
            'periode' => $visitasi->periode,
            'desa' => $visitasi->desa,
            'desaOptions' => Desa::query()->where('is_active', true)->orderBy('nama')->get(['id', 'nama']),
            'totalBobot' => $totalBobot,
            'sisaBobot' => max(0, round(100 - ($totalBobot - (float) $visitasi->bobot), 2)),
        ]);
    }

    public function update(UpdateVisitasiRequest $request, IndikatorVisitasi $visitasi): RedirectResponse
    {
        $data = $request->validated();
        $data['is_active'] = (bool) ($data['is_active'] ?? false);
        $data['kategori'] = Desa::find($data['desa_id'])?->nama ?? $visitasi->kategori;

        $visitasi->update($data);

        AuditTrailService::record(
            Auth::user(),
            AksiAudit::Update,
            "Memperbarui indikator visitasi [{$visitasi->kode}]",
            $visitasi
        );

        return redirect()
            ->route('admin.visitasi.index', ['periode' => $visitasi->periode_id, 'desa' => $visitasi->desa_id])
            ->with('success', "Indikator {$visitasi->kode} berhasil diperbarui.");
    }

    public function destroy(IndikatorVisitasi $visitasi): RedirectResponse
    {
        $kode = $visitasi->kode;
        $periodeId = $visitasi->periode_id;
        $desaId = $visitasi->desa_id;

        $visitasi->delete();

        AuditTrailService::record(
            Auth::user(),
            AksiAudit::Delete,
            "Menghapus indikator visitasi [{$kode}]",
            $visitasi
        );

        return redirect()
            ->route('admin.visitasi.index', ['periode' => $periodeId, 'desa' => $desaId])
            ->with('success', "Indikator {$kode} berhasil dihapus.");
    }

    /**
     * Salin seluruh indikator aktif dari desa sumber ke desa tujuan dalam periode yang sama.
     */
    public function salin(SalinIndikatorVisitasiRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $periodeId = (int) $data['periode_id'];
        $desaSumber = Desa::findOrFail($data['desa_sumber_id']);
        $desaTujuan = Desa::findOrFail($data['desa_tujuan_id']);

        $sumber = IndikatorVisitasi::query()
            ->where('periode_id', $periodeId)
            ->where('desa_id', $desaSumber->id)
            ->orderBy('urutan')
            ->get();

        if ($sumber->isEmpty()) {
            return redirect()
                ->route('admin.visitasi.index', ['periode' => $periodeId, 'desa' => $desaTujuan->id])
                ->with('error', "Desa {$desaSumber->nama} belum memiliki indikator untuk disalin.");
        }

        $bobotTujuan = (float) IndikatorVisitasi::query()
            ->where('periode_id', $periodeId)
            ->where('desa_id', $desaTujuan->id)
            ->sum('bobot');

        $disalin = 0;
        $dilewati = 0;

        DB::transaction(function () use ($sumber, $periodeId, $desaTujuan, &$bobotTujuan, &$disalin, &$dilewati) {
            $urutan = (int) IndikatorVisitasi::query()
                ->where('periode_id', $periodeId)
                ->where('desa_id', $desaTujuan->id)
                ->max('urutan');

            foreach ($sumber as $item) {
                // Jangan melebihi 100 bobot pada desa tujuan.
                if (round($bobotTujuan + (float) $item->bobot, 2) > 100) {
                    $dilewati++;

                    continue;
                }

                IndikatorVisitasi::create([
                    'periode_id' => $periodeId,
                    'desa_id' => $desaTujuan->id,
                    'kategori' => $desaTujuan->nama,
                    'kode' => IndikatorVisitasi::generateKode($periodeId, $desaTujuan),
                    'indikator_visitasi' => $item->indikator_visitasi,
                    'deskripsi' => $item->deskripsi,
                    'bobot' => $item->bobot,
                    'urutan' => ++$urutan,
                    'is_active' => $item->is_active,
                ]);

                $bobotTujuan += (float) $item->bobot;
                $disalin++;
            }
        });

        AuditTrailService::record(
            Auth::user(),
            AksiAudit::Create,
            "Menyalin {$disalin} indikator visitasi dari desa {$desaSumber->nama} ke {$desaTujuan->nama} (periode #{$periodeId})",
        );

        $pesan = "{$disalin} indikator berhasil disalin ke {$desaTujuan->nama}.";
        if ($dilewati > 0) {
            $pesan .= " {$dilewati} indikator dilewati karena akan melebihi total bobot 100.";
        }

        return redirect()
            ->route('admin.visitasi.index', ['periode' => $periodeId, 'desa' => $desaTujuan->id])
            ->with($dilewati > 0 ? 'warning' : 'success', $pesan);
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

    private function resolveDesa(Request $request): ?Desa
    {
        if ($id = $request->integer('desa')) {
            return Desa::find($id);
        }

        return null;
    }

    /**
     * @return array{label: string, kelas: string}
     */
    private function statusBobot(?float $total): array
    {
        if ($total === null) {
            return ['label' => '—', 'kelas' => 'secondary'];
        }

        return match (true) {
            round($total, 2) > 100 => ['label' => 'Melebihi bobot', 'kelas' => 'danger'],
            round($total, 2) === 100.0 => ['label' => 'Lengkap', 'kelas' => 'success'],
            default => ['label' => 'Belum lengkap', 'kelas' => 'warning'],
        };
    }
}
