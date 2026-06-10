<?php

namespace App\Http\Controllers\Desa;

use App\Enums\AksiAudit;
use App\Enums\StatusJawaban;
use App\Enums\StatusPeriode;
use App\Http\Controllers\Controller;
use App\Http\Requests\Desa\SimpanJawabanKuesionerRequest;
use App\Models\Desa;
use App\Models\JawabanKuesioner;
use App\Models\Kuesioner;
use App\Models\PeriodePenilaian;
use App\Models\User;
use App\Services\AuditTrailService;
use App\Services\JawabanKuesionerService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KuesionerController extends Controller
{
    public function edit(Request $request): View|RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        // Super Admin bisa pilih desa via query parameter, Staff Admin Desa otomatis ke desanya
        if ($user->isSuperAdmin()) {
            $desa = Desa::query()->findOrFail($request->integer('desa_id'));
        } else {
            $desa = $user->desa;
            abort_unless($desa, 403, 'Akun Anda belum terhubung ke desa manapun.');
        }

        $periode = PeriodePenilaian::query()
            ->where('status', StatusPeriode::Aktif->value)
            ->latest('tanggal_mulai')
            ->first();

        if (! $periode) {
            return redirect()
                ->route($user->isSuperAdmin() ? 'admin.dashboard' : 'desa.dashboard')
                ->with('error', 'Belum ada periode penilaian yang aktif. Hubungi Super Admin.');
        }

        $indikator = Kuesioner::query()
            ->where('periode_id', $periode->id)
            ->where('is_active', true)
            ->orderBy('kategori')
            ->orderBy('urutan')
            ->get();

        $jawabanTersimpan = JawabanKuesioner::query()
            ->where('desa_id', $desa->id)
            ->where('periode_id', $periode->id)
            ->get()
            ->keyBy('kuesioner_id');

        $byKategori = $indikator->groupBy('kategori');

        $isFinal = $jawabanTersimpan->isNotEmpty()
            && $jawabanTersimpan->every(fn ($j) => $j->status === StatusJawaban::Final);

        $totalIndikator = $indikator->count();
        $sudahDijawab = $jawabanTersimpan->whereNotNull('jawaban')->count()
            + $jawabanTersimpan->whereNotNull('skor')->where('skor', '>', 0)->count();
        // Hitung distinct yang sudah ada record (kasarnya)
        $sudahDijawab = $jawabanTersimpan->count();

        return view('desa.kuesioner.edit', [
            'desa' => $desa,
            'periode' => $periode,
            'byKategori' => $byKategori,
            'jawabanTersimpan' => $jawabanTersimpan,
            'isFinal' => $isFinal,
            'totalIndikator' => $totalIndikator,
            'sudahDijawab' => $sudahDijawab,
            'totalBobot' => (float) $indikator->sum('bobot_indikator'),
        ]);
    }

    public function update(
        SimpanJawabanKuesionerRequest $request,
        JawabanKuesionerService $service,
    ): RedirectResponse {
        /** @var User $user */
        $user = Auth::user();

        // Super Admin bisa pilih desa, Staff Admin Desa terikat ke desanya
        if ($user->isSuperAdmin()) {
            $desa = Desa::query()->findOrFail($request->integer('desa_id'));
        } else {
            $desa = $user->desa;
            abort_unless($desa, 403);
        }

        $periode = PeriodePenilaian::query()->findOrFail($request->integer('periode_id'));

        if ($periode->status !== StatusPeriode::Aktif) {
            return back()->with('error', 'Periode tidak aktif. Pengisian dibatalkan.');
        }

        // Cek apakah sudah final — staff admin desa tidak boleh edit setelah final
        $sudahFinal = JawabanKuesioner::query()
            ->where('desa_id', $desa->id)
            ->where('periode_id', $periode->id)
            ->where('status', StatusJawaban::Final->value)
            ->exists();

        if ($sudahFinal) {
            return back()->with('error', 'Jawaban sudah difinalisasi dan tidak dapat diubah. Hubungi Super Admin untuk membuka kembali.');
        }

        $finalisasi = $request->boolean('finalisasi');
        $jawabanInput = (array) $request->input('jawaban', []);

        // Hanya Super Admin yang boleh mengisi skor
        if (! $user->isSuperAdmin()) {
            $jawabanInput = array_map(fn ($item) => [
                'kuesioner_id' => $item['kuesioner_id'],
                'jawaban' => $item['jawaban'] ?? null,
                'keterangan' => $item['keterangan'] ?? null,
                // skor di-unset agar tidak mengubah skor yang sudah ada
            ], $jawabanInput);
        }

        $service->simpan($desa, $periode, $jawabanInput, $user, $finalisasi);

        AuditTrailService::record(
            $user,
            $finalisasi ? AksiAudit::Update : AksiAudit::Update,
            $finalisasi
                ? "Memfinalisasi jawaban kuesioner desa {$desa->nama} periode {$periode->nama}"
                : "Menyimpan draft jawaban kuesioner desa {$desa->nama} periode {$periode->nama}",
            $desa,
            ['periode_id' => $periode->id, 'jumlah_jawaban' => count($jawabanInput)]
        );

        return redirect()
            ->route('desa.kuesioner.edit', $user->isSuperAdmin() ? ['desa_id' => $desa->id] : [])
            ->with('success', $finalisasi
                ? 'Jawaban berhasil difinalisasi.'
                : 'Draft jawaban berhasil disimpan.');
    }
}
