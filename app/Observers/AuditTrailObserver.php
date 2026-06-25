<?php

namespace App\Observers;

use App\Enums\AksiAudit;
use App\Models\AuditTrail;
use App\Models\Desa;
use App\Models\Kuesioner;
use App\Models\NilaiAkhir;
use App\Services\AuditTrailService;
use Illuminate\Support\Facades\Auth;

class AuditTrailObserver
{
    /**
     * @param  Desa|Kuesioner|NilaiAkhir  $model
     */
    public function created($model): void
    {
        AuditTrailService::record(
            Auth::user(),
            AksiAudit::Create,
            "Membuat {$this->label($model)} #{$model->getKey()}",
            $model,
            ['data' => $model->toArray()]
        );
    }

    /**
     * @param  Desa|Kuesioner|NilaiAkhir  $model
     */
    public function updated($model): void
    {
        $dirty = $model->getChanges();

        // Hanya catat jika ada perubahan
        if (empty($dirty)) {
            return;
        }

        AuditTrailService::record(
            Auth::user(),
            AksiAudit::Update,
            "Memperbarui {$this->label($model)} #{$model->getKey()}",
            $model,
            [
                'before' => array_intersect_key($model->getOriginal(), $dirty),
                'after' => $dirty,
            ]
        );
    }

    /**
     * @param  Desa|Kuesioner|NilaiAkhir  $model
     */
    public function deleted($model): void
    {
        AuditTrailService::record(
            Auth::user(),
            AksiAudit::Delete,
            "Menghapus {$this->label($model)} #{$model->getKey()}",
            $model,
            ['data' => $model->toArray()]
        );
    }

    /**
     * @param  Desa|Kuesioner|NilaiAkhir  $model
     */
    private function label($model): string
    {
        return match ($model::class) {
            Desa::class => 'desa',
            Kuesioner::class => 'kuesioner',
            NilaiAkhir::class => 'nilai akhir',
            AuditTrail::class => 'audit trail',
            default => class_basename($model),
        };
    }
}
