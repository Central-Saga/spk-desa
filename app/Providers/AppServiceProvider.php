<?php

namespace App\Providers;

use App\Models\Desa;
use App\Models\Kuesioner;
use App\Models\NilaiAkhir;
use App\Observers\AuditTrailObserver;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Paginator::useBootstrapFive();

        App::setLocale('id');
        Carbon::setLocale('id');

        // Register audit trail observers
        Desa::observe(AuditTrailObserver::class);
        Kuesioner::observe(AuditTrailObserver::class);
        NilaiAkhir::observe(AuditTrailObserver::class);

        // Auto-inject sidebar template based on authenticated user role
        View::composer('*', function ($view) {
            if (! $view->offsetExists('sidebarTemplate')) {
                $user = Auth::user();
                if ($user) {
                    $template = match (true) {
                        $user->isSuperAdmin() => 'admin.partials.sidebar',
                        $user->isStaffAdminDesa() => 'desa.partials.sidebar',
                        $user->isStaffPenilaian() => 'penilai.partials.sidebar',
                        $user->isPimpinan() => 'pimpinan.partials.sidebar',
                        default => 'admin.partials.sidebar',
                    };
                    $view->with('sidebarTemplate', $template);
                }
            }
        });
    }
}
