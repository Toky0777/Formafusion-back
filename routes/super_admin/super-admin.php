<?php

use App\Http\Controllers\AbonnementController;
use App\Http\Controllers\AgendaSuperAdminController;
use App\Http\Controllers\AssiduiteSuperAdminController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\QcmController;
use App\Http\Controllers\CreditsPaymentController;
use App\Http\Controllers\CreditsPacksController;
use App\Http\Controllers\CrudAbnController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ModuleController;
use App\Http\Controllers\ModuleInterneController;
use App\Http\Controllers\ProgrammeController;
use App\Http\Controllers\ProgrammeInterneController;
use App\Http\Controllers\StatisticsController;
use App\Http\Controllers\SuperAdminController;
use App\Http\Controllers\TransactionHistoryController;
use App\Http\Controllers\UserSuperAdminController;
use App\Http\Controllers\VilleController;

Route::middleware(['auth', 'isSuperAdmin'])->group(function () {
    Route::get('homeAdmin', [HomeController::class, 'indexAdmin']);

    //Liste projet
    Route::get('superAdmins/projetlist', [AssiduiteSuperAdminController::class, 'projetlist'])->name('superAdmins.projetlist');

    //Assiduités
    Route::get('superAdmins/assiduite', [AssiduiteSuperAdminController::class, 'assiduite'])->name('superAdmins.assiduite');

    //AgendaSuperAdmin
    Route::get('superAdmins/agenda', [AgendaSuperAdminController::class, 'agenda'])->name('superAdmins.agenda');

    // aboutSuperAdmin
    Route::get('superAdmins', [SuperAdminController::class, 'about'])->name('superAdmins.about');

    // CRUD Abonnement SUPER ADMIN
    Route::prefix('crudAbn')->name('crudAbn.')->controller(CrudAbnController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/card', 'card')->name('card');
        Route::get('/create', 'create')->name('create');
        Route::post('', 'store')->name('store');
        Route::get('/{id}', 'show')->name('show');
        Route::get('/{id}/editPlan', 'editPlan')->name('editPlan');
        Route::get('/{id}/editFeature', 'editFeature')->name('editFeature');
        Route::patch('/{id}', 'update')->name('update');
        Route::delete('/{id}', 'destroy')->name('destroy');
    });
    Route::post('/subscriptions/change/{id}', [CrudAbnController::class, 'change'])->name('subscriptions.change');

    // Abonnement.Cfp
    Route::get('abonnement/list', [CrudAbnController::class, 'allAbn'])->name('abonnement.superA.index');

    Route::prefix('abonnement/su')->group(function () {
        Route::get('/{id}', [AbonnementController::class, 'listSubscription'])->name('abonnement.su');
        Route::get('/payment/{user_id}', [AbonnementController::class, 'paymentSubscription']);
    });

    //ETP list SuperAdmin
    Route::get('superAdmins/entreprises', [SuperAdminController::class, 'entreprises'])->name('superAdmins.entreprises');
    Route::patch('superAdmins/entreprises/{idEtp}/update', [SuperAdminController::class, 'blockEtp']);
    Route::patch('superAdmins/entreprises/{idEtp}/unblock', [SuperAdminController::class, 'unblockEtp']);
    Route::patch('superAdmins/entreprises/{idEtp}/trash', [SuperAdminController::class, 'trashEtp']);

    //CFP list SuperAdmin
    Route::get('superAdmins/cfp', [SuperAdminController::class, 'cfp'])->name('superAdmins.cfp');
    Route::patch('superAdmins/cfp/{idCfp}/update', [SuperAdminController::class, 'blockCfp']);
    Route::patch('superAdmins/cfp/{idCfp}/unblock', [SuperAdminController::class, 'unblockCfp']);
    Route::patch('superAdmins/cfp/{idCfp}/trash', [SuperAdminController::class, 'trashCfp']);

    //Users list SuperAdmin
    Route::get('superAdmins/formateurs', [UserSuperAdminController::class, 'formateurs'])->name('superAdmins.formateurs');
    Route::get('superAdmins/formateurs/cfp', [UserSuperAdminController::class, 'formateurscfp'])->name('superAdmins.formateurcfp');
    Route::get('superAdmins/formateurs/etp', [UserSuperAdminController::class, 'formateursetp'])->name('superAdmins.formateursentreprise');
    Route::get('superAdmins/referents/cfp', [UserSuperAdminController::class, 'referentscfp'])->name('superAdmins.referentscfp');
    Route::get('superAdmins/referents/entreprise', [UserSuperAdminController::class, 'referentsetp'])->name('superAdmins.referentsetp');
    Route::get('superAdmins/modules/cfp', [UserSuperAdminController::class, 'cfpmodule'])->name('superAdmins.cfpmodules');
    Route::get('superAdmins/modules/entreprise', [UserSuperAdminController::class, 'etpmodule'])->name('superAdmins.etpmodules');

    Route::get('superAdmins/apprenants/entreprise', [UserSuperAdminController::class, 'etpapprenants'])->name('superAdmins.apprenants');

    // Suppresion module par le SuperAdmin
    Route::get('superAdmins/modules/cfp/delete/{idModule}', [UserSuperAdminController::class, 'deleteCfpModule']);
    Route::get('superAdmins/formateurs/cfp/delete/{idFormateur}', [UserSuperAdminController::class, 'deleteFormateursCfp']);
    Route::get('superAdmins/referents/cfp/delete/{idReferent}', [UserSuperAdminController::class, 'deleteReferentsCfp']);

    Route::get('superAdmins/referents/entreprise/delete/{idReferent}', [UserSuperAdminController::class, 'deleteReferentsEtp']);
    Route::get('superAdmins/formateurs/etp/delete/{idEmploye}', [UserSuperAdminController::class, 'deleteFormateursEtp']);
    Route::get('superAdmins/modules/entreprise/delete/{idModule}', [UserSuperAdminController::class, 'deleteEtpModule']);
    Route::get('superAdmins/apprenants/entreprise/delete/{idEmploye}', [UserSuperAdminController::class, 'deleteEtpApprenants']);


    Route::get('superAdmins/modules/{idModule}', [UserSuperAdminController::class, 'show']);

    Route::get('superAdmins/modules/{idModule}/objectifs', [ModuleController::class, 'getObjectif']);
    Route::get('superAdmins/modules/{idModule}/prestations', [ModuleController::class, 'getPrestation']);
    Route::get('superAdmins/modules/{idModule}/cibles', [ModuleController::class, 'getCible']);
    Route::get('superAdmins/modules/{idModule}/prerequis', [ModuleController::class, 'getPrerequis']);
    Route::get('superAdmins/modules/programmes/{idModule}', [ProgrammeController::class, 'getProgramme']);

    Route::get('superAdmins/etpDetail/modules/{idModule}', [UserSuperAdminController::class, 'showEtp']);
    Route::get('superAdmins/etp/modules/{idModule}/objectifs', [ModuleInterneController::class, 'getObjectif']);
    Route::get('superAdmins/etp/modules/{idModule}/prestations', [ModuleInterneController::class, 'getPrestation']);
    Route::get('superAdmins/etp/modules/{idModule}/cibles', [ModuleInterneController::class, 'getCible']);
    Route::get('superAdmins/etp/programmes/{idModule}', [ProgrammeInterneController::class, 'getProgramme']);


    //Projet List
    Route::get('superAdmins/projets/typeProjet/{idTypeProjet}', [SuperAdminController::class, 'projetstype'])->name('superAdmins.projets.type');

    //Projet par financement
    Route::get('superAdmins/projets/fond/{idPaiement}', [SuperAdminController::class, 'projetsFondAutres'])->name('superAdmins.projets.fond');

    Route::get('superAdmins/organismelist', [AssiduiteSuperAdminController::class, 'organismelist'])->name('superAdmins.organismelist');
    Route::get('superAdmins/organismevalidate', [AssiduiteSuperAdminController::class, 'organismevalidate'])->name('superAdmins.organismevalidate');
    Route::get('superAdmins/domaineList', [AssiduiteSuperAdminController::class, 'domaineList'])->name('superAdmins.domaineList');
    Route::post('superAdmins/domaineInsert', [AssiduiteSuperAdminController::class, 'domaineInsert'])->name('superAdmins.domaineInsert');
    Route::get('superAdmins/domaineUpdate', [AssiduiteSuperAdminController::class, 'domaineUpdate']);
    Route::delete('superAdmins/domaineDelete/{id}', [AssiduiteSuperAdminController::class, 'deleteDomaine']);
    Route::get('superAdmins/publicityModule', [AssiduiteSuperAdminController::class, 'publicityModule'])->name('superAdmins.publicityModule');
    Route::get('superAdmins/moduleCfp/{id}', [AssiduiteSuperAdminController::class, 'moduleCfp'])->name('superAdmins.moduleCfp');
    Route::post('superAdmins/modulePromu/{id}', [AssiduiteSuperAdminController::class, 'modulePromu'])->name('superAdmins.modulePromu');
    Route::get('superAdmins/listeModulePromu', [AssiduiteSuperAdminController::class, 'listeModulePromu'])->name('superAdmins.listeModulePromu');
    Route::post('superAdmins/updateRang', [AssiduiteSuperAdminController::class, 'updateRang'])->name('superAdmins.updateRang');
    Route::get('superAdmins/detache/{id}', [AssiduiteSuperAdminController::class, 'detache'])->name('superAdmins.detache');

    Route::delete('superAdmins/domaineProject/{id}', [AssiduiteSuperAdminController::class, 'deleteProject']);

    // villes
    Route::get('villes', [VilleController::class, 'index'])->name('superAdmin.villes.index');
    Route::post('villes', [VilleController::class, 'store'])->name('superAdmin.villes.store');
    Route::delete('villes/{id}', [VilleController::class, 'destroy'])->name('superAdmin.villes.destroy');
    Route::post('importer', [VilleController::class, 'importer'])->name('importer.import');

    //statistics
    Route::get('/statistics/project', [StatisticsController::class, 'project']);
    Route::get('/statistics/learner', [StatisticsController::class, 'learner']);
    Route::get('/statistics/cfp', [StatisticsController::class, 'cfp']);
    Route::get('/statistics/entreprise', [StatisticsController::class, 'entreprise']);

    Route::put('/customers/{id}', [AssiduiteSuperAdminController::class, 'update'])->name('customers.update');

    # Routes Testing Center
    // Routes pour le CRUD des packs de crédits
    Route::prefix('credits-packs')->group(function () {
        Route::get('/', [CreditsPacksController::class, 'index_credits_packs'])->name('credits.packs.index'); # Route vers la vue des packs de crédits
        Route::post('/store', [CreditsPacksController::class, 'store_credits_packs'])->name('credits.packs.store'); # Appelé directement dans la vue "index_credits_packs" pour sauvegarder le pack que l'on souhaite créer
        Route::put('/{id}/update', [CreditsPacksController::class, 'update_credits_packs'])->name('credits.packs.update'); # Appelé directement dans la vue "index_credits_packs" pour màj le pack que l'on souhaite
        Route::delete('/{id}/delete', [CreditsPacksController::class, 'delete_credits_packs'])->name('credits.packs.delete'); # Appelé directement dans la vue "index_credits_packs" pour supprimer le pack que l'on souhaite
    });
    // Routes pour le CRUD des packs de crédits

    // Routes pour les transactions de crédits (crédit ou débit)
    Route::get('/transactions/dashboard', [TransactionHistoryController::class, 'dashboardTransactionHistory'])->name('transactions.history.dashboard'); # Route pour le dashboard des transactions de crédits 
    Route::get('/transactions', [TransactionHistoryController::class, 'index'])->name('transactions.index'); # Route pour la vue index des transactions de crédits
    Route::get('/transactions/details', [TransactionHistoryController::class, 'getDetails'])->name('transactions.details'); # Route pour les détails des transactions
    // Routes pour les transactions de crédits (crédit ou débit)

    // Routes pour le tableau de bord des chiffres d'affaires de ventes de crédits
    Route::prefix('dashboard')->group(function () {
        Route::get('/sales-revenue', [CreditsPaymentController::class, 'salesRevenusDashboard'])->name('dashboard.sales.revenue'); # Route pour le dashboard des chiffres d'affaires d'achat de crésits
        Route::get('/sales-revenue/filter', [CreditsPaymentController::class, 'filterSalesRevenue'])->name('dashboard.sales-revenue.filter'); # Route pour les filtres dans les chiffres d'affaires
    });
    // Routes pour le tableau de bord des chiffres d'affaires de ventes de crédits

    // Routes pour les résultats des Qcm global du côtés des superadmin
    Route::prefix('qcm')->group(function () {
        Route::get('/cfp/list', [QcmController::class, 'indexCfpListForQCM'])->name('qcm.indexCfpListForQcm'); # Route menant à la liste des centres de formations pour les résultats global des qcm côtés superadmin
        Route::get('/cfp/{id}/global/results', [QcmController::class, 'index_global_results_allQcmOfUserSA'])->name('ctf.qcm.globalresults.index.superadmin'); # Route vers la vue des résultats globals des qcm d'un centre de formation côtés superadmin
        Route::get('/list/cfp/{id}', [QcmController::class, 'showCfpQcm'])->name('qcm.showCfpQcm'); # Route menant à la liste des qcm du centre de formation
        Route::get('/{id}/results', [QcmController::class, 'showQcmResults'])->name('qcm.results.superadmin'); # Route pour les résultats d'un qcm
    });
});
