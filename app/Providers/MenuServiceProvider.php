<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class MenuServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot()
    {

        View::composer('*', function ($view) {
            $user = auth()->user();

            $menus = [];

            if ($user && ($user->hasRole('Formateur') || $user->hasRole('Cfp') || $user->hasRole('EmployeCfp'))) {
                $menus[] = [
                    'menu_title' => 'CRUD QCM',
                    'menu_icon' => 'book-open-reader',
                    'menu' => 1,
                    'label' => 'Créer un QCM',
                    'endpoint' => 'qcm/create',
                    'route' => route('create.qcm.form'),
                    'icon' => 'plus',
                ];
                // $menus[] = [
                //     'menu_title' => 'Results',
                //     'menu_icon' => 'book-open-reader',
                //     'menu' => 2,
                //     'label' => 'Résultats global QCM',
                //     'endpoint' => 'global-results/qcm',
                //     'route' => route('ctf.qcm.globalresults.index'),
                //     'icon' => 'list',
                // ];
            } elseif ($user && $user->hasRole('Particulier')) {
                $menus[] = [
                    'menu_title' => 'Purchase & History Credits',
                    'menu_icon' => 'credit-card',
                    'menu' => 1,
                    'label' => 'Acheter crédits',
                    'endpoint' => 'particulier/credits-pack/buy',
                    'route' => route('credits.index.particulier'),
                    'icon' => 'coins',
                ];
                $menus[] = [
                    'menu_title' => 'Purchase & History Credits',
                    'menu_icon' => 'credit-card',
                    'menu' => 1,
                    'label' => 'Historiques achats crédits',
                    'endpoint' => 'credits-payments',
                    'route' => route('credits-payments.index'),
                    'icon' => 'history',
                ];
            } elseif ($user && $user->hasRole('Employe')) {
                $menus[] = [
                    'menu_title' => 'Campaign or Received Invitation List',
                    'menu_icon' => 'envelope',
                    'menu' => 1,
                    'label' => 'Liste des campagnes',
                    'endpoint' => 'qcm/invitations-index',
                    'route' => route('qcm.invitations.index'),
                    'icon' => 'list',
                ];
            } elseif ($user && $user->hasRole('Referent')) {
                $menus[] = [
                    'menu_title' => 'Campaign or Received Invitation List',
                    'menu_icon' => 'envelope',
                    'menu' => 1,
                    'label' => 'Lancer une campagne',
                    'endpoint' => 'qcm/invitation/campaign/step-one',
                    'route' => route('qcm.invitation.campaign.step-one'),
                    'icon' => 'envelope',
                ];
                $menus[] = [
                    'menu_title' => 'Campaign or Received Invitation List',
                    'menu_icon' => 'envelope',
                    'menu' => 1,
                    'label' => 'Liste des campagnes',
                    'endpoint' => 'qcm/invitation/campaign',
                    'route' => route('qcm.invitation.campaign.index'),
                    'icon' => 'list',
                ];
                $menus[] = [
                    'menu_title' => 'Campaign or Received Invitation List',
                    'menu_icon' => 'envelope',
                    'menu' => 1,
                    'label' => 'Liste des invitations',
                    'endpoint' => 'qcm/invitations-index',
                    'route' => route('qcm.invitations.index'),
                    'icon' => 'list',
                ];
                $menus[] = [
                    'menu_title' => 'Purchase & History Credits',
                    'menu_icon' => 'credit-card',
                    'menu' => 2,
                    'label' => 'Historiques achats crédits',
                    'endpoint' => 'credits-payments',
                    'route' => route('credits-payments.index'),
                    'icon' => 'history',
                ];
            }

            // dd($menus); // Vérifier si les menus sont bien générés après un changement de page

            $walletAuthUser = 0;

            if ($user) {
                $walletModel = new \App\Models\CreditsWallet();
                $walletAuthUser = $walletModel->user_credit_walletBasedOnRole(auth()->id());
            }

            // Correction : Utilisez la méthode groupMenusByLabelTC pour regrouper les menus
            $groupedMenus = $this->groupMenusByLabelTC($menus);

            // Correction : Transmettez à la fois menus et groupedMenus à la vue
            $view->with([
                'menus' => $menus,
                'groupedMenus' => $groupedMenus,
                'walletAuthUser' => $walletAuthUser,
                'user' => $user,
            ]);
        });
    }

    /**
     * Regroupe les menus par label.
     * 
     * @param array $menus
     * @return \Illuminate\Support\Collection
     */
    public function groupMenusByLabelTC($menus)
    {
        return collect($menus)
            ->groupBy('menu_title')
            ->map(function ($items, $title) {
                $menuIcon = $items->first()['menu_icon'] ?? null;
                return [
                    'items' => $items,
                    'icon' => $menuIcon,
                ];
            });
    }
}
