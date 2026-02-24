<?php

namespace App\Providers;

use App\Interfaces\ApprenantInterface;
use App\Interfaces\CustomerOther\EmployeInterface;
use App\Interfaces\FormateurInterface;
use App\Interfaces\InvitationInterface as InvitationInterfaceClean;
use App\Interfaces\LieuInterface;
use App\Interfaces\UserRegisterInterface;
use App\Models\User;
use App\Services\ApprenantService;
use App\Services\CfpService;
use App\Services\CustomerOther\Employe\StoreService;
use App\Services\DossierService;
use App\Services\EntrepriseService;
use App\Services\FormateurService;
use App\Services\LieuService;
use App\Services\ParticulierService;
use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(UserRegisterInterface::class, ParticulierService::class);
        $this->app->singleton(UserRegisterInterface::class, CfpService::class);
        $this->app->singleton(LieuInterface::class, LieuService::class);
        $this->app->singleton(EmployeInterface::class, StoreService::class);
        $this->app->singleton(ApprenantInterface::class, ApprenantService::class);
        $this->app->singleton(InvitationInterfaceClean::class, EntrepriseService::class);
        $this->app->singleton(InvitationInterfaceClean::class, CfpService::class);
        $this->app->singleton(FormateurInterface::class, FormateurService::class);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Route::pattern('id', '[0-9]+');
        Paginator::useBootstrapFive();
        View::composer('*', function ($view) {
            if (Auth::check()) {
                $authenticatedUser = Auth::user();
                $user = User::findOrFail($authenticatedUser->id);
                $subscriptions = $user->planSubscriptions()->with('plan')->first();

                // if (Auth::user()->id === 1) {
                //     $infoProfilCfp = DB::table('customers')
                //         ->select('idCustomer', 'customerName', 'nif', 'stat', 'logo', 'customerEmail', 'idTypeCustomer')
                //         ->where('idCustomer', $authenticatedUser->id)
                //         ->first();
                // } else {
                //     $zay = DB::select("SELECT employes.idCustomer, customerName, nif, stat, logo, customerEmail, idTypeCustomer FROM employes INNER JOIN customers ON employes.idCustomer = customers.idCustomer WHERE idEmploye = ?", [$authenticatedUser->id]);
                //     $infoProfilCfp = $zay[0];
                // }
                $infoProfilCfp = DB::table('customers')
                    ->select('idCustomer', 'customerName', 'nif', 'stat', 'logo', 'customerEmail', 'idTypeCustomer')
                    ->where('idCustomer', $authenticatedUser->id)
                    ->first();

                $endpoint = config('filesystems.disks.do.url_cdn_digital');
                $bucket = config('filesystems.disks.do.bucket');
                $digitalOcean = $endpoint . '/' . $bucket;

                $etp_grouped = DB::table('etp_groupes')->where('idEntreprise', $authenticatedUser->id)->exists();

                $checkEtp = DB::table('entreprises')
                    ->where('idCustomer', Auth::user()->id)
                    ->first();


                $villes = DB::table('ville_codeds as vc')
                    ->orderBy('vc.vi_code_postal', 'asc')->get();

                $ville_addSalle = DB::table('villes')->select('idVille', 'ville')->orderBy('ville', 'asc')->get();
                $lieux_addSalle = DB::table('lieux')
                    ->select('idLieu', 'li_name', 'ville_name', 'vi_code_postal')
                    ->where('li_name', 'NOT LIKE', '%Default%')
                    ->join('ville_codeds', 'ville_codeds.id', 'lieux.idVilleCoded')
                    ->orderBy('li_name', 'asc')
                    ->get();

                $view->with('sub', $subscriptions)
                    ->with('infoProfilCfp', $infoProfilCfp)
                    ->with('bucket', $bucket)
                    ->with('endpoint', $endpoint)
                    ->with('digitalOcean', $digitalOcean)
                    ->with('checkEtp', $checkEtp)
                    ->with('isEtpGrouped', $etp_grouped)
                    ->with('ville', $villes)
                    ->with('ville_addSalle', $ville_addSalle)
                    ->with('lieux_addSalle', $lieux_addSalle);
            } else {
                $view->with('sub', null)
                    ->with('infoProfilCfp', null)
                    ->with('bucket', null)
                    ->with('endpoint', null)
                    ->with('digitalOcean', null)
                    ->with('checkEtp', null)
                    ->with('isEtpGrouped', null)
                    ->with('ville', null)
                    ->with('ville_addSalle', null)
                    ->with('lieux_addSalle', null);
            }
        });
    }
}
