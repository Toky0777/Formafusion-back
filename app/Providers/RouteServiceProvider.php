<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */

    public const HOME = '/formation/espaceClient';
    public const HOMESADMIN = '/homeAdmin';
    public const HOMEETP = '/formation/espaceClient';
    public const HOMEEMP = '/homeEmp';
    public const HOMEFORM = '/home-form';
    public const HOMEFORMINTERN = '/homeFormInterne';
    public const HOMEPARTICULIER = '/homeParticulier';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     *
     * @return void
     */
    public function boot()
    {
        $this->configureRateLimiting();

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));

            Route::middleware('web')
                ->group(base_path('routes/cfps/cfp.php'));

            Route::middleware('web')
                ->group(base_path('routes/cfps/cfp-emp.php'));

            Route::middleware('web')
                ->group(base_path('routes/employes/employe.php'));

            Route::middleware('web')
                ->group(base_path('routes/entreprises/etp.php'));

            Route::middleware('web')
                ->group(base_path('routes/formateurs/formateur.php'));

            Route::middleware('web')
                ->group(base_path('routes/formateurs/formateur-interne.php'));

            Route::middleware('web')
                ->group(base_path('routes/particuliers/particulier.php'));

            Route::middleware('web')
                ->group(base_path('routes/super_admin/super-admin.php'));
        });

        Route::pattern('id', '[0-9]+');
    }

    /**
     * Configure the rate limiters for the application.
     *
     * @return void
     */
    protected function configureRateLimiting()
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
    }
}
