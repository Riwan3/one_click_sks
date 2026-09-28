<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::define('manage-procedure-evaluation', fn (User $user) => $user->canManageProcedureEvaluation());
        Gate::define('manage-academy-evaluation', fn (User $user) => $user->canManageProcedureEvaluation());
        Gate::define('access-compliance-report', fn (User $user) => $user->canAccessComplianceReport());
        Gate::define('manage-compliance-assignment', fn (User $user) => $user->canManageComplianceAssignment());
        Gate::define('manage-gl-setting', fn (User $user) => $user->canManageGlSetting());
    }
}
