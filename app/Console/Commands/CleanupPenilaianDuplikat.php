<?php

namespace App\Console\Commands;

use App\Models\Desa;
use App\Models\IndikatorVisitasi;
use App\Models\JadwalVisitasi;
use App\Models\PenilaianVisitasi;
use App\Models\PeriodePenilaian;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

class CleanupPenilaianDuplikat extends Command
{
    protected $signature = 'penilaian:cleanup-duplikat
        {--dry-run : Tampilkan yang akan dihapus tanpa eksekusi}
        {--periode= : Filter by periode ID}
        {--desa= : Filter by desa ID}';

    protected $description = 'Membersihkan penilaian visitasi duplikat/orphaned agar template aktif valid.';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $periodeId = $this->option('periode');
        $desaId = $this->option('desa');

        $periodeQuery = PeriodePenilaian::query();
        if ($periodeId !== null) {
            $periodeQuery->where('id', $periodeId);
        }
        $periodes = $periodeQuery->pluck('id');

        $desaQuery = Desa::query()->where('is_active', true);
        if ($desaId !== null) {
            $desaQuery->where('id', $desaId);
        }
        $desas = $desaQuery->pluck('id');

        if ($periodes->isEmpty()) {
            $this->warn('Tidak ada periode yang sesuai filter.');

            return self::SUCCESS;
        }

        if ($desas->isEmpty()) {
            $this->warn('Tidak ada desa aktif yang sesuai filter.');

            return self::SUCCESS;
        }

        $totalOrphaned = 0;
        $totalJadwalDuplikat = 0;
        $totalPenilaianDuplikat = 0;

        foreach ($periodes as $pid) {
            foreach ($desas as $did) {
                $templateIndikator = IndikatorVisitasi::activeTemplate($pid, $did)
                    ->pluck('indikator_visitasi');

                // 1. Hapus penilaian orphaned (indikator tidak ada di template aktif)
                $orphanedQuery = PenilaianVisitasi::query()
                    ->where('desa_id', $did)
                    ->where('periode_id', $pid);

                if ($templateIndikator->isNotEmpty()) {
                    $orphanedQuery->whereNotIn('indikator_visitasi', $templateIndikator);
                }

                $orphanedCount = $orphanedQuery->count();

                if ($orphanedCount > 0) {
                    $this->info(sprintf(
                        '%s: Hapus %d penilaian orphaned untuk desa %d periode %d',
                        $dryRun ? '[DRY-RUN]' : '[HAPUS]',
                        $orphanedCount,
                        $did,
                        $pid
                    ));

                    if (! $dryRun) {
                        $orphanedQuery->delete();
                    }

                    $totalOrphaned += $orphanedCount;
                }

                // 2. Handle jadwal duplikat: keep latest, hapus sisanya + penilaiannya
                $jadwalDuplikat = $this->cariJadwalDuplikat($did, $pid);

                if ($jadwalDuplikat->isNotEmpty()) {
                    $latestJadwalId = $jadwalDuplikat->first()->id;
                    $hapusJadwalIds = $jadwalDuplikat->slice(1)->pluck('id');

                    $penilaianCount = PenilaianVisitasi::query()
                        ->whereIn('jadwal_id', $hapusJadwalIds)
                        ->count();

                    $this->info(sprintf(
                        '%s: Keep jadwal %d, hapus jadwal %s (penilaian %d) untuk desa %d periode %d',
                        $dryRun ? '[DRY-RUN]' : '[HAPUS]',
                        $latestJadwalId,
                        $hapusJadwalIds->implode(','),
                        $penilaianCount,
                        $did,
                        $pid
                    ));

                    if (! $dryRun) {
                        PenilaianVisitasi::query()->whereIn('jadwal_id', $hapusJadwalIds)->delete();
                        JadwalVisitasi::query()->whereIn('id', $hapusJadwalIds)->delete();
                    }

                    $totalJadwalDuplikat += $hapusJadwalIds->count();
                    $totalPenilaianDuplikat += $penilaianCount;
                }
            }
        }

        $this->newLine();
        $this->info(sprintf(
            '%sSummary: %d penilaian orphaned, %d jadwal duplikat, %d penilaian duplikat.',
            $dryRun ? '[DRY-RUN] ' : '',
            $totalOrphaned,
            $totalJadwalDuplikat,
            $totalPenilaianDuplikat
        ));

        return self::SUCCESS;
    }

    /**
     * @return Collection<int, JadwalVisitasi>
     */
    private function cariJadwalDuplikat(int $desaId, int $periodeId): Collection
    {
        $ids = JadwalVisitasi::query()
            ->select('id')
            ->where('desa_id', $desaId)
            ->where('periode_id', $periodeId)
            ->orderByDesc('id')
            ->pluck('id');

        return JadwalVisitasi::query()
            ->whereIn('id', $ids)
            ->orderByDesc('id')
            ->get();
    }
}
