<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CfpInviteEtp;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AbnCfpController;
use App\Http\Controllers\AbonnementController;
use App\Http\Controllers\ModuleController;
use App\Http\Controllers\ProfilController;
use App\Http\Controllers\ProjetController;
use App\Http\Controllers\SeanceController;
use App\Http\Controllers\GalleryController;
use App\Http\Controllers\AnalyticController;
use App\Http\Controllers\SalleCfpController;
use App\Http\Controllers\SalleEtpController;
use App\Http\Controllers\AgendaCfpController;
use App\Http\Controllers\ApprenantController;
use App\Http\Controllers\FormateurController;
use App\Http\Controllers\ProgrammeController;
use App\Http\Controllers\ReportingController;
use App\Http\Controllers\RessourceController;
use App\Http\Controllers\AgenceController;
use App\Http\Controllers\AttestationController;
use App\Http\Controllers\BadgeController;
use App\Http\Controllers\EmargementController;
use App\Http\Controllers\EmployecfpController;
use App\Http\Controllers\EvaluationController;
use App\Http\Controllers\ProjetInterController;
use App\Http\Controllers\ModuleRessourceController;
use App\Http\Controllers\BankAccountController;
use App\Http\Controllers\CfpAnnuaireController;
use App\Http\Controllers\CfpDashboardController;
use App\Http\Controllers\CfpProfilController;
use App\Http\Controllers\ChiffreAffaireRepportingCfp;
use App\Http\Controllers\ParticulierController;
use App\Http\Controllers\CoursController;
use App\Http\Controllers\SecurityController;
use App\Http\Controllers\ShowDrawerController;
use App\Http\Controllers\RestaurationController;
use App\Http\Controllers\DossierController;
use App\Http\Controllers\FactureAcompteController;
use App\Http\Controllers\FactureController;
use App\Http\Controllers\FactureProfoController;
use App\Http\Controllers\historiqueController;
use App\Http\Controllers\InvoicePaymentController;
use App\Http\Controllers\SubContractorController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\RepportingClientController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\ConflitsController;
use App\Http\Controllers\LieuxController;
use App\Http\Controllers\SalleController;
use App\Http\Controllers\EtpInformalController;
use App\Http\Controllers\PedaController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\ProspectionController;
use App\Http\Controllers\FinancialGoalController;
use App\Http\Controllers\MarketPlaceController;
use App\Http\Controllers\MobileMoneyAccountController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\SectionController;

