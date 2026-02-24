<?php

namespace App\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use App\View\Composers\CfpSideBarComposer;
use App\View\Composers\EtpSideBarComposer;
use App\View\Composers\FormateurSideBarComposer;
use App\View\Composers\StudentSideBarComposer;




class ViewServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        View::composer('layouts.master', CfpSideBarComposer::class);
        View::composer('layouts.masterEtp', EtpSideBarComposer::class);
        View::composer('layouts.masterForm', FormateurSideBarComposer::class);
        View::composer('layouts.masterEmp', StudentSideBarComposer::class);
    }
}
