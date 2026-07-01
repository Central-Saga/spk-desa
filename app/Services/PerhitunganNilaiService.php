<?php

namespace App\Services;

use App\Models\Desa;
use App\Models\IndikatorVisitasi;
use App\Models\JawabanKuesioner;
use App\Models\NilaiAkhir;
use App\Models\PenilaianVisitasi;
use App\Models\PeriodePenilaian;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

final class PerhitunganNilaiService
{
    public const BOBOT_KUESIONER = 0.60;

    public const BOBOT_VISITASI = 0.40;

    /**
     * Hitung nilai akhir + tetapkan ranking untuk semua desa aktif pada periode.
     *
     * @return Collection<int, NilaiAkhir>
     */
    public function hitungSemuaDesa(PeriodePenilaian $periode, User $admin): Collection
    {
        return DB::transaction(function () use ($periode, $admin) {
            $desaAktif = Desa::query()->where('is_active', true)->get();

            $hasil = $desaAktif->map(fn (Desa $desa) => $this->hitungSatuDesa($desa, $periode, $admin));

            // Reload + sort untuk assign ranking
            $records = NilaiAkhir::query()
                ->where('periode_id', $periode->id)
                ->orderByDesc('nilai_akhir')
                ->orderBy('desa_id')
                ->get();

            $peringkat = 1;
            $previousNilai = null;
            $sameRankCount = 0;

            foreach ($records as $idx => $row) {
                if ($previousNilai !== null && (float) $row->nilai_akhir === (float) $previousNilai) {
                    // tied — pakai peringkat sebelumnya
                    $sameRankCount++;
                } else {
                    $peringkat = $idx + 1;
                    $sameRankCount = 0;
                }

                $row->update(['peringkat' => $peringkat]);
                $previousNilai = $row->nilai_akhir;
            }

            return NilaiAkhir::query()
                ->where('periode_id', $periode->id)
                ->orderBy('peringkat')
                ->get();
        });
    }

    /**
     * Hitung nilai akhir 1 desa pada periode tertentu.
     */
    public function hitungSatuDesa(Desa $desa, PeriodePenilaian $periode, User $admin): NilaiAkhir
    {
        $nilaiKuesioner = $this->hitungNilaiKuesioner($desa, $periode);
        $nilaiVisitasi = $this->hitungNilaiVisitasi($desa, $periode);
        $nilaiAkhir = ($nilaiKuesioner * self::BOBOT_KUESIONER)
            + ($nilaiVisitasi * self::BOBOT_VISITASI);

        return NilaiAkhir::updateOrCreate(
            ['desa_id' => $desa->id, 'periode_id' => $periode->id],
            [
                'nilai_kuesioner' => round($nilaiKuesioner, 2),
                'nilai_visitasi' => round($nilaiVisitasi, 2),
                'nilai_akhir' => round($nilaiAkhir, 2),
                'dihitung_pada' => now(),
                'dihitung_oleh' => $admin->id,
            ]
        );
    }

    /**
     * Nilai kuesioner = sum(skor * bobot_indikator / 100).
     * Hasil ada di rentang 0-100 jika total bobot indikator = 100.
     */
    private function hitungNilaiKuesioner(Desa $desa, PeriodePenilaian $periode): float
    {
        return (float) JawabanKuesioner::query()
            ->where('jawaban_kuesioner.desa_id', $desa->id)
            ->where('jawaban_kuesioner.periode_id', $periode->id)
            ->join('kuesioner', 'jawaban_kuesioner.kuesioner_id', '=', 'kuesioner.id')
            ->where('kuesioner.is_active', true)
            ->whereNull('kuesioner.deleted_at')
            ->sum(DB::raw('jawaban_kuesioner.skor * kuesioner.bobot_indikator / 100'));
    }

    /**
     * Nilai visitasi = sum(skor * bobot / 100).
     */
    private function hitungNilaiVisitasi(Desa $desa, PeriodePenilaian $periode): float
    {
        $templateIndikator = IndikatorVisitasi::activeTemplate($periode->id, $desa->id)
            ->pluck('indikator_visitasi');

        if ($templateIndikator->isEmpty()) {
            return 0.0;
        }

        return (float) PenilaianVisitasi::query()
            ->where('desa_id', $desa->id)
            ->where('periode_id', $periode->id)
            ->whereIn('indikator_visitasi', $templateIndikator)
            ->sum(DB::raw('skor * bobot / 100'));
    }

    /**
     * Cek kelengkapan data penilaian untuk satu desa pada periode tertentu.
     *
     * @return array{kuesioner_lengkap: bool, kuesioner_terjawab: int, total_kuesioner: int, visitasi_lengkap: bool, visitasi_dinilai: int, total_visitasi: int}
     */
    public function cekKelengkapan(Desa $desa, PeriodePenilaian $periode): array
    {
        $totalKuesioner = $periode->kuesioner()->where('is_active', true)->count();
        $kuesionerTerjawab = JawabanKuesioner::query()
            ->where('jawaban_kuesioner.desa_id', $desa->id)
            ->where('jawaban_kuesioner.periode_id', $periode->id)
            ->join('kuesioner', 'jawaban_kuesioner.kuesioner_id', '=', 'kuesioner.id')
            ->where('kuesioner.is_active', true)
            ->whereNull('kuesioner.deleted_at')
            ->count();

        $templateIndikator = IndikatorVisitasi::activeTemplate($periode->id, $desa->id)
            ->pluck('indikator_visitasi');

        $totalVisitasi = $templateIndikator->count();
        $visitasiDinilai = PenilaianVisitasi::query()
            ->where('desa_id', $desa->id)
            ->where('periode_id', $periode->id)
            ->whereIn('indikator_visitasi', $templateIndikator)
            ->count();

        return [
            'kuesioner_lengkap' => $totalKuesioner > 0 && $kuesionerTerjawab >= $totalKuesioner,
            'kuesioner_terjawab' => $kuesionerTerjawab,
            'total_kuesioner' => $totalKuesioner,
            'visitasi_lengkap' => $totalVisitasi > 0 && $visitasiDinilai >= $totalVisitasi,
            'visitasi_dinilai' => $visitasiDinilai,
            'total_visitasi' => $totalVisitasi,
        ];
    }
}
