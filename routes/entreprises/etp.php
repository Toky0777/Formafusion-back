<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EtpInviteCfp;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AbnEtpController;
use App\Http\Controllers\AbnGrpController;
use App\Http\Controllers\ModuleController;
use App\Http\Controllers\ProjetController;
use App\Http\Controllers\EmployeController;
use App\Http\Controllers\SalleEtpController;
use App\Http\Controllers\AgendaEtpController;
use App\Http\Controllers\CatalogueController;
use App\Http\Controllers\AgenceController;
use App\Http\Controllers\EmployeEtpController;
use App\Http\Controllers\EvaluationController;
use App\Http\Controllers\ProjetInterController;
use App\Http\Controllers\SeanceIntraController;
use App\Http\Controllers\ApprenantEtpController;
use App\Http\Controllers\ModuleInterneController;
use App\Http\Controllers\ProjetInterneController;
use App\Http\Controllers\SeanceInterneController;
use App\Http\Controllers\FormateurInterneController;
use App\Http\Controllers\ProgrammeInterneController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ModuleRessourceInterneController;
use App\Http\Controllers\EtpGroupeController;
use App\Http\Controllers\ReportingControllerEtp;
use App\Http\Controllers\SecurityController;
use App\Http\Controllers\ShowDrawerController;
use App\Http\Controllers\DossierControllerEtp;
use App\Http\Controllers\ProfilEtpController;
use App\Http\Controllers\LieuxController;
use App\Http\Controllers\QcmBaremeController;
use App\Http\Controllers\SalleController;
use App\Http\Controllers\BatchController;
use App\Http\Controllers\BatchLearnerController;
use App\Http\Controllers\CreditsPacksController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\QcmInvitationController;
use App\Http\Controllers\ReservationEtpController;
use App\Http\Controllers\SearchController;