// Sous Référent Cfp
Route::middleware(['auth', 'isEmployeCfp'])->group(function () {

    Route::get('searchGenerality', [SearchController::class, 'searchGenerality'])->name('searchGenerality');
    Route::get('searchIndex_apprenant', [SearchController::class, 'searchIndexApprenant'])->name('searchIndexApprenant');
    Route::get('searchIndex_referent', [SearchController::class, 'searchIndexReferent'])->name('searchIndexReferent');
    Route::get('searchIndex_rormateur', [SearchController::class, 'searchIndexFormateur'])->name('searchIndexFormateur');
    Route::get('searchIndex_client', [SearchController::class, 'searchIndexClient'])->name('searchIndexClient');
    Route::get('searchIndex_cfp', [SearchController::class, 'searchIndexCfp'])->name('searchIndexCfp');
    Route::get('searchIndex_lieu', [SearchController::class, 'searchIndexLieu'])->name('searchIndexLieu');
    Route::get('searchIndex_projet', [SearchController::class, 'searchIndexProjet'])->name('searchIndexProjet');
    Route::get('searchIndex_projet_reference', [SearchController::class, 'searchIndexReferenceProject'])->name('searchIndexReferenceProject');
    Route::get('searchIndex_projet_city', [SearchController::class, 'searchIndexProjectByCity'])->name('searchIndexProjectByCity');
    Route::get('searchIndex_projet_place', [SearchController::class, 'searchIndexProjectByPlace'])->name('searchIndexProjectByPlace');
    Route::get('searchIndex_projet_neighborhood', [SearchController::class, 'searchIndexProjectByNeighborhood'])->name('searchIndexProjectByNeighborhood');
    Route::get('searchIndex_course', [SearchController::class, 'searchIndexCourse'])->name('searchIndexCourse');
    Route::get('searchIndex_referent_customer', [SearchController::class, 'searchIndexReferentCustomer'])->name('searchIndexReferentCustomer');
    Route::get('searchIndex_projet/{id}}', [SearchController::class, 'getProjectEtp'])->name('getProjectEtpWithCfp');
    Route::get('searchIndex_folder', [SearchController::class, 'searchIndexFolder'])->name('searchIndexFolder');
    Route::get('searchIndex_particular', [SearchController::class, 'searchIndexParticular'])->name('searchIndexParticular');
    Route::get('search_by_key', [SearchController::class, 'keySuggestion'])->name('keySuggestion');


    Route::get('search', [HomeController::class, 'search'])->name('search');
    Route::get('home/customer', [HomeController::class, 'getIdCustomer']);
    Route::get('/confidentialite', [HomeController::class, 'confidentialite'])->name('confidentialite');
    Route::get('/condition', [HomeController::class, 'condition'])->name('condition');

    Route::prefix('/home')->group(function () {
        Route::get('/', [CfpDashboardController::class, 'index'])->name('home');
        Route::get('/accueil', [CfpDashboardController::class, 'accueil'])->name('accueil');
        Route::get('/refresh-token', [CfpDashboardController::class, 'refreshToken']);

        Route::get('/api/config', [CfpDashboardController::class, 'getConfigApi']);
        Route::get('/ca/{month}/{type}', [CfpDashboardController::class, 'currentCa']);
        Route::get('/learner/{month}/{type}', [CfpDashboardController::class, 'getLearner']);
    });

    // Referents
    Route::prefix('cfp/referents')->group(function () {
        Route::get('/', [EmployecfpController::class, 'index'])->name('cfp.referents.index');
        Route::get('/{idEmploye}/show', [EmployecfpController::class, 'show']);
        Route::get('/{idEmploye}/edit', [EmployecfpController::class, 'edit']);
        Route::get('/listReferent', [EmployecfpController::class, 'listReferent'])->name('list.referent');
        Route::patch('/{idEmploye}', [EmployecfpController::class, 'update']);
        Route::post('/{idEmploye}/updatePhoto', [EmployecfpController::class, 'updatePhoto']);
        Route::patch('/updatePassword/{idEmploye}', [EmployecfpController::class, 'updatePassword']);
    });

    // Module
    Route::prefix('cfp/modules')->group(function () {
        Route::get('/', [ModuleController::class, 'index'])->name('cfp.modules.index');
        Route::post('/', [ModuleController::class, 'store'])->name('cfp.modules.store');
        Route::patch('/{idModule}/makeOnline', [ModuleController::class, 'makeOnline']);
        Route::patch('/{idModule}/makeOffline', [ModuleController::class, 'makeOffline']);
        Route::patch('/{idModule}/makeTrashed', [ModuleController::class, 'makeTrashed']);
        Route::patch('/{idModule}/restoreModule', [ModuleController::class, 'restoreModule']);
        Route::delete('/{idModule}/deleteModule', [ModuleController::class, 'destroy'])->name('modules.destroy');
        Route::get('/{idModule}/edit', [ModuleController::class, 'edit'])->name('modules.edit');
        Route::put('/{idModule}', [ModuleController::class, 'update'])->name('cfp.modules.update');
        Route::get('/{idModule}', [ModuleController::class, 'show'])->name('cfp.modules.show');
        Route::get('/{moduleName}/searchOnLine', [ModuleController::class, 'searchOnLine'])->name('cfp.modules.searchOnLine');
        Route::get('/{moduleName}/searchOffLine', [ModuleController::class, 'searchOffLine'])->name('cfp.modules.searchOffLine');
        Route::patch('/{idModule}/addImage', [ModuleController::class, 'updateImage'])->name('cfp.modules.updateImage');
        Route::post('/{idModule}/updateImgMdl', [ModuleController::class, 'updateImgMdl'])->name('cfp.modules.updateImgMdl');

        // Objectifs
        Route::post('/{idModule}/objectifs', [ModuleController::class, 'addObjectif']);
        Route::get('/{idModule}/objectifs', [ModuleController::class, 'getObjectif']);
        Route::delete('/{idObjectif}/objectifs', [ModuleController::class, 'deleteObjectif']);

        // Prestations
        Route::post('/{idModule}/prestations', [ModuleController::class, 'addPrestation']);
        Route::get('/{idModule}/prestations', [ModuleController::class, 'getPrestation']);
        Route::delete('/{idPrestation}/prestations', [ModuleController::class, 'deletePrestation']);

        // Prerequis
        Route::post('/{idModule}/prerequis', [ModuleController::class, 'addPrerequis']);
        Route::get('/{idModule}/prerequis', [ModuleController::class, 'getPrerequis']);
        Route::delete('/{idPrestation}/prerequis', [ModuleController::class, 'deletePrerequis']);

        // Cibles
        Route::post('/{idModule}/cibles', [ModuleController::class, 'addCible']);
        Route::get('/{idModule}/cibles', [ModuleController::class, 'getCible']);
        Route::delete('/{idCible}/cibles', [ModuleController::class, 'deleteCible']);

        // DomaineFormations
        Route::get('/domaine/getDomainFormations', [ModuleController::class, 'getDomainFormations'])->name('cfp.modules.getDomainFormations');

        Route::get('/get/getAllModule', [ModuleController::class, 'getAllModuleCfp']);
        Route::post('/firstModule', [ModuleController::class, 'storeFirst']);
        Route::get('/get/firstModule', [ModuleController::class, 'getFirstModules']);

        Route::get('/detail/{idModule}/drawer', [ModuleController::class, 'detailModule']);

        Route::get('/level/getModuleLevel', [ModuleController::class, 'getModuleLevel']);

        // Modifie le qualité de progress bar
        Route::get('/{idModule}/getSumQuality', [ModuleController::class, 'getSumQuality']);
    });

    //Reporting
    Route::prefix('reporting')->group(function () {

        // -- formation
        Route::get('/exportXl', [ReportingController::class, 'exportXl'])->name('exportXl');
        Route::get('/exportPdf', [ReportingController::class, 'exportPdf'])->name('exportPdf');
        Route::get('/formation', [ReportingController::class, 'formation'])->name('ReportingFormation');
        Route::post('/formation', [ReportingController::class, 'filterFormation'])->name('reporting.filter.formation');

        // -- apprenant
        Route::get('/exportXlApp', [historiqueController::class, 'exportXlApp'])->name('exportXlApp');
        Route::get('/exportPdfApp', [historiqueController::class, 'exportPdfApp'])->name('exportPdfApp');
        Route::get('/apprenant', [historiqueController::class, 'apprenant'])->name('reporting.filter.apprenant');
        Route::get('/learner', [historiqueController::class, 'getLearner']);
        Route::get('/learner/project', [historiqueController::class, 'getProjectLearner'])->name('reporting.project-customer');
        Route::get('/historique/search/{name}/name', [historiqueController::class, 'searchName']);

        // -- client
        Route::get('/exportXlCl', [RepportingClientController::class, 'exportXlCl'])->name('exportXlCl');
        Route::get('/exportPdfCl', [RepportingClientController::class, 'exportPdfCl'])->name('exportPdfCl');
        Route::get('/client', [RepportingClientController::class, 'client'])->name('reporting.filter.client');
        Route::get('/client_list', [RepportingClientController::class, 'getCustomer']);
        Route::get('/search/customer', [RepportingClientController::class, 'searchByCustomer'])->name('reporting.customer.search');
        Route::get('/etp/search/{name_etp}/name', [RepportingClientController::class, 'searchEtp'])->name('etp.search.name');

        // -- cours
        Route::get('/exportXl', [CoursController::class, 'exportXl'])->name('exportXl');
        Route::get('/exportPdf', [CoursController::class, 'exportPdf'])->name('exportPdf');
        Route::get('/cours', [CoursController::class, 'cours'])->name('reporting.filter.cours');
        Route::get('cours/search', [CoursController::class, 'searchByModule']);

        // -- chiffre d'affaire
        // Route::get('/exportXl', [CoursController::class, 'exportXl'])->name('exportXl');
        // Route::get('/exportPdf', [CoursController::class, 'exportPdf'])->name('exportPdf');
        Route::get('/chiffre/projet', [ChiffreAffaireRepportingCfp::class, 'caProjet'])->name('reporting.caProjet');
        Route::get('/chiffre/projet/{year}', [ChiffreAffaireRepportingCfp::class, 'caProjetYear'])->name('reporting.caProjetYear');
        Route::get('/chiffre/cours', [ChiffreAffaireRepportingCfp::class, 'caModule'])->name('reporting.caModule');
        Route::get('/chiffre/cours/{year}', [ChiffreAffaireRepportingCfp::class, 'caModuleYear'])->name('reporting.caModuleYear');
        Route::get('/chiffre/client', [ChiffreAffaireRepportingCfp::class, 'caCustomer'])->name('reporting.caCustomer');
        Route::get('/chiffre/client/{year}', [ChiffreAffaireRepportingCfp::class, 'caCustomerYear'])->name('reporting.caCustomerYear');
        Route::get('/chiffre/reference', [ChiffreAffaireRepportingCfp::class, 'caReference'])->name('reporting.caReference');
        Route::get('/chiffre/reference/{year}', [ChiffreAffaireRepportingCfp::class, 'caReferenceYear']);
        Route::get('/chiffre/mois', [ChiffreAffaireRepportingCfp::class, 'caMonth'])->name('reporting.caMonth');
        Route::get('/chiffre/mois/{year}', [ChiffreAffaireRepportingCfp::class, 'caMonthYear']);
        Route::get('/chiffre/dossier', [ChiffreAffaireRepportingCfp::class, 'caFolder'])->name('reporting.caFolder');
        Route::get('/chiffre/dossier/{year}', [ChiffreAffaireRepportingCfp::class, 'caFolderYear']);
        Route::get('/chiffre/ville', [ChiffreAffaireRepportingCfp::class, 'caPlace'])->name('reporting.caPlace');
        Route::get('/chiffre/ville/{year}', [ChiffreAffaireRepportingCfp::class, 'caPlaceYear']);
    });


    // Programme
    Route::prefix('cfp/programmes')->group(function () {
        Route::post('/{idModule}', [ProgrammeController::class, 'store'])->name('cfp.programmes.store');
        Route::put('/test/{idProgramme}/update', [ProgrammeController::class, 'update'])->name('cfp.programmes.update');
        Route::get('/{idModule}', [ProgrammeController::class, 'getProgramme']);
        Route::get('/{idProgramme}/edit', [ProgrammeController::class, 'edit']);
        Route::delete('/{idProgramme}', [ProgrammeController::class, 'destroy'])->name('programmes.destroy');
    });


    //ModuleRessource
    Route::prefix('cfp/module/ressources')->group(function () {
        Route::post('/{idModule}', [ModuleRessourceController::class, 'store'])->name('cfp.module.ressources.store');
        Route::get('/{idModuleRessource}', [ModuleRessourceController::class, 'destroy'])->name('cfp.module.ressources.destroy');
        Route::get('/{idModuleRessource}/download', [ModuleRessourceController::class, 'download'])->name('cfp.module.ressources.download');
    });

    // Projets
    Route::prefix('/cfp/projets')->group(function () {
        Route::delete('/deletephoto/{idProjet}/{url}', [ProjetController::class, 'destroyPhoto'])->name('deletephoto.destroy');
        Route::get('/listephoto/{idProjet}/{idTypeImage}', [ProjetController::class, 'listePhotoMomentum'])->name('listephoto.momentum');
        Route::post('/uploadphoto/{idProjet}', [ProjetController::class, 'uploadPhotoMomentum'])->name('uploadphoto.momentum');
        Route::post('/', [ProjetController::class, 'store'])->name('cfp.projets.store');
        Route::get('/', [ProjetController::class, 'index'])->name('cfp.projets.index');
        Route::get('/{idProjet}/detail', [ProjetController::class, 'show'])->name('cfp.projets.show');
        Route::get('/{idProjet}/detail/momentum', [ProjetController::class, 'showmomentum'])->name('cfp.projets.showmomentum');
        Route::get('/formateur/{id}/mini-cv', [ProjetController::class, 'getMiniCV']);
        Route::post('/{idProjet}/update-taxe', [ProjetController::class, 'updateTaxe']);
        Route::get('/{idProjet}/{isEtp}/frais', [ProjetController::class, 'fraisdetails'])->name('cfp.projets.fraisdetails');
        Route::post('/{idProjet}/{idFrais}/{isEtp}/fraisprojet/assign', [ProjetController::class, 'fraisAssign'])->name('cfp.projets.fraisAssign');
        Route::post('/update-frais', [ProjetController::class, 'updateFrais'])->name('cfp.projets.updateFrais');
        Route::post('/{idProjet}/{idFraisProjet}/delete-frais', [ProjetController::class, 'fraisRemove'])->name('cfp.projets.deleteFrais');
        Route::post('/{idProjet}/total-frais', [ProjetController::class, 'fraisTotal'])->name('cfp.projets.fraisTotal');
        Route::get('/{idFraisProjet}/idProjet', [ProjetController::class, 'getIdProjetByIdFraisProjet'])->name('cfp.projets.getIdProjet');
        Route::delete('/{idProjet}/{idEtp}/removeEtpFraisProjet', [ProjetController::class, 'removeEtpFraisProjet'])->name('cfp.projets.removeEtpFraisProjet');
        Route::get('/fermeturefrais', [ProjetController::class, 'fermeturefrais'])->name('cfp.projets.fermeturefrais');
        Route::get('/{idProjet}/form/assign', [ProjetController::class, 'getFormAssign'])->name('cfp.projets.form.assign.index');
        Route::post('/{idProjet}/{idFormateur}/form/assign', [ProjetController::class, 'formAssign']);
        Route::get('/{idProjet}/getFormAdded', [ProjetController::class, 'getFormAdded']);
        Route::delete('/{idProjet}/{idFormateur}/form/assign', [ProjetController::class, 'formRemove']);
        Route::get('/{idProjet}/etp/assign', [ProjetController::class, 'getEtpAssign']);
        Route::patch('/{idProjet}/{idEtp}/etp/assign', [ProjetController::class, 'etpAssign']);
        Route::get('/{idProjet}/mainGetIdEtp', [ProjetController::class, 'mainGetIdEtp']);
        Route::get('/{idProjet}/mainGetIdModule', [ProjetController::class, 'mainGetIdModule']);
        Route::patch('/{idProjet}/{idModule}/module/assign', [ProjetController::class, 'moduleAssign']);
        Route::patch('/{idProjet}/date/assign', [ProjetController::class, 'dateAssign']);
        Route::get('/{idProjet}/details', [ProjetController::class, 'detailsJson']);
        Route::get('/{idModule}/getProgrammeProject', [ProjetController::class, 'getProgramme']);
        Route::get('/{idModule}/getModuleRessourceProject', [ProjetController::class, 'getModuleRessourceProject']);
        Route::delete('/{idProjet}/destroy', [ProjetController::class, 'destroy'])->name('cfp.projets.destroy');
        Route::post('/{idProjet}/duplicate', [ProjetController::class, 'duplicate'])->name('cfp.projets.duplicate');
        Route::patch('/{idProjet}/update/date', [ProjetController::class, 'updateDate'])->name('cfp.projets.updateDate');
        Route::patch('/{idProjet}/update/module', [ProjetController::class, 'updateModule'])->name('cfp.projets.updateModule');
        Route::patch('/update/financement/{idProjet}/{idCfp_inter}', [ProjetController::class, 'updateFinancement'])->name('cfp.projets.updateFinancement');
        Route::patch('/{idProjet}/update/price', [ProjetController::class, 'updatePrice'])->name('cfp.projets.updatePrice');
        Route::patch('/{idProjet}/{idSalle}/salle/assign', [ProjetController::class, 'salleAssign']);
        Route::get('/{idProjet}/getSalleAdded', [ProjetController::class, 'getSalleAdded']);
        Route::patch('/{idProjet}/cancel', [ProjetController::class, 'cancel']);
        Route::patch('/{idProjet}/repport', [ProjetController::class, 'repport']);
        Route::patch('/{idProjet}/close', [ProjetController::class, 'close']);
        Route::patch('/{idProjet}/updateProjet', [ProjetController::class, 'updateProjet']);
        Route::patch('/{idProjet}/updateNbPlace', [ProjetController::class, 'updateNbPlace']);
        Route::patch('/{idProjet}/updateProjetInter', [ProjetController::class, 'updateProjetInter']);
        Route::get('/filter/getDropdownItem', [ProjetController::class, 'getDropdownItem']);
        Route::get('/filter/items', [ProjetController::class, 'filterItems']);
        Route::get('/filter/item', [ProjetController::class, 'filterItem']);
        Route::patch('/{idProjet}/confirm', [ProjetController::class, 'confirm']);
        Route::get('/getVille', [ProjetController::class, 'getVille']);
        Route::patch('/{idProjet}', [ProjetController::class, 'updateVille']);
        Route::post('/{idProjet}/{idEtp}', [ProjetController::class, 'etpAssignInter']);
        Route::patch('/{idProjet}/update/modalite', [ProjetController::class, 'updateModalite'])->name('cfp.projets.updateModalite');
        Route::get('/getModalite', [ProjetController::class, 'getModalite']);
        Route::get('/parts/getAllParts', [ProjetController::class, 'getAllParts'])->name('parts.getAllParts');
        Route::post('/{idProjet}/{idParticulier}/part/assign', [ProjetController::class, 'assignPart']);
        Route::get('/{idProjet}/getPartAdded', [ProjetController::class, 'getPartAdded']);
        Route::delete('/{idProjet}/{idParticulier}/part/assign', [ProjetController::class, 'unassignPart'])->name('parts.unassign');
        Route::patch('/{idProjet}/updatePrivacy', [ProjetController::class, 'updatePrivacy']);
        Route::patch('/{idProjet}/trash', [ProjetController::class, 'trash'])->name('projets.cfp.trash');
        Route::patch('/{idProjet}/restore', [ProjetController::class, 'restore'])->name('projets.cfp.restore');

        Route::get('/list', [ProjetController::class, 'getProjectList'])->name('cfp.projets.list');

        Route::get('/detailProjetCfpPdf/{id}', [ProjetController::class, 'detailProjetCfpPdf'])->name('cfp.projets.detailProjetCfpPdf');
        Route::patch('/{id}/archive', [ProjetController::class, 'makeArchive']);
        Route::patch('/{id}/restoreArchive', [ProjetController::class, 'restoreArchive']);

        Route::patch('/{id}/linkInvitation', [ProjetController::class, 'linkInvitation']);

        Route::get('/{id}/getApprListProjet', [ProjetController::class, 'getApprListProjet']);
        Route::get('/{id}/getFormProject', [ProjetController::class, 'getFormProject']);
        Route::get('/{id}/getSessionProject', [ProjetController::class, 'getSessionProject']);

        Route::get('/getEtpClient/{id}/{idCfp_inter}', [ProjetController::class, 'getEtpProjectInter']);
    });

    //Mes clients
    Route::prefix('cfp/invites/etp')->group(function () {
        Route::get('/list/{idTypeEtp}', [CfpInviteEtp::class, 'index'])->name('cfp.invites.etp');
        Route::get('/search/{name}/name', [CfpInviteEtp::class, 'searchName']);
        Route::get('/getAllEtps', [CfpInviteEtp::class, 'getAllEtps']);
        Route::get('/getAllFrais', [CfpInviteEtp::class, 'getAllFrais']);
        Route::get('/{idEtp}/edit', [CfpInviteEtp::class, 'edit']);
        Route::patch('/{idEtp}/update', [CfpInviteEtp::class, 'update']);
        Route::post('/{idEtp}/updateLogo', [CfpInviteEtp::class, 'updateLogo']);
        Route::delete('/{id}', [CfpInviteEtp::class, 'destroy']);

        Route::get('/listVille_coded', [SalleEtpController::class, 'getVillesByPostalCode']);
    });

    // Reservations
    Route::prefix('/cfp/reservation')->group(function () {
        Route::get('/', [ProjetController::class, 'reservation'])->name('cfp.reservation');
        Route::get('/list', [ProjetController::class, 'reservationList'])->name('reservations');
        Route::get('/get/{idProjet}', [ProjetController::class, 'reservationProject'])->name('cfp.reservation.project');
        Route::put('/update/{id}/{type}', [ProjetController::class, 'reservationValidation']);
        Route::get('/filter', [ProjetController::class, 'reservationFilter'])->name('filter.reservation');
    });

    Route::prefix('/cfp/rsv')->group(function () {
        Route::get('/{id}', [ReservationController::class, 'index'])->name('cfp.rsc.index');
    });

    // Apprenants
    Route::prefix('cfp/apprenants')->group(function () {
        Route::get('/getEtps', [ApprenantController::class, 'getEtps'])->name('cfp.apprenants.getEtps');
        Route::get('/', [ApprenantController::class, 'index'])->name('cfp.apprenants.index');
        Route::post('/', [ApprenantController::class, 'addEmp'])->name('cfp.apprenants.addEmp');
        Route::get('/getApprenants', [ApprenantController::class, 'getApprenants'])->name('cfp.apprenants.getApprenants');
        Route::get('/{idApprenant}', [ApprenantController::class, 'edit'])->name('cfp.apprenants.edit');
        Route::patch('/{idApprenant}', [ApprenantController::class, 'update']);
        Route::get('/search/{name}/name', [ApprenantController::class, 'searchName'])->name('cfp.apprenants.search.name');
        Route::get('/search/getEtpFilter', [ApprenantController::class, 'getEtpFilter'])->name('cfp.apprenants.search.getEtpFilter');
        Route::get('/search/{idEtp}/getEmpFiltered', [ApprenantController::class, 'getEmpFiltered'])->name('cfp.apprenants.search.getEmpFiltered');
        Route::post('/{idApprenant}/updatePhoto', [ApprenantController::class, 'updateImageAppr'])->name('cfp.apprenants.updateImageAppr');
        Route::get('/filter/getDropdownItem', [ApprenantController::class, 'getDropdownItem'])->name('cfp.apprenants.filter.getDropdownItem');
        Route::get('/filter/items', [ApprenantController::class, 'filterItems']);
        Route::get('/filter/item', [ApprenantController::class, 'filterItem']);
        Route::get('/getEtpsExcel', [ApprenantController::class, 'getEtps'])->name('cfp.apprenants.getEtpsExcel');
        Route::post('/addEmpExcel', [ApprenantController::class, 'addEmpExcel'])->name('cfp.apprenants.addEmpExcel');
        Route::delete('/{id}', [ApprenantController::class, 'destroy'])->name('cfp.apprenants.destroy');
    });

    // Formateurs
    Route::prefix('cfp/forms')->group(function () {
        Route::get('/', [FormateurController::class, 'index'])->name('cfp.forms.index');
        Route::post('/', [FormateurController::class, 'sendInvitation'])->name('cfp.forms.store');
        Route::get('/getAllForms', [FormateurController::class, 'getAllForms']);
        Route::get('/listForm', [FormateurController::class, 'listForm'])->name('list.formateur');
        Route::get('/{idFormateur}/edit', [FormateurController::class, 'edit']);
        Route::patch('/{idFormateur}/update', [FormateurController::class, 'update']);
        Route::post('/{idFormateur}/updatePhotoform', [FormateurController::class, 'updateImageForm'])->name('cfp.formateurs.updateImageForm');
        Route::delete('/{id}', [FormateurController::class, 'hardDelete'])->name('cfp.forms.hardDelete');
    });

    // Gestion de dossier pour les projets
    Route::prefix('cfp/dossier')->group(function () {
        Route::get('/', [DossierController::class, 'new'])->name('cfp.dossier');
        Route::get('/showAllDossier', [DossierController::class, 'getAllDossier'])->name('dossier.showAll');
        Route::post('/ajouter', [DossierController::class, 'store'])->name('dossier.store');
        Route::get('/show', [DossierController::class, 'showByIdCfp'])->name('dossier.show');
        Route::post('/update/{idDossier}', [DossierController::class, 'edit'])->name('dossier.update');
        Route::post('/delete/{idDossier}', [DossierController::class, 'destroy'])->name('dossier.destroy');
        Route::get('/getDossierDetail/{idDossier}', [DossierController::class, 'getDossierDetail'])->name('dossier.getDossierDetail');

        Route::get('/document/liste/{idDossier}', [DossierController::class, 'getFichier'])->name('dossier.liste.fichier');
        Route::post('/document/ajouter/{idDossier}/{idProjet}', [DossierController::class, 'ajoutProjetInFolder'])->name('dossier.ajouter.fichier.dossier');
        Route::get('/document/show/{idDossier}', [DossierController::class, 'showByDossier'])->name('dossier.show.fichier');
        Route::post('/document/edit/{idDocument}', [DossierController::class, 'editDocument'])->name('dossier.editDocument');
        Route::post('/document/delete/{idDocument}', [DossierController::class, 'destroyDocument'])->name('dossier.destroyDocument');
        Route::post('/document/supprimer/{id}/{idProjet}', [DossierController::class, 'supprimeProjetInFolder'])->name('dossier.supprimer.fichier.dossier');
        Route::post('/document/upload/{idDossier}', [DossierController::class, 'uploadFichier'])->name('dossier.uploadFichier');
        Route::get('/document/projets/{idProjet}', [DossierController::class, 'getDocumentProjet'])->name('dossier.getDocumentProjet');
        Route::get('/document/section/', [DossierController::class, 'getSectionDocument'])->name('dossier.getDocumentSectionDocument');
        Route::get('/document/type/{idSectionDocument}', [DossierController::class, 'getTypeDocument'])->name('dossier.getTypeDocument');

        Route::get('/nombreDossier/{idDossier}', [DossierController::class, 'getNombreDocument'])->name('dossier.nombreDossier');
        Route::get('/load-dossiers', [DossierController::class, 'loadDossier']);
        Route::get('/projets/folder/{idDossier?}', [DossierController::class, 'getProjectsFolder'])->name('dossier.getProjectsFolder');
        Route::get('/getNombreProjet/{idDossier}', [DossierController::class, 'getNombreProjet'])->name('dossier.getNombreProjet');
        Route::get('/getOneDocumentByFolder', [DossierController::class, 'getOneDocumentByFolder'])->name('dossier.getOneDocumentByFolder');
        Route::post('/move-projet', [DossierController::class, 'moveProjet'])->name('dossier.move.projet');
        Route::get('/note/{idDossier}', [DossierController::class, 'getNote'])->name('dossier.note');
        Route::post('/update-note/{idDossier}', [DossierController::class, 'updateNote'])->name('dossier.update.note');

        Route::get('/showSelected/{id}', [DossierController::class, 'getSelectedDossier'])->name('dossier.showSelected');
        Route::get('/invoiceIsPaid', [DossierController::class, 'projectIsPaid'])->name('dossier.projectIsPaid');
        Route::get('/folderIsPaid', [DossierController::class, 'folderIsPaid'])->name('dossier.folderIsPaid');

        // conversion des fichiers dans digital Ocean
        Route::get('/listFiles', [DossierController::class, 'listFiles']);
        Route::get('/convert', [DossierController::class, 'convertImages']);
        Route::get('/testegd', [DossierController::class, 'testegd']);
        Route::get('/updateImageNames', [DossierController::class, 'updateImageNames']); // pour le dossier momentum
        Route::get('/convertImagesRecursive', [DossierController::class, 'convertImagesRecursive']); // pour le dossier momentum
        Route::get('/updateDatabaseImageExtensions', [DossierController::class, 'updateDatabaseImageExtensions']); // pour modifier la base de données avec nom de table et la colonne
        Route::get('/updateImagePathsInDatabase', [DossierController::class, 'updateImagePathsInDatabase']); // pour modifer l'url de la table image
    });

    Route::prefix('cfp/projet/etpInter')->group(function () {
        Route::get('/getEtpAdded/{idProjet}', [ProjetInterController::class, 'getEtpAdded']);
        Route::get('/getApprenantProjetInter/{idProjet}', [ProjetInterController::class, 'getApprenantProjetInter']);
        Route::get('/getApprenantAddedInter/{idProjet}', [ProjetInterController::class, 'getApprenantAddedInter']);
        Route::delete('/{idProjet}/{idEtp}', [ProjetInterController::class, 'removeEtpInter']);
        Route::post('/{idProjet}/{idApprenant}/{idEtp}', [ProjetInterController::class, 'addApprenantInter']);
        Route::delete('/{idProjet}/{idApprenant}/{idEtp}', [ProjetInterController::class, 'removeApprsEtp']);
    });

    // Apprenant_project
    Route::prefix('cfp/projet/apprenants')->group(function () {
        Route::get('/{idProjet}', [ApprenantController::class, 'projetIndex'])->name('cfp.projet.apprenants.index');
        Route::get('/getApprenantProjets/{idEtp}', [ApprenantController::class, 'getApprenantProjets']);
        Route::get('/getApprenantAdded/{idProjet}', [ApprenantController::class, 'getApprenantAdded']);
        Route::post('/{idProjet}/{idApprenant}', [ApprenantController::class, 'addApprenant']);
        Route::delete('/{idProjet}/{idApprenant}', [ApprenantController::class, 'removeApprenant']);
        Route::get('/getApprAddedInter/{idProjet}', [ApprenantController::class, 'getApprAddedInter']);
        Route::get('/checkPresences/{idProjet}', [ApprenantController::class, 'getPresencesBatch']);
        // Route::get('/checkPresence/{idProjet}/{idEmploye}', [ApprenantController::class, 'getPresenceUnique']);
    });

    Route::prefix('cfp/projet/evaluation')->group(function () {
        Route::post('/chaud', [EvaluationController::class, 'store'])->name('cfp.evaluation');
        Route::patch('/editEval', [EvaluationController::class, 'editEval'])->name('cfp.editEvaluation');
        Route::get('/checkEval/{idProjet}/{idEmploye}', [EvaluationController::class, 'checkEval']);
    });

    // cfp.salles
    Route::prefix('cfp/salles')->group(function () {
        Route::get('/list', [SalleCfpController::class, 'list']);
        Route::get('/loadVille', [SalleCfpController::class, 'loadVille'])->name('cfp.salles.loadVille');
        Route::get('/getAllSalle/{idEtp}', [SalleCfpController::class, 'getAllSalle'])->name('cfp.salles.getAllSalle');
        Route::get('/{idSalle}/edit', [SalleCfpController::class, 'edit']);
        Route::post('/{idSalle}/update', [SalleCfpController::class, 'update']);

        Route::get('/getSalleDetails/{idLieu}/{vi_code_postal}', [SalleCfpController::class, 'getSalleDetails']);
    });

    // Salles
    Route::get('cfp/salles', [SalleController::class, 'index'])->name('cfp.salles.index');
    Route::post('cfp/salles', [SalleController::class, 'store'])->name('cfp.salles.store');
    Route::delete('cfp/salles/{id}', [SalleController::class, 'destroy'])->name('cfp.salles.destroy');
    Route::get('cfp/getSalleDetails', [SalleController::class, 'getSalleDetails']);
    Route::post('cfp/updateSalleLieu', [SalleController::class, 'updateSalleLieu'])->name('updateAllLieuxSalles');

    // cfp.seances
    Route::prefix('cfp/seances')->group(function () {
        Route::post('/', [SeanceController::class, 'store']);
        Route::get('/{idProjet}/getAllSeances', [SeanceController::class, 'getAllSeances']);
        Route::get('/{idProjet}/getInfoSeances', [SeanceController::class, 'getInfoSeances']);
        Route::get('/{idProjet}/getSeanceAndTotalTime', [SeanceController::class, 'getSeanceAndTotalTime']); // <===== Récupère le nombre de séance et sa durée en heure(TOTAL)
        Route::patch('/{idSeance}/update', [SeanceController::class, 'update']);
        Route::patch('/idCalendarLastSession/updateId', [SeanceController::class, 'updateIdCalendarLastSession']);
        Route::patch('/idListCalendarSession/updateIDs', [SeanceController::class, 'updateIdListCalendarSession']);

        Route::delete('/{idSeance}/delete', [SeanceController::class, 'destroy']);
        Route::get('/getLastFieldSeances', [SeanceController::class, 'getLastFieldSeances']);         // <===== Récupère le dernier élément de la table seances...
        Route::get('/getLastFieldVueSeances', [SeanceController::class, 'getLastFieldVueSeances']);   // <===== Récupère le dernier élément de la vue seances...
        Route::get('{idSeance}/getFieldVueSeanceOfId', [SeanceController::class, 'getFieldVueSeanceOfId']); // <===== Récupère le dernier élément de la vue seances en fonction de l'idSeance
        Route::post('/sendInvitationCalendar', [SeanceController::class, 'sendInvitationCalendar'])->name('send.invitation.calendar');
    });

    //cfp.profil
    Route::prefix('/cfp/profils')->group(function () {
        Route::get('/{idCustomer}/index', [ProfilController::class, 'indexCfp'])->name('cfp.profils.index');
        Route::patch('/{idCustomer}/update', [ProfilController::class, 'update']);
        Route::post('/{idCustomer}/updateLogo', [ProfilController::class, 'updateLogo']);
    });

    //cfp.security
    Route::prefix('/cfp/security')->group(function () {
        Route::get('/', [SecurityController::class, 'index'])->name('cfp.security.index');
        Route::post('change-password', [SecurityController::class, 'changePassword'])->name('passwordUpdateCfp');
    });

    Route::prefix('/cfp/gallery')->group(function () {
        Route::post('/{idProjet}/addImage', [GalleryController::class, 'addImageGallery'])->name('cfp.gallery.addImage');
        Route::get('/', [GalleryController::class, 'getAllGallery'])->name('cfp.gallery.folder');
        Route::get('/folder', [GalleryController::class, 'getAllFolder']);
        Route::get('/folderFilter', [GalleryController::class, 'getAllFolderOrder']);
        Route::get('/getImage', [GalleryController::class, 'getGalleryByFolder']);
        Route::get('/image', [GalleryController::class, 'allImage']);
    });

    // Calendrier CFP
    Route::prefix('/cfp/annuaire')->group(function () {
        Route::get('/', [CfpAnnuaireController::class, 'index'])->name('cfp.annuaire.index');
    });


    // Particulier
    Route::prefix('particulier')->group(function () {
        Route::get('/', [ParticulierController::class, 'index'])->name('cfp.particulier.index');
        Route::post('/', [ParticulierController::class, 'store']);
        Route::get('/{idParticulier}', [ParticulierController::class, 'edit'])->name('particulier.edit');
        Route::patch('/{idParticulier}', [ParticulierController::class, 'update']);
        Route::delete('/destroy/{id}', [ParticulierController::class, 'destroy'])->name('particulier.destroy');
        Route::post('/{idParticulier}/updatePhoto', [ParticulierController::class, 'updatePhoto']);
    });

    // EmployeCfps
    Route::get('employeCfps/{empCfpId}/editPhoto', [EmployecfpController::class, 'editPhoto'])->name('employeCfps.editPhoto');
    Route::patch('employeCfps/{empCfpId}/photo', [EmployecfpController::class, 'updatePhoto'])->name('employeCfps.updatePhoto');
    Route::patch('employeCfps/{idEmploye}/activate', [EmployecfpController::class, 'activate'])->name('employeCfps.activate');
    Route::patch('employeCfps/{idEmploye}/disableEmp', [EmployecfpController::class, 'disableEmp'])->name('employeCfps.disableEmp');
    Route::get('employeCfps/{idEmploye}/getReferent', [ModuleController::class, 'getReferent']);

    // AbnCfp
    Route::prefix('cfp/abonnement')->name('cfp.abonnement.')->controller(AbnCfpController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/recap/{id}', 'recapAbn')->name('recap');
        Route::get('/forfait', 'showSubscriptions')->name('forfait');
        Route::post('/{plan}/subscribe', 'subscribe')->name('subscribe');
    });

    // AgendaCfps
    Route::get('agendaCfps', [AgendaCfpController::class, 'index'])->name('agenda.index');
    Route::get('agendaCfps/{year}', [AgendaCfpController::class, 'setStatut']);
    Route::get('agendaCfps/getEvent', [AgendaCfpController::class, 'getEvent']);
    Route::get('agendaCfps/countSeance/{month}/{year}', [AgendaCfpController::class, 'countSeance']);
    Route::get('agendaCfps/listProjetForms', [AgendaCfpController::class, 'listProjetForms']);
    Route::get('agendaMonth', [AgendaCfpController::class, 'monthAgenda']);
    Route::get('agenda/addSession', [AgendaCfpController::class, 'add'])->name('agenda.session.add');

    Route::prefix('cfp/agendas')->group(function () {
        Route::get('/', [AgendaCfpController::class, 'index'])->name('cfp.agendas.index');
        Route::get('/getEvents', [AgendaCfpController::class, 'getEvents']);
        Route::get('/getEventsGroupBy', [AgendaCfpController::class, 'getEventsGroupBy']);
        Route::get('/events_resources_agenda', [AgendaCfpController::class, 'getEventResources']);
        Route::patch('/events_updateForm_agenda', [AgendaCfpController::class, 'updateEventForms']);
    });

    // ProjetInter
    Route::post('projetInters', [ProjetInterController::class, 'store'])->name('projets.cfp.inter.store');

    // ApprenantExcel
    Route::get('projets/{idProjet}/detailApprExcel', [ProjetController::class, 'detailApprCfpExcel'])->name('projets.detailApprCfpExcel');

    // EvaluationChaud
    Route::get('evaluation/cfp/chaud/{idProjet}/{idEmploye}', [EvaluationController::class, 'evalCfp']);
    Route::get('evaluation/cfp/chaud/{idProjet}/{idEmploye}/pdf', [EvaluationController::class, 'pdfForm']);

    // EvaluationFroid
    Route::get('evaluation/cfp/froid/{idProjet}/{idEmploye}', [EvaluationController::class, 'evalFroidCfp']);
    Route::get('evaluation/cfp/froid/{idProjet}/{idEmploye}/pdf', [EvaluationController::class, 'pdfFormFroid']);

    //Evaluation apprenant
    Route::post('/evaluation/aprrenant', [EvaluationController::class, 'save'])->name('evaluation.apprenant');
    Route::get('/evaluation/aprrenant/{idEmploye}/{idProjet}', [EvaluationController::class, 'get']);

    // Ressources materiels
    Route::get('ressources/cfp', [RessourceController::class, 'addRessource'])->name("ressources.cfp.index");
    Route::post('materiel/cfps', [RessourceController::class, 'storeMateriel'])->name("materiel.cfps.store");
    Route::get('materiel/cfp/{idMateriel}', [RessourceController::class, 'editMateriel']);
    Route::patch('materiel/cfp/{idMateriel}', [RessourceController::class, 'updateMateriel'])->name("materiel.cfps.update");
    Route::delete('materiel/cfp/{idMateriel}', [RessourceController::class, 'deleteMateriel'])->name("materiel.cfp.destroy");

    Route::post('ressources/cfp/session', [RessourceController::class, 'store'])->name("ressources.cfp.session.store");
    Route::get('ressource/dashboard/cfp', [RessourceController::class, 'dashboardRessource'])->name('ressource.dashboard');

    // Facture
    Route::prefix('cfp/factures')->group(function () {
        Route::get('/', [FactureController::class, 'index'])->name('cfp.factures.index');
        Route::get('/create', [FactureController::class, 'create'])->name('cfp.factures.create');
        Route::post('/', [FactureController::class, 'store'])->name('cfp.factures.store');
        Route::get('/{id}', [FactureController::class, 'show'])->name('cfp.factures.show');
        Route::get('/{id}/edit', [FactureController::class, 'edit'])->name('cfp.factures.edit');
        Route::put('/{idInvoice}', [FactureController::class, 'update'])->name('cfp.factures.update');
        Route::post('/{id}/approve', [FactureController::class, 'approve'])->name('cfp.factures.approve');
        Route::get('/allEtps', [FactureController::class, 'getAllEtps']);
        Route::get('/export/{id}', [FactureController::class, 'exportInvoicePdf'])->name('exportInvoice');
        Route::get('/preview1', [FactureController::class, 'preview1'])->name('preview1');
        Route::post('/{id}/cancel', [FactureController::class, 'cancel'])->name('cfp.factures.cancel');
        Route::post('/send-invoice-email/{id}', [FactureController::class, 'sendInvoiceEmail'])->name('cfp.factures.sendEmail');
        Route::get('/{id}/destroy', [FactureController::class, 'destroy'])->name('cfp.factures.destroy');
        Route::post('/{id}/restore', [FactureController::class, 'restore'])->name('cfp.factures.restore');
        Route::get('/tresor', [FactureController::class, 'getTresor'])->name('cfp.factures.tresor');
        Route::get('/getEvents', [FactureController::class, 'getEvents']);
        Route::get('/projects/{clientId}', [FactureController::class, 'getProjectsByClient']);
        Route::get('/{invoiceId}', [FactureController::class, 'view'])->name('cfp.factures.view');
        Route::get('/dossiers/{clientId}', [FactureController::class, 'getDossierByClient']);
        Route::get('/projets/{idDossier}', [FactureController::class, 'getProjetByDossier']);
    });

    // facture payments
    Route::post('/{id}/payment/store', [InvoicePaymentController::class, 'store'])->name('invoice.payment.store');
    Route::put('/payments/{payment}', [InvoicePaymentController::class, 'update']);
    Route::delete('/payments/{payment}', [InvoicePaymentController::class, 'destroy']);


    //Analytic
    Route::get('AnalysticCfp/index', [AnalyticController::class, 'indexCfp'])->name('AnalyticCfp');

    Route::prefix('cfp/emargement')->group(function () {
        Route::post('/', [EmargementController::class, 'store'])->name('emargements.cfp.store');
        Route::patch('/update/{idProjet}/{isPresent}', [EmargementController::class, 'update'])->name('emargements.update');
        Route::get('/{idProjet}', [EmargementController::class, 'edit'])->name('emargements.edit');
    });

    //customer drawer
    Route::prefix('/cfp')->group(function () {
        Route::get('/etp-drawer/{idEtp}', [ShowDrawerController::class, 'showEtpDrawer'])->name('cfp.etp-drawer.index');
        Route::get('/form-drawer/{idFormateur}', [ShowDrawerController::class, 'showFormDrawer'])->name('cfp.form-drawer.index');
        Route::get('/session-drawer/{idProjet}', [ShowDrawerController::class, 'showSessionDrawer'])->name('cfp.session-drawer.index');
        Route::get('/document-drawer/{idProjet}', [ShowDrawerController::class, 'showDocumentDrawer'])->name('cfp.document-drawer.index');
        Route::get('/dossier-drawer/{idProjet}', [ShowDrawerController::class, 'showDossierDrawer'])->name('cfp.dossier-drawer.index');
        Route::get('/apprenant-drawer/{idProjet}', [ShowDrawerController::class, 'showApprenantDrawer'])->name('cfp.apprenant-drawer.index');
        Route::get('/etp-drawers/apprenant/{id}', [ShowDrawerController::class, 'showApprenantWithProjectCfp']);
        Route::get('/planreperage-drawer/{idProjet}', [ShowDrawerController::class, 'showPLanDeReperageDrawer'])->name('cfp.dossier-drawer.plan');
    });

    //restauration
    Route::post('/cfp/projets/addRestauration', [RestaurationController::class, 'store'])->name('restauration.store');
    Route::post('/cfp/projets/deleteRestauration/{idProjet}/{idRestauration}', [RestaurationController::class, 'deleteRestauration'])->name('restauration.delete');
    Route::get('/cfp/projets/getRestauration/{idProjet}', [RestaurationController::class, 'getRestauration'])->name('restauration.get');

    // FACTURE ACOMPTE
    Route::get('factureAcompte', [FactureAcompteController::class, 'index'])->name('factureAcompte.cfp.index');
    Route::get('factureAcompte/creer', [FactureAcompteController::class, 'creerIndex'])->name('factureAcompte.cfp.creerIndex');
    Route::get('factureAcompte/approuver', [FactureAcompteController::class, 'approuveIndex'])->name('factureAcompte.cfp.approuveIndex');

    // FACTURE PROFORMA
    Route::prefix('cfp/factureProfo')->group(function () {
        Route::get('/', [FactureProfoController::class, 'index'])->name('cfp.factureProfo.index');
        Route::get('/create', [FactureProfoController::class, 'create'])->name('cfp.factureProfo.create');
        Route::get('/{id}/edit', [FactureProfoController::class, 'edit'])->name('cfp.factureProfo.edit');
        Route::post('/{id}/convertir', [FactureController::class, 'convertir'])->name('cfp.factures.convertir');
    });

    // Sous-traitant project
    Route::prefix('/cfp/projects/subContractor')->group(function () {
        Route::get('/getAll', [SubContractorController::class, 'getAll']);
        Route::post('/{idProjet}/{idSubContractor}/assign', [SubContractorController::class, 'assign']);
        Route::get('/{idProjet}/getAssign', [SubContractorController::class, 'getAssign']);
        Route::delete('/{idSubContractor}/removeAssign', [SubContractorController::class, 'removeAssign']);
        Route::get('/', [SubContractorController::class, 'getSubContractorList'])->name('cfp.subContractor');
    });

    // Sous-traitant
    Route::prefix('cfp/invites/subcontractor')->group(function () {
        Route::get('/getNif/{nif}', [SubContractorController::class, 'getNif']);
        Route::get('/cfp/{idCfp}', [SubContractorController::class, 'getCfpDetail']);
        Route::post('/', [SubContractorController::class, 'sendInvitation']);
    });

    Route::get('/notifications/read/{id}', [NotificationController::class, 'markNotificationAsRead'])->name('notifications.read');

    // Conflits 
    Route::get('/cfp/conflits', [ConflitsController::class, 'index']);
    Route::get('/cfp/conflitsLieu', [ConflitsController::class, 'conflitsLieu'])->name('conflitsLieu');
    Route::get('/cfp/conflitsFormateur', [ConflitsController::class, 'conflitsFormateur'])->name('conflitsFormateur');
    Route::get('/cfp/ignoredConflitLieu/{id}/{idProjet}', [ConflitsController::class, 'ignoredConflitLieu'])->name('ignoredConflitLieu');
    Route::get('/cfp/ignoredConflitFormateur/{id}/{idProjet}', [ConflitsController::class, 'ignoredConflitFormateur'])->name('ignoredConflitFormateur');

    // Agence
    Route::get('cfp/agences', [AgenceController::class, 'index'])->name('cfp.agences.index');

    // Lieux
    Route::get('cfp/lieux', [LieuxController::class, 'index'])->name('cfp.lieux.index');
    Route::get('cfp/lieux/{id}', [LieuxController::class, 'search'])->name('cfp.lieux.search');
    Route::get('cfp/lieuxsearch', [LieuxController::class, 'searchNoId']);


    Route::prefix('cfp/bankAcount')->group(function () {
        Route::get('/', [BankAccountController::class, 'index'])->name('cfp.bankAcount.index');
        Route::post('/', [BankAccountController::class, 'store'])->name('cfp.bankAcount.store');
        Route::patch('/{id}', [BankAccountController::class, 'update'])->name('cfp.bankAcount.update');
        Route::delete('/{id}', [BankAccountController::class, 'destroy'])->name('cfp.bankAcount.destroy');
    });

    Route::prefix('cfp/prospection')->group(function () {
        Route::get('/', [ProspectionController::class, 'index'])->name('cfp.prospection.index');
        Route::get('/etp_prospect', [ProspectionController::class, 'getProspect']);
        Route::get('/{id}', [ProspectionController::class, 'show'])->name('cfp.prospection.show');
        Route::get('/getEtp/{id}', [ProspectionController::class, 'getEtpSelected']);
        Route::get('/getEvents', [ProspectionController::class, 'getEvents']);
        Route::post('/', [ProspectionController::class, 'storeOrUpdate'])->name('cfp.prospection.store');
        Route::post('/{id}', [ProspectionController::class, 'manageOpportunities']);
        Route::patch('/etp_prospect/{id}', [ProspectionController::class, 'updateProspect']);
        Route::patch('/etp/assign/{id}/{idEtp?}', [ProspectionController::class, 'etpAssignOp']);
        Route::patch('/{id}/updateId', [ProspectionController::class, 'updateIdCalendarOpportunity']);
        Route::delete('/{id}', [ProspectionController::class, 'delete']);
        Route::post('/save-lists-order', [ProspectionController::class, 'saveOrder']);
    });

    // Lieux
    Route::post('cfp/lieux', [LieuxController::class, 'store'])->name('cfp.lieux.store');
    Route::delete('cfp/lieux_delete/{id}', [LieuxController::class, 'deleteLieu']);
    Route::get('cfp/lieux/getEtps', [LieuxController::class, 'getAllEtps']);

    // Villes
    Route::get('cfp/villes/{idVille}', [LieuxController::class, 'allVilleCodeds']);
    Route::get('cfp/quartier', [LieuxController::class, 'propositionQuartier']);
    Route::get('cfp/searchJson', [LieuxController::class, 'searchJson']);

    Route::prefix('cfp/peda')->group(function () {
        Route::get('/', [PedaController::class, 'index'])->name('cfp.peda.index');
        Route::get('/envoyer-cours/{idProjet}', [PedaController::class, 'sendCours'])->name('envoyer.cours');
        Route::get('/envoyer-rapports/{idProjet}', [PedaController::class, 'sendRapport'])->name('envoyer.rapports');
        Route::get('/envoyer-attestations/{idProjet}', [PedaController::class, 'sendAttestation'])->name('envoyer.attestations');

        Route::get('/suivi-envois/{idProjet}', [PedaController::class, 'getSuivie']);
    });


    // test services
    Route::controller(InvitationController::class)->prefix('cfp/invite-etp')->group(function () {
        Route::get('/{name}/name', 'getCustomerName');
        Route::get('/{id}/{typeCustomer}', 'getCustomer');
        Route::post('/store/{typeCustomer}/{idCustomer}', 'inviteCustomer');
        Route::post('/new-customer', 'inviteNewCustomer');
    });

    Route::prefix('cfp/attestation')->group(function () {
        Route::get('/', [AttestationController::class, 'index'])->name('cfp.attestation.index');
        Route::post('/', [AttestationController::class, 'store'])->name('cfp.attestation.store');
        Route::get('/{id}/edit', [AttestationController::class, 'edit'])->name('cfp.attestation.edit');
        Route::post('/{id}/update', [AttestationController::class, 'update'])->name('cfp.attestation.update');
        Route::post('/{id}/delete', [AttestationController::class, 'destroy'])->name('cfp.attestation.destroy');
        Route::get('/getProjet', [AttestationController::class, 'getProjet'])->name('cfp.attestation.getProjet');
        Route::get('/{id}/getApprenant', [AttestationController::class, 'getApprenant'])->name('cfp.attestation.getApprenant');
    });

    Route::prefix('cfp/objectif')->group(function () {
        Route::get('/', [FinancialGoalController::class, 'index'])->name('objectif.index');
        Route::post('/save', [FinancialGoalController::class, 'save']);
        Route::get('/getByYear', [FinancialGoalController::class, 'getGoalByYear']);
        Route::get('/getTotalYear', [FinancialGoalController::class, 'getTotalYear']);
        Route::get('/getByMonth', [FinancialGoalController::class, 'goalByMOnth']);
    });

    // Evaluation-froid
    Route::controller(EvaluationController::class)->prefix('cfp/evaluations/froids')->group(function () {
        Route::get('/', 'indexFroid')->name('cfp.evaluations.froids.index');
        Route::post('/{idProjet}', 'sendEvaluation')->name('cfp.evaluations.froids.send');
        Route::get('/apprenants/{idProjet}', 'getApprenants');
        Route::get('/detail/{idProjet}', 'getApprenantByProjectResult');
        Route::get('/result/{idProjet}/{idEmploye}', 'apprenantEvaluationResult')->name('cfp.evaluations.result.apprenant.pdf');
    });

    //mobile money
    Route::prefix('cfp/mobilemoneyAcount')->group(function () {
        // Route::get('/', [MobileMoneyAccountController::class, 'index'])->name('cfp.mobilemoneyAcount.index');
        Route::post('/', [MobileMoneyAccountController::class, 'store'])->name('cfp.mobilemoneyAcount.store');
        Route::patch('/{id}', [MobileMoneyAccountController::class, 'update'])->name('cfp.mobilemoneyAcount.update');
        Route::delete('/{id}', [MobileMoneyAccountController::class, 'destroy'])->name('cfp.mobilemoneyAcount.destroy');
    });

    Route::prefix('cfp/badge')->group(function () {
        Route::get('/', [BadgeController::class, 'index'])->name('cfp.badge.index');
        Route::post('/{id}', [BadgeController::class, 'store'])->name('cfp.badge.store');
        Route::get('/{id}/edit', [BadgeController::class, 'edit'])->name('cfp.badge.edit');
        Route::post('/{id}/update', [BadgeController::class, 'update'])->name('cfp.badge.update');
        Route::post('/{id}/delete', [BadgeController::class, 'destroy'])->name('cfp.badge.destroy');
        Route::get('/getCatalogue', [BadgeController::class, 'getCatalogue'])->name('cfp.badge.getCatalogue');
        Route::get('/getApprenant/{idProjet}', [BadgeController::class, 'getApprenant'])->name('cfp.badge.getApprenant');
    });

    Route::prefix('espace')->group(function () {
        Route::get('/profil', [MarketPlaceController::class, 'espaceClientProfil'])->name('espaceClient.profil');
        Route::get('/reglement', [MarketPlaceController::class, 'espaceClientReglement'])->name('espaceClient.reglement');
    });

    Route::post('/traits/add', [CfpProfilController::class, 'addTrait'])->name('traits.add');
    Route::post('/traits/remove', [CfpProfilController::class, 'removeTrait'])->name('traits.remove');

    Route::post('/reasons/add', [CfpProfilController::class, 'addReason'])->name('reasons.add');
    Route::post('/reasons/remove', [CfpProfilController::class, 'removeReason'])->name('reasons.remove');

    Route::prefix('sections')->group(function () {
        Route::post('/reglement', [SectionController::class, 'storeReglement'])->name('sections.storeReglement');
        Route::post('/accueil', [SectionController::class, 'storeAccueil'])->name('sections.storeAccueil');
        Route::post('/conditions', [SectionController::class, 'storeConditions'])->name('sections.storeConditions');
        Route::post('/acces', [SectionController::class, 'storeAcces'])->name('sections.storeAcces');
        Route::post('/accompagnement', [SectionController::class, 'storeAccompagnement'])->name('sections.storeAccompagnement');
        Route::post('/organigramme', [SectionController::class, 'storeOrganigramme'])->name('sections.storeOrganigramme');
        Route::post('/picture', [SectionController::class, 'storePictures'])->name('sections.storePictures');
        Route::post('/picture/remove/{id}', [SectionController::class, 'destroy'])->name('sections.destroy');
    });
});
