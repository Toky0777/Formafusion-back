<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ModuleController;
use App\Http\Controllers\ApprenantController;
use App\Http\Controllers\FormateurController;
use App\Http\Controllers\EmargementController;
use App\Http\Controllers\EvaluationController;
use App\Http\Controllers\AgendaFormController;
use App\Http\Controllers\FormDashboardController;
use App\Http\Controllers\ShowDrawerController;
use App\Http\Controllers\SupportCoursController;

Route::middleware(['auth', 'isFormateur'])->group(function () {
    Route::get('home-form', [FormateurController::class, 'indexForm'])->name('homeForm.indexForm');
    // tableau de bord
    Route::get('formateur/tableau-de-bord', [FormDashboardController::class, 'index'])->name('form.dashboard');

    // Invitation
    Route::get('listInvitations', [FormateurController::class, 'listInvitation'])->name('form.listInvitation');
    Route::patch('acceptInv/{idFormateur}', [FormateurController::class, 'acceptInv'])->name('form.acceptInv');

    //Filtre Formateur
    Route::get('/filter/getDropdownItem', [FormateurController::class, 'getDropdownItem']);
    Route::get('/filter/items', [FormateurController::class, 'filterItems']);
    Route::get('/filter/item', [FormateurController::class, 'filterItem']);

    // Projet Formateur
    Route::prefix('projetsForm')->group(function () {
        Route::get('/', [FormateurController::class, 'indexForm'])->name('projetForms.indexForm');
        Route::get('/list', [FormateurController::class, 'getProjectListForm'])->name('projetForms.list');
        Route::get('/{idProjet}/detailForm', [FormateurController::class, 'detailForm'])->name('projetForms.detailForm');
        Route::get('/getProjetForm/{statut}', [FormateurController::class, 'getProjetForm']);
        Route::get('/getEtpAdded/{idProjet}', [FormateurController::class, 'getEtpAdded']);
        Route::get('/{idProjet}/etp/assign', [FormateurController::class, 'getEtpAssign']);
        Route::post('/add-apprenant/{idProjet}/{idApprenant}', [FormateurController::class, 'addApprenant']);
        Route::delete('/add-apprenant/{idProjet}/{idApprenant}', [FormateurController::class, 'removeApprenant']);
        Route::get('/{idProjet}/detailForm/momentum', [FormateurController::class, 'showmomentum'])->name('projetForms.detailForm.showmomentum');
        Route::post('/uploadphoto/{idProjet}', [FormateurController::class, 'uploadPhotoMomentum'])->name('projetForms.uploadphoto.momentum');
        Route::delete('/deletephoto/{idProjet}/{url}', [FormateurController::class, 'destroyPhoto'])->name('projetForms.deletephoto.destroy');
    });

    // AgendaForm et Annuaire
    Route::prefix('agendaForms')->group(function () {
        Route::get('/', [AgendaFormController::class, 'index'])->name('agenda.form');
        Route::get('/customer', [AgendaFormController::class, 'getIdCustomer']);
        Route::get('/{idProjet}/getEvent', [AgendaFormController::class, 'getEvent']);
        Route::get('/getEvents', [AgendaFormController::class, 'getEvents']);
        Route::get('/countSeance/{month}/{year}', [AgendaFormController::class, 'countSeance']);
    });
    // Route::get('formateur/calendar', [AgendaFormController::class, 'indexCalendar'])->name('calendar.form');

    //seance Formateur 
    Route::prefix('projetsForm')->group(function () {
        Route::post('/', [AgendaFormController::class, 'store']);
        Route::get('/{idProjet}/getAllSeances', [AgendaFormController::class, 'getAllSeances']);
        Route::get('/getLastFieldSeances', [AgendaFormController::class, 'getLastFieldSeances']);
        Route::get('/getLastFieldVueSeances', [AgendaFormController::class, 'getLastFieldVueSeances']);
        Route::get('/{idProjet}/homeForm/get-id-customer', [FormateurController::class, 'getIdCustomer']);
    });

    //Mini CV
    Route::get('miniCv', [FormateurController::class, 'indexCv'])->name('miniCv.index');
    Route::get('formateur/{idFormateur}/mini-cv', [FormateurController::class, 'getMiniCV']);
    Route::get('miniCv/create', [FormateurController::class, 'createCv'])->name('miniCv.index.create');
    Route::get('miniCv/createDp', [FormateurController::class, 'createDp'])->name('miniCv.index.createDp');
    Route::get('miniCv/createCp', [FormateurController::class, 'createCp'])->name('miniCv.index.createCp');
    Route::get('miniCv/createLg', [FormateurController::class, 'createLg'])->name('miniCv.index.createLg');
    Route::post('/update-langue-note/{id}', [FormateurController::class, 'updateNote'])->name('update.langue.note');
    Route::post('/update-competence-note/{id}', [FormateurController::class, 'updateNote'])->name('update.competence.note');
    Route::post('miniCv', [FormateurController::class, 'storeCv'])->name('miniCv.index.store');
    Route::get('/profile/edit', [FormateurController::class, 'editProfile'])->name('profile.edit.form');
    Route::put('/profile/update/{id}', [FormateurController::class, 'updateProfile'])->name('profile.update.form');
    Route::post('form/photo/update/{id}', [FormateurController::class, 'updatePhoto'])->name('photo.update.form');
    Route::delete('miniCv/{id}/destroy', [FormateurController::class, 'destroy'])->name('miniCv.index.destroy');

    //Drawer
    Route::prefix('/projetsForm')->group(function () {
        Route::get('/etp-drawer/{idEtp}', [ShowDrawerController::class, 'showCustomerDrawer']);
        Route::get('/{idProjet}/session-drawer', [ShowDrawerController::class, 'showSessionDrawer']);
        Route::get('/apprenants-drawer/{idProjet}', [ShowDrawerController::class, 'showApprenantDrawer']);
        Route::get('/{idProjet}/document-drawer', [ShowDrawerController::class, 'showDocumentDrawer']);
        Route::get('/planreperage-drawer/{idProjet}', [ShowDrawerController::class, 'showPLanDeReperageDrawer']);
        Route::get('/detail/{idModule}/drawer', [ModuleController::class, 'detailModule']);
    });

    //Mes centre de Formation
    Route::get('centreDeFormation', [FormateurController::class, 'indexCf'])->name('centreDeFormation.index');

    //Apprenant
    Route::get('projetsForm/getApprenantProjets/{idEtp}', [FormateurController::class, 'getApprenantProjets']);

    //Customer
    Route::prefix('projetsForm')->group(function () {
        Route::post('/chaud', [EvaluationController::class, 'store']);
        Route::patch('/editEval', [EvaluationController::class, 'editEval']);
        Route::get('/checkEval/{idProjet}/{idEmploye}', [EvaluationController::class, 'checkEval']);
        Route::get('/etp-drawer/{idEtp}', [FormateurController::class, 'showEtpDrawer']);
        Route::get('/form-drawer/{idFormateur}', [FormateurController::class, 'showFormDrawer']);
        Route::get('/getApprenantAddedInter/{idProjet}', [FormateurController::class, 'getApprenantAddedInter']);
        Route::get('/getApprenantAdded/{idProjet}', [FormateurController::class, 'getApprenantAdded']);
        Route::get('/getApprAddedInter/{idProjet}', [ApprenantController::class, 'getApprAddedInter']);
        Route::get('/checkPresence/{idProjet}/{idEmploye}', [FormateurController::class, 'getPresenceUnique']);
        Route::get('getApprenantProjetInter/{idProjet}', [FormateurController::class, 'getApprenantProjetInter']);
    });

    // Evaluation
    Route::post('/projetsForm/evaluation/aprrenant', [EvaluationController::class, 'save'])->name('evaluation.apprenant.form');
    Route::get('/projetsForm/evaluation/aprrenant/{idEmploye}/{idProjet}', [EvaluationController::class, 'get']);

    //Get Etps
    Route::get('projetsForm/etp/getAllEtps', [FormateurController::class, 'getAllEtps']);

    // SUPPORT DE COURS
    Route::get('projetsForm/support', [SupportCoursController::class, 'support'])->name('support.form');
    // Emargement
    Route::prefix('projetsForm/emargement')->group(function () {
        Route::post('/', [EmargementController::class, 'store']);
        Route::patch('/update/{idProjet}/{idPresent}', [EmargementController::class, 'update']);
        Route::get('/{idProjet}', [EmargementController::class, 'edit']);
    });
    //Règle de confidentialité
    Route::get('formateur/confidentialite', [FormateurController::class, 'confidentialite'])->name('form.confidentialite');
    Route::get('formateur/condition', [FormateurController::class, 'condition'])->name('form.condition');

    // Apprenants
    Route::controller(ApprenantController::class)->group(function () {
        Route::prefix('form/apprenants')->group(function () {
            Route::get('/', 'indexFormAppr')->name('form.apprenants.index');
            Route::post('/', 'addEmpForm')->name('form.apprenants.store');
            Route::get('/entreprises', 'getEntreprises');
            Route::get('/{id}', 'editEmpForm');
            Route::patch('/{id}', 'update');
            Route::post('/{idApprenant}/updatePhoto', 'updateImageAppr');
        });
    });
});