Route::middleware(['auth', 'isReferent'])->group(function () {
    Route::get('home-etp', [DashboardController::class, 'dashboardEtp'])->name('home.entreprise');
    Route::get('homeEtp/customer', [HomeController::class, 'getIdCustomer']);
    Route::get('/confidentialite', [HomeController::class, 'confidentialite'])->name('confidentialite');
    Route::get('/condition', [HomeController::class, 'condition'])->name('condition');
    // Projets
    Route::prefix('/etp/projets')->group(function () {
        Route::get('/', [ProjetInterneController::class, 'index'])->name('etp.projets.index');
        Route::post('/', [ProjetInterneController::class, 'store'])->name('etp.projets.store');
        Route::get('/{idProjet}/detail', [ProjetInterneController::class, 'show'])->name('etp.projets.show');
        Route::get('/formateur/{id}/mini-cv', [ProjetInterneController::class, 'getMiniCV']);
        Route::post('/{idProjet}/{idFormateur}/form/assign', [ProjetInterneController::class, 'formAssign']);
        Route::get('/{idProjet}/getFormInterneAdded', [ProjetInterneController::class, 'getFormInterneAdded']);

        Route::get('/{idProjet}/getFormAdded', [ProjetInterneController::class, 'getFormAdded']);
        Route::delete('/{idProjet}/{idFormateur}/form/assign', [ProjetInterneController::class, 'formRemove']);
        Route::get('/{idProjet}/etp/assign', [ProjetInterneController::class, 'getEtpAssign']);
        Route::patch('/{idProjet}/{idEtp}/etp/assign', [ProjetInterneController::class, 'etpAssign']);
        Route::get('/{idProjet}/mainGetIdEtp', [ProjetInterneController::class, 'mainGetIdEtp']);
        Route::get('/{idProjet}/mainGetIdModule', [ProjetInterneController::class, 'mainGetIdModule']);
        Route::patch('/{idProjet}/{idModule}/module/assign', [ProjetInterneController::class, 'moduleAssign']);
        Route::patch('/{idProjet}/date/assign', [ProjetInterneController::class, 'dateAssign']);
        Route::get('/{idProjet}/details', [ProjetInterneController::class, 'detailsJson']);
        Route::get('/{idModule}/getProgrammeProject', [ProjetInterneController::class, 'getProgramme']);
        Route::get('/{idModule}/getModuleRessourceProject', [ProjetInterneController::class, 'getModuleRessourceProject']);
        Route::delete('/{idProjet}/destroy', [ProjetInterneController::class, 'destroy'])->name('etp.projets.destroy');
        Route::post('/{idProjet}/duplicate', [ProjetInterneController::class, 'duplicate'])->name('etp.projets.duplicate');
        Route::patch('/{idProjet}/update/date', [ProjetInterneController::class, 'updateDate'])->name('etp.projets.updateDate');
        Route::patch('/{idProjet}/update/module', [ProjetInterneController::class, 'updateModule'])->name('etp.projets.updateModule');
        Route::patch('/{idProjet}/{idSalle}/salle/assign', [ProjetInterneController::class, 'salleAssign']);
        Route::get('/{idProjet}/getSalleAdded', [ProjetInterneController::class, 'getSalleAdded']);
        Route::patch('/{idProjet}/cancel', [ProjetInterneController::class, 'cancel']);
        Route::patch('/{idProjet}/repport', [ProjetInterneController::class, 'repport']);
        Route::patch('/{idProjet}/updateProjet', [ProjetInterneController::class, 'updateProjet']);
        Route::get('/filter/getDropdownItem', [ProjetInterneController::class, 'getDropdownItem']);
        Route::get('/filter/items', [ProjetInterneController::class, 'filterItems']);
        Route::get('/filter/item', [ProjetInterneController::class, 'filterItem']);
        Route::patch('/{idProjet}/confirm', [ProjetInterneController::class, 'confirm']);
        Route::get('/{idProjet}/getStatutProjet', [ProjetInterneController::class, 'getStatutProjet']);
        Route::get('/{idProjet}/form/assign', [ProjetInterneController::class, 'getFormAssign'])->name('etp.projets.form.assign.index');

        Route::get('/list', [ProjetInterneController::class, 'getProjectList'])->name('etp.projets.list');
        Route::get('/{idProjet}/detail/momentum', [ProjetInterneController::class, 'showmomentum'])->name('etp.projets.showmomentum');
    });

    Route::prefix('etp/reservations')->group(function () {
        Route::get('/projet_inter', [ProjetInterneController::class, 'projectInter'])->name('project.inter');
    });

    Route::prefix('etp/rsv')->group(function () {
        Route::get('/', [ReservationEtpController::class, 'index'])->name('etp.rsc.index');
    });

    Route::get('etp/projets/{idProjet}/{isEtp}/frais', [ProjetController::class, 'fraisdetailsEtp'])->name('etp.projets.fraisdetails');
    Route::post('etp/projets/{idProjet}/{idFrais}/{isEtp}/fraisprojet/assign', [ProjetController::class, 'fraisAssign'])->name('etp.projets.fraisAssign');
    Route::post('etp/projets/update-frais', [ProjetController::class, 'updateFrais'])->name('etp.projets.updateFrais');
    Route::post('etp/projets/{idProjet}/total-frais', [ProjetController::class, 'fraisTotalEtp'])->name('etp.projets.fraisTotal');
    Route::get('etp/projets/{idProjet}/getTotalHTJSON', [ProjetController::class, 'getTotalHTJSON'])->name('etp.projets.getTotalHTJSON');
    Route::get('etp/projets/{idFraisProjet}/idProjet', [ProjetController::class, 'getIdProjetByIdFraisProjet'])->name('etp.projets.getIdProjet');
    Route::post('etp/projets/{idProjet}/{idFraisProjet}/delete-frais', [ProjetController::class, 'fraisRemove'])->name('etp.projets.deleteFrais');
    Route::get('etp/projets/fermeturefrais', [ProjetController::class, 'fermeturefrais'])->name('etp.projets.fermeturefrais');
    Route::post('etp/projets/{idProjet}/update-taxe', [ProjetController::class, 'updateTaxe']);

    // Referent_etp
    Route::prefix('etp/referents')->group(function () {
        Route::get('/', [EmployeEtpController::class, 'index'])->name('etp.referents.index');
        Route::post('/', [EmployeEtpController::class, 'store'])->name('etp.referents.store');
        Route::get('/{idEmploye}/edit', [EmployeEtpController::class, 'edit']);
        Route::patch('/{idEmploye}', [EmployeEtpController::class, 'update']);
        Route::get('/{idEmploye}/show', [EmployeEtpController::class, 'show']);
        Route::delete('/{id}', [EmployeEtpController::class, 'destroy'])->name('etp.referents.destroy');
        Route::post('/{idEmploye}/updatePhoto', [EmployeEtpController::class, 'updatePhoto']);
        Route::patch('/updatePassword/{idEmploye}', [EmployeEtpController::class, 'updatePassword']);
    });

    // dossier entreprise 
    Route::prefix('etp/dossier')->group(function () {
        Route::get('/', [DossierControllerEtp::class, 'newEtp'])->name('etp.dossier');
        Route::get('/show', [DossierControllerEtp::class, 'showByIdEtp'])->name('etp.dossier.show');
        Route::get('/getDossierDetail/{idDossier}', [DossierControllerEtp::class, 'getDossierDetailEtp'])->name('etp.dossier.getDossierDetail');
        Route::get('/document/liste/{idDossier}', [DossierControllerEtp::class, 'getFichierEtp'])->name('etp.dossier.liste.fichier');
        Route::get('/note/{idDossier}', [DossierControllerEtp::class, 'getNote'])->name('etp.dossier.note');
        Route::post('/update-note/{idDossier}', [DossierControllerEtp::class, 'updateNote'])->name('etp.dossier.update.note');
        Route::post('/document/upload/{idDossier}', [DossierControllerEtp::class, 'uploadFichier'])->name('etp.dossier.uploadFichier');
        Route::get('/document/section/', [DossierControllerEtp::class, 'getSectionDocument'])->name('etp.dossier.section');
        Route::get('/document/type/{idSectionDocument}', [DossierControllerEtp::class, 'getTypeDocument'])->name('etp.dossier.type');
        Route::post('/document/delete/{idDocument}', [DossierControllerEtp::class, 'destroyDocument'])->name('etp.dossier.destroyDocument');
    });


    // AbnEtp
    Route::prefix('etp/abonnement')->name('etp.abonnement.')->controller(AbnEtpController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/recap/{id}', 'recapAbn')->name('recap');
        Route::get('/forfait', 'showSubscriptions')->name('forfait');
        Route::post('/{plan}/subscribe', 'subscribe')->name('subscribe');
    });

    // Formateur_Interne
    Route::prefix('etp/formInternes')->group(function () {
        Route::get('/', [FormateurInterneController::class, 'index'])->name('etp.formateurInternes.index');
        Route::get('/{idFormateur}/edit', [FormateurInterneController::class, 'edit'])->name('etp.formateurInternes.edit');
        Route::get('/getAllForms', [FormateurInterneController::class, 'getAllForms']);
        Route::post('/store', [FormateurInterneController::class, 'sendInvitation'])->name('etp.formateurInternes.storep');
        Route::patch('/{idFormateur}/update', [FormateurInterneController::class, 'update']);
        Route::post('/{idFormateur}/updatePhotoform', [FormateurInterneController::class, 'updateImageForm'])->name('etp.formateurInternes.updateImageForm');
        Route::delete('formateurInternes/{id}', [FormateurInterneController::class, 'removeFormateur'])->name('formateurInternes.removeFormateur'); //<-- à Modifier
    });

    //Employés
    Route::prefix('etp/employes')->group(function () {
        Route::get('/', [EmployeController::class, 'index'])->name('etp.employes.index');
        Route::get('/getIdEtp', [EmployeController::class, 'getIdEtp'])->name('employes.idEtp');
        Route::get('/{idEmploye}', [EmployeController::class, 'edit'])->name('employes.idEtp');
        Route::post('/addEmp', [EmployeController::class, 'addEmp'])->name('etp.employes.addEmp');
        Route::patch('/{idEmploye}/update', [EmployeController::class, 'update'])->name('employes.etp.update');
        Route::post('/{idEmploye}/updatePhoto', [EmployeController::class, 'updateImageEmpl'])->name('etp.employes.updateImageEmpl');
        Route::get('/search/{name}/name', [EmployeController::class, 'searchName'])->name('etp.apprenants.search.name');
        Route::get('/search/getEmpFiltered', [EmployeController::class, 'getEmpFiltered'])->name('etp.apprenants.search.getEmpFiltered');
        Route::get('/filter/getDropdownItem', [EmployeController::class, 'getDropdownItem'])->name('etp.employes.filter.getDropdownItem');
        Route::get('/filter/items', [EmployeController::class, 'filterItems']);
        Route::get('/filter/item', [EmployeController::class, 'filterItem']);
        Route::patch('/{idEmploye}/service', [EmployeController::class, 'updateService']);
        Route::post('/addEmpExcel', [EmployeController::class, 'addEmpExcel'])->name('etp.employes.addEmpExcel');
        Route::get('/add/getEtpType', [EmployeController::class, 'getEtpType']);
        Route::get('/getEmployesEtp/{idProjet}', [EmployeController::class, 'getApprenantEtp'])->name('listEmployes.etp');
        Route::get('/getEmployes/{idProjet}', [EmployeController::class, 'getApprenantProjectInter'])->name('etp.employes.project');
        Route::get('/getNbPlace/{idProjet}', [EmployeController::class, 'getNbPlaceApprenantAdded'])->name('etp.nbPlace.reserved');
        Route::post('/{idProjet}/{idApprenant}/{idEtp}', [ProjetInterController::class, 'addApprenantInterReservation']);
        Route::delete('/{idProjet}/{idApprenant}/{idEtp}', [ProjetInterController::class, 'removeApprsEtp']);
        Route::delete('/{id}/delete', [EmployeController::class, 'deleteEmployeEtp'])->name('delete.emp_etp');
    });

    Route::prefix('etp/batch')->group(function () {
        Route::get('/', [BatchController::class, 'index']);
        Route::get('/getAll', [BatchController::class, 'getAll']);
        Route::post('/create', [BatchController::class, 'store']);
        Route::put('/update/{batch}', [BatchController::class, 'update']);
        Route::delete('/delete/{batch}', [BatchController::class, 'destroy']);
        Route::get('/{id}', [BatchController::class, 'edit']);
    });

    Route::prefix('etp/batch_learner')->group(function () {
        Route::get('/', [BatchLearnerController::class, 'index']);
        Route::get('/getAll', [BatchLearnerController::class, 'getAll']);
        Route::get('/non_participant/{id}', [BatchLearnerController::class, 'getNoParticipantLearner']);
        Route::get('/participant/{id}', [BatchLearnerController::class, 'getParticipantLearner']);
        Route::post('/create', [BatchLearnerController::class, 'store']);
        Route::put('update/{batch}', [BatchLearnerController::class, 'update']);
        Route::delete('/delete/{batch_learner}', [BatchLearnerController::class, 'destroy']);
    });

    // etp.salles
    Route::prefix('etp/salles')->group(function () {
        Route::get('/list', [SalleEtpController::class, 'list']);
        Route::get('/', [SalleEtpController::class, 'index'])->name('etp.salles.index');
        Route::get('/loadVille', [SalleEtpController::class, 'loadVille'])->name('etp.salles.loadVille');
        Route::post('/', [SalleEtpController::class, 'store'])->name('etp.salles.store');
        Route::get('/getAllSalle', [SalleEtpController::class, 'getAllSalle'])->name('etp.salles.getAllSalle');
        Route::get('/{idSalle}/edit', [SalleEtpController::class, 'edit']);
        Route::patch('/{idSalle}/update', [SalleEtpController::class, 'update']);
        Route::delete('/{idSalle}/delete', [SalleEtpController::class, 'destroy'])->name('etp.salles.delete');

        Route::get('/listVille_coded', [SalleEtpController::class, 'getVillesByPostalCode']);
    });

    Route::get('etp/getSalleDetails', [SalleController::class, 'getSalleDetails']);
    Route::post('etp/updateSalleLieu', [SalleController::class, 'updateSalleLieu'])->name('updateEtpLieuxSalles');
    Route::delete('etp/salles/{id}', [SalleController::class, 'destroy'])->name('etp.salles.destroy');
    Route::delete('etp/lieux_delete/{id}', [LieuxController::class, 'deleteLieu']);
    Route::post('etp/salles', [SalleController::class, 'store'])->name('etp.salles.store');
    Route::get('etp/villes/{idVille}', [LieuxController::class, 'allVilleCodeds']);
    Route::get('etp/quartier', [LieuxController::class, 'propositionQuartier']);



    //etp.profil
    Route::prefix('/etp/profils')->group(function () {
        Route::get('/', [ProfilEtpController::class, 'index'])->name('profil.etp.index');
        Route::patch('/{idCustomer}/update', [ProfilEtpController::class, 'update']);
        Route::post('/{idCustomer}/updateLogo', [ProfilEtpController::class, 'updateLogo']);

        Route::get('/listVille_coded', [SalleEtpController::class, 'getVillesByPostalCode']);
    });

    //etp.security
    Route::prefix('/etp/security')->group(function () {
        Route::get('/', [SecurityController::class, 'indexEtp'])->name('etp.security.index');
        Route::post('change-password', [SecurityController::class, 'changePassword'])->name('passwordUpdateEtp');
    });


    //Reporting Etp

    // --- Formation ---
    Route::prefix('etp/reporting')->group(function () {
        Route::get('/exportXl', [ReportingControllerEtp::class, 'exportXl'])->name('exportXlEtp');
        Route::get('/exportPdf', [ReportingControllerEtp::class, 'exportPdf'])->name('exportPdfEtp');
        Route::get('/formation', [ReportingControllerEtp::class, 'formation'])->name('ReportingFormationEtp');
        Route::post('/formation', [ReportingControllerEtp::class, 'filterFormation'])->name('reporting.filter.formationEtp');
    });
    // --- Apprenant ---
    Route::prefix('etp/reporting')->group(function () {
        Route::get('/exportXl/app', [ReportingControllerEtp::class, 'exportAppEtpXl'])->name('exportAppEtpXl');
        Route::get('/exportPdf/app', [ReportingControllerEtp::class, 'exportAppEtpPdf'])->name('exportAppEtpPdf');
        Route::get('/apprenant', [ReportingControllerEtp::class, 'apprenantEtp'])->name('ReportingApprenantEtp');
        Route::post('/apprenant/filter', [ReportingControllerEtp::class, 'filterApprenant'])->name('reporting.filter.apprenantEtp');
    });
    // --- Cfp ---
    Route::prefix('etp/reporting')->group(function () {
        Route::get('/exportXl/cl', [ReportingControllerEtp::class, 'exportXlCl'])->name('exportXlEtpCl');
        Route::get('/exportPdf/cl', [ReportingControllerEtp::class, 'exportPdfCl'])->name('exportPdfEtpCl');
        Route::get('/client', [ReportingControllerEtp::class, 'client'])->name('ReportingClientEtp');
        Route::post('/client/filter', [ReportingControllerEtp::class, 'filterClient'])->name('reporting.filter.ClientEtp');
    });
    // --- Cours ---
    Route::prefix('etp/reporting')->group(function () {
        Route::get('/exportXlCours', [ReportingControllerEtp::class, 'exportXlCours'])->name('exportXlEtp');
        Route::get('/exportPdfCours', [ReportingControllerEtp::class, 'exportPdfCours'])->name('exportPdfEtp');
        Route::get('/cours', [ReportingControllerEtp::class, 'cours'])->name('ReportingCoursEtp');
        Route::post('/cours/filter', [ReportingControllerEtp::class, 'filterCours'])->name('reporting.filter.coursEtp');
    });
    // --- Chiffre d'affaire ---
    Route::prefix('etp/reporting')->group(function () {
        // Route::get('/exportXlChiffre', [ReportingControllerEtp::class, 'exportXlCours'])->name('exportXlEtp');
        // Route::get('/exportPdfChiffre', [ReportingControllerEtp::class, 'exportPdfCours'])->name('exportPdfEtp');
        Route::get('/chiffre', [ReportingControllerEtp::class, 'chiffreAEtp'])->name('ReportingChiffreEtp');
    });


    // Catalogues
    Route::prefix('etp/catalogue')->group(function () {
        Route::get('/', [CatalogueController::class, 'index'])->name('catalogue.index');
        Route::get('/result', [CatalogueController::class, 'result'])->name('marketplace.result');
    });

    //Mes centres de formations
    Route::prefix('etp/invites/cfp')->group(function () {
        Route::get('/', [EtpInviteCfp::class, 'index'])->name('etp.invites.cfp');
        Route::get('/getEtpDetail/{idEtp}', [EtpInviteCfp::class, 'getEtpDetail']);
        Route::get('/search/{name}/name', [EtpInviteCfp::class, 'searchName']);
        Route::get('/getAllEtps', [EtpInviteCfp::class, 'getAllEtps']);
        Route::get('/getAllFrais', [EtpInviteCfp::class, 'getAllFrais']);
        Route::get('/{idEtp}/edit', [EtpInviteCfp::class, 'edit']);
        Route::patch('/{idEtp}/update', [EtpInviteCfp::class, 'update']);
        Route::post('/{idEtp}/updateLogo', [EtpInviteCfp::class, 'updateLogo']);
    });

    // test services
    Route::controller(InvitationController::class)->prefix('etp/invite-cfp')->group(function () {
        Route::get('/{name}/name', 'getCustomerName');
        Route::post('/store/{typeCustomer}/{idCustomer}', 'inviteCustomer');
        Route::post('/new-customer', 'inviteNewCustomer');
    });

    //Modules ETP
    Route::prefix('etp/modules')->group(function () {
        Route::post('/', [ModuleInterneController::class, 'store'])->name('etp.modules.store');
        Route::get('/', [ModuleInterneController::class, 'index'])->name('etp.modules.index');
        Route::patch('/{idModule}/makeOnline', [ModuleInterneController::class, 'makeOnline']);
        Route::patch('/{idModule}/makeOffline', [ModuleInterneController::class, 'makeOffline']);
        Route::patch('/{idModule}/makeTrashed', [ModuleInterneController::class, 'makeTrashed']);
        Route::patch('/{idModule}/restoreModule', [ModuleInterneController::class, 'restoreModule']);
        Route::delete('/{idModule}/deleteModule', [ModuleInterneController::class, 'destroy'])->name('modules.destroy');
        Route::get('/{idModule}/edit', [ModuleInterneController::class, 'edit'])->name('modules.edit');
        Route::put('/{idModule}', [ModuleInterneController::class, 'update'])->name('etp.modules.update');
        Route::get('/{idModule}', [ModuleInterneController::class, 'show'])->name('etp.modules.show');
        Route::get('/{moduleName}/search', [ModuleInterneController::class, 'search'])->name('etp.modules.search');
        Route::patch('/{idModule}/addImage', [ModuleInterneController::class, 'updateImage'])->name('etp.modules.updateImage');
        Route::post('/{idModule}/updateImgMdl', [ModuleInterneController::class, 'updateImgMdl'])->name('etp.modules.updateImgMdl');

        // Objectifs
        Route::post('/{idModule}/objectifs', [ModuleInterneController::class, 'addObjectif']);
        Route::get('/{idModule}/objectifs', [ModuleInterneController::class, 'getObjectif']);
        Route::delete('/{idObjectif}/objectifs', [ModuleInterneController::class, 'deleteObjectif']);

        // Prestations
        Route::post('/{idModule}/prestations', [ModuleInterneController::class, 'addPrestation']);
        Route::get('/{idModule}/prestations', [ModuleInterneController::class, 'getPrestation']);
        Route::delete('/{idPrestation}/prestations', [ModuleInterneController::class, 'deletePrestation']);

        // Prerequis
        Route::post('/{idModule}/prerequis', [ModuleInterneController::class, 'addPrerequis']);
        Route::get('/{idModule}/prerequis', [ModuleInterneController::class, 'getPrerequis']);
        Route::delete('/{idPrestation}/prerequis', [ModuleInterneController::class, 'deletePrerequis']);

        // Cibles
        Route::post('/{idModule}/cibles', [ModuleInterneController::class, 'addCible']);
        Route::get('/{idModule}/cibles', [ModuleInterneController::class, 'getCible']);
        Route::delete('/{idCible}/cibles', [ModuleInterneController::class, 'deleteCible']);

        //Domaine de formation
        Route::get('/domaine/getDomainFormations', [ModuleInterneController::class, 'getDomainFormations'])->name('etp.modules.getDomainFormations');
        Route::get('/get/getAllModule', [ModuleInterneController::class, 'getAllModuleEtp']);
        Route::post('/firstModule', [ModuleInterneController::class, 'storeFirst']);
        Route::get('/get/firstModule', [ModuleInterneController::class, 'getFirstModuleInternes']);

        Route::get('/detail/{idModule}/drawer', [ModuleInterneController::class, 'detailModules']);
    });

    // Agendas
    Route::get('agendaEtps/getEvent', [AgendaEtpController::class, 'getEvent']);
    Route::get('agendaEtps/listProjetForms', [AgendaEtpController::class, 'listProjetForms']);
    Route::get('agendaEtps', [AgendaEtpController::class, 'index'])->name('agendaEtps.index');

    Route::prefix('etp/agendas')->group(function () {
        Route::get('/', [AgendaEtpController::class, 'index'])->name('etp.agendas.index');
        Route::get('/getEvents', [AgendaEtpController::class, 'getEvents']);
        Route::get('/getEventsGroupBy', [AgendaEtpController::class, 'getEventsGroupBy']);
        Route::get('/events_resources_agenda', [AgendaEtpController::class, 'getEventResources']);
        Route::get('/annuaire', [AgendaEtpController::class, 'indexAnnuaire'])->name('etp.annuaire.index');
    });

    // Employe_project
    Route::prefix('etp/projet/apprenants')->group(function () {
        Route::get('/{idProjet}', [ApprenantEtpController::class, 'projetIndex'])->name('etp.projet.apprenants.index');
        Route::get('/getApprenantProjets/{idEtp}', [ApprenantEtpController::class, 'getApprenantProjets']);
        Route::get('/getApprenantAdded/{idProjet}', [ApprenantEtpController::class, 'getApprenantAdded']);
        Route::get('/getAllApprenantInter/{idProjet}', [ApprenantEtpController::class, 'getAllApprenantInter']);
        Route::post('/{idProjet}/{idApprenant}', [ApprenantEtpController::class, 'addApprenant']);
        Route::delete('/{idProjet}/{idApprenant}', [ApprenantEtpController::class, 'removeApprenant']);
        Route::get('/checkPresence/{idProjet}/{idEmploye}', [ApprenantEtpController::class, 'getPresenceUnique']);
    });


    Route::prefix('etp/projet/evaluation')->group(function () {
        //Route::post('/chaud', [EvaluationController::class, 'store'])->name('etp.evaluation');
        Route::patch('/editEval', [EvaluationController::class, 'editEval'])->name('etp.editEvaluation');
        Route::get('/checkEval/{idProjet}/{idEmploye}', [EvaluationController::class, 'checkEval']);
    });

    // etp.seances 
    Route::prefix('etp/seanceInternes')->group(function () {
        Route::post('/', [SeanceInterneController::class, 'store']);
        Route::get('/{idProjet}/getAllSeances', [SeanceInterneController::class, 'getAllSeances']);
        Route::patch('/{idSeance}/update', [SeanceInterneController::class, 'update']);
        Route::delete('/{idSeance}/delete', [SeanceInterneController::class, 'destroy']);
        Route::get('/{idProjet}/getSeanceAndTotalTime', [SeanceInterneController::class, 'getSeanceAndTotalTime']); // <===== Récupère le nombre de séance et sa duré en heure(TOTAL)
        // Route::get('/getLastFieldSeances', [SeanceInterneController::class, 'getLastFieldSeances']);         // <===== Récupère le dernier élément de la table seances...
        Route::get('/getLastFieldVueSeances', [SeanceInterneController::class, 'getLastFieldVueSeances']);   // <===== Récupère le dernier élément de la vue seances...
    });

    Route::prefix('etp/seanceIntras')->group(function () {
        Route::post('/', [SeanceIntraController::class, 'store']);
        Route::get('/{idProjet}/getAllSeances', [SeanceIntraController::class, 'getAllSeances']);
        Route::patch('/{idSeance}/update', [SeanceIntraController::class, 'update']);
        Route::delete('/{idSeance}/delete', [SeanceIntraController::class, 'destroy']);

        Route::get('/getLastFieldSeances', [SeanceIntraController::class, 'getLastFieldSeances']);         // <===== Récupère le dernier élément de la table seances...
        Route::get('/getLastFieldVueSeances', [SeanceIntraController::class, 'getLastFieldVueSeances']);   // <===== Récupère le dernier élément de la vue seances...
    });

    Route::prefix('etp/projet/etpInter')->group(function () {
        Route::get('/getEtpAdded/{idProjet}', [ProjetInterController::class, 'getEtpAdded']);
        Route::get('/getApprenantProjetInter/{idProjet}', [ProjetInterController::class, 'getApprenantProjetInter']);
        Route::get('/getApprenantAddedInter/{idProjet}', [ProjetInterController::class, 'getApprenantAddedInter']);
        Route::delete('/{idProjet}/{idEtp}', [ProjetInterController::class, 'removeEtpInter']);
        Route::post('/{idProjet}/{idApprenant}/{idEtp}', [ProjetInterController::class, 'addApprenantInter']);
        Route::delete('/{idProjet}/{idApprenant}/{idEtp}', [ProjetInterController::class, 'removeApprsEtp']);
    });

    // Programme interne ETP
    Route::prefix('etp/programmes')->group(function () {
        Route::post('/{idModule}', [ProgrammeInterneController::class, 'store'])->name('etp.programmes.store');
        Route::put('/test/{idProgramme}/update', [ProgrammeInterneController::class, 'update'])->name('etp.programmes.update');
        Route::get('/{idModule}', [ProgrammeInterneController::class, 'getProgramme']);
        Route::get('/{idProgramme}/edit', [ProgrammeInterneController::class, 'edit']);
        Route::delete('/{idProgramme}', [ProgrammeInterneController::class, 'destroy'])->name('programmes.destroy');
    });

    //ModuleRessource
    Route::prefix('etp/module/ressources')->group(function () {
        Route::post('/{idModule}', [ModuleRessourceInterneController::class, 'store'])->name('etp.module.ressources.store');
        Route::get('/{idModuleRessource}', [ModuleRessourceInterneController::class, 'destroy'])->name('etp.module.ressources.destroy');
        Route::get('/{idModuleRessource}/download', [ModuleRessourceInterneController::class, 'download'])->name('etp.module.ressources.download');
    });

    // Etp.groupes
    Route::prefix('etp/groupes')->group(function () {
        Route::get('/', [EtpGroupeController::class, 'index'])->name('etp.groupes.index');
        Route::get('/create', [EtpGroupeController::class, 'create'])->name('etp.groupes.create');
        Route::post('/', [EtpGroupeController::class, 'store'])->name('etp.groupes.store');
        Route::put('/{idEntreprise}', [EtpGroupeController::class, 'update'])->name('etp.groupes.update');
        Route::get('/getEtp', [EtpGroupeController::class, 'openModalEmp']);
        Route::get('/invite/etp', [EtpGroupeController::class, 'getCustomer']);
        Route::post('/addEmp', [EtpGroupeController::class, 'addEmp'])->name('etp.grpoues.addEmp');
    });

    //customer drawer
    Route::prefix('/etp')->group(function () {
        Route::get('/etp-drawer/{idEtp}', [ShowDrawerController::class, 'showCfpDrawer'])->name('etp.etp-drawer.index');
        Route::get('/form-drawer/{idFormateur}', [ShowDrawerController::class, 'showFormDrawer'])->name('etp.form-drawer.index');
        Route::get('/session-drawer/{idProjet}', [ShowDrawerController::class, 'showSessionDrawer'])->name('etp.session-drawer.index');
        Route::get('/dossier-drawer/{idProjet}', [ShowDrawerController::class, 'showDossierDrawer'])->name('etp.dossier-drawer.index');
        Route::get('/apprenant-drawer/{idProjet}', [ShowDrawerController::class, 'showApprenantDrawer'])->name('etp.apprenant-drawer.index');
        Route::get('/etp-drawers/apprenant/{id}', [ShowDrawerController::class, 'showApprenantWithProject'])->name('etp.etp-drawer.apprenant');

        Route::get('/planreperage-drawer/{idProjet}', [ShowDrawerController::class, 'showPLanDeReperageDrawer'])->name('etp.dossier-drawer.index');
    });

    // AbnGrp
    Route::prefix('grp/abonnement')->name('grp.abonnement.')->controller(AbnGrpController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/recap/{id}', 'recapAbn')->name('recap');
        Route::get('/forfait', 'showSubscriptions')->name('forfait');
        Route::post('/{plan}/subscribe', 'subscribe')->name('subscribe');
    });

    Route::get('modules/catalogue/cfp/avis', [ModuleController::class, 'avis'])->name('catalogueFormation.avis');

    // Agences
    Route::get('etp/agences', [AgenceController::class, 'index'])->name('etp.agences.index');
    Route::post('etp/agences', [AgenceController::class, 'store'])->name('etp.agences.store');
    Route::delete('etp/agences/{id}', [AgenceController::class, 'destroy'])->name('etp.agences.destroy');

    # Routes Testing Center
    # Routes pour les qcm
    Route::prefix('/qcm')->group(function () {
        # Resultats
        Route::get('/{id}/ept/{idEtp}/results', [QcmBaremeController::class, 'resultsOfEtpEmp'])->name('emp.etp.results'); # Route pour les résultats des employés d'une entreprise à un qcm
        Route::get('/{id}/emp/{idAppr}/session/{idSession}', [QcmBaremeController::class, 'result_appr_qcm_one_session'])->name('qcm.result.one.emp.etp'); # Route pour avoir les résulats détaillés d'un employés d'une entreprise après un QCM (les réponses qu'il a choisi, son total de points, son niveau)
        Route::get('/emp/{id}/test/{idQCM}/session/{idSession}/spider-chart-data', [QcmBaremeController::class, 'getSpiderChartData']); # Route menant vers le diagramme en araigné d'un employé après un test pour les entreprises (avec modal)
        Route::get('/{id}/etp/{idEtp}/spider-chart-data-global', [QcmBaremeController::class, 'getGlobalSpiderChartDataForEmpOfEtp'])->name('qcm.spider-chart.data.global.etp.emp'); # Route pour obtenir les données du graphique global des employés d'une entreprise lors d'un test, ici "idCtf" est l'id de l'entreprise (avec modal)
        # Resultats

        # Routes pour l'envoie d'invitation aux employés (seulement pour les entreprises)
        Route::get('/invitations/create', [QcmInvitationController::class, 'create_invitation'])->name('qcm.invitations.create'); # Route vers la vue pour rédiger le mail
        Route::post('/invitations', [QcmInvitationController::class, 'store_invitation'])->name('qcm.invitations.store'); # Route pour stocker les invitations
        # Routes pour l'envoie d'invitation aux employés (seulement pour les entreprises)
    });
    # Routes pour les qcm

    # Routes pour l'achat de crédits pour les référents/entreprises
    Route::prefix('/etp')->group(function () {
        // Routes test pour les paiement (cb, chèque, virement bancaire)
        Route::get('/credits-pack/buy', [CreditsPacksController::class, 'index_buy_credits_pack'])->name('credits.index.etp'); # Route pour afficher les packs de crédits disponible à l'achat pour les entreprises/référents
        Route::get('/credits/{id}/recap', [CreditsPacksController::class, 'recapPurchase'])->name('credits.recap.etp'); # Route pour afficher le recap d'un pack de crédits disponible à l'achat, avant de l'acheter pour les entreprises/référents
        Route::post('/credits/{id}/process', [CreditsPacksController::class, 'processPurchase'])->name('credits.process.etp'); # Route pour procéder à l'achat de crédits pour les entreprises/référents
        Route::get('/credits/history', [CreditsPacksController::class, 'history'])->name('credits.history.etp'); # Route pour voir l'historique d'achat de crédits pour les entreprises/référents
        // Routes test pour les paiement (cb, chèque, virement bancaire)
    });
    # Routes pour l'achat de crédits pour les référents/entreprises
    # Routes Testing Center

    Route::prefix('/search')->group(function () {
        Route::get('/', [SearchController::class, 'searchGeneralityEtp'])->name('search.etp');
        Route::get('/projet', [SearchController::class, 'searchIndexProjet'])->name('searchIndexProjetEtp');
        Route::get('/formateur', [SearchController::class, 'searchIndexFormateur'])->name('searchIndexFormateurEtp');
        Route::get('/cfp', [SearchController::class, 'searchIndexCfp'])->name('serachIndexCfp');
        Route::get('/employe', [SearchController::class, 'searchIndexEmploye'])->name('searchIndexEmployeEtp');
        Route::get('/referent', [SearchController::class, 'searchIndexReferentCustomer'])->name('searchIndexReferentEtp');
    });
});
