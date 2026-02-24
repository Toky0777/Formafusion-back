<?php

use App\Http\Controllers\ApprenantController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProjetController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfilController;
use App\Http\Controllers\SeanceController;
use App\Http\Controllers\AgendaEmpController;
use App\Http\Controllers\EvaluationController;
use App\Http\Controllers\FiltreApprenantController;
use App\Http\Controllers\GalleryEmpController;
use App\Http\Controllers\ShowDrawerController;
use App\Http\Controllers\SupportCoursController;

// Employe / Apprenant
Route::middleware(['auth', 'isEmploye'])->group(function () {
    Route::get('homeEmp', [HomeController::class, 'indexEmp'])->name('home.employe');

    Route::get('homeEmp/employe', [HomeController::class, 'getIdEmploye']);

    //Profil de l'apprenant
    Route::prefix('profil')->group(function () {
        Route::get('/edit/emp', [ApprenantController::class, 'editProfil'])->name('profil.edit.emp');
        Route::put('/update/emp', [ApprenantController::class, 'updateProfil'])->name('profil.update.emp');
        Route::post('/crop/emp/{idEmploye}', [ApprenantController::class, 'crop'])->name('profil.crop.emp');
        Route::get('/emp/profil', [ProfilController::class, 'profilEmp'])->name('profil.emp.index');
    });

    // ProjetEmps
    Route::prefix('projetsEmp')->group(function () {
        Route::get('/', [ApprenantController::class, 'indexEmp'])->name('projets.employe.index');
        Route::get('/list', [ApprenantController::class, 'getProjectListEmp']);
        Route::get('/getProjetEmp/{statut}', [ApprenantController::class, 'getProjetEmp']);
        Route::get('/{idProjet}/detailEmp', [ApprenantController::class, 'detailEmp'])->name('emps.detailEmp.index');
        Route::get('/get-id-customer', [ApprenantController::class, 'getIdCustomer'])->name('emps.idCustomer');
        Route::get('/projet/etpInter/getApprenantAddedInter/{idProjet}', [ApprenantController::class, 'getApprenantAddedInter']);
        Route::get('/projet/apprenants/getApprAddedInter/{idProjet}', [ApprenantController::class, 'getApprAddedInter']);
        Route::get('/projet/etpInter/getEtpAdded', [ApprenantController::class, 'getEtpAdded']);
        Route::get('/{idProjet}/detailForm/momentum', [ApprenantController::class, 'showmomentum'])->name('emps.detailForm.showmomentum');
        Route::post('/uploadphoto/{idProjet}', [ApprenantController::class, 'uploadPhotoMomentum'])->name('emps.uploadphoto.momentum');
        Route::delete('/deletephoto/{idProjet}/{url}', [ApprenantController::class, 'destroyPhoto'])->name('emps.deletephoto.destroy');
    });
    //Filtre Projets Employe
    Route::prefix('employe')->group(function () {
        Route::get('/filter/getDropdownItem', [FiltreApprenantController::class, 'getDropdownItem']);
        Route::get('/filter/items', [FiltreApprenantController::class, 'filterItems']);
        Route::get('/filter/item', [FiltreApprenantController::class, 'filterItem']);
    });

    //Seance
    Route::prefix('employe')->group(function () {
        Route::get('seancesEmp/{idProjet}/getAllSeances', [ApprenantController::class, 'getAllSeance']);
        Route::get('seancesEmp/getLastFieldVueSeances', [SeanceController::class, 'getLastFieldVueSeances']);
    });

    //Salle
    Route::prefix('employe')->group(function () {
        Route::get('/salles/list', [ApprenantController::class, 'list']);
        Route::get('/salles/getAllSalles', [ApprenantController::class, 'getAllSalle'])->name('employes.salles.getAllSalle');
        Route::get('/{idProjet}/getSalleAdded', [ApprenantController::class, 'getSalleAdded']);
    });

    // Employe Inter / Support de cours / Programme 
    Route::prefix('projetsEmp')->group(function () {
        Route::get('/support', [SupportCoursController::class, 'supportEmp'])->name('support.emp');
        Route::get('/projet/etpInter/getApprenantProjetInter/{idProjet}', [ApprenantController::class, 'getApprenantProjetInter']);
        Route::post('/etpIntra/{idProjet}/{idApprenant}', [ApprenantController::class, 'addApprenant']);
        Route::post('/etpInter/{idProjet}/{idApprenant}/{idEtp}', [ApprenantController::class, 'addApprenantInter']);
        Route::get('/{idModule}/getProgrammeProject', [ApprenantController::class, 'programProject']);
        Route::get('/{idModule}/getModuleRessourceProject', [ApprenantController::class, 'moduleRessource']);
        Route::get('projetsEmp/avis', [ProjetController::class, 'avisEmp'])->name('projetEmp.avis');
        Route::get('{idModuleRessource}/download', [SupportCoursController::class, 'download'])->name('projetEmp.download');
    });


    //Evaluation
    Route::prefix('employe/projet/evaluation')->group(function () {
        Route::post('/chaud', [EvaluationController::class, 'store'])->name('emp.evaluation');
        Route::patch('/editEval', [EvaluationController::class, 'editEval'])->name('emp.editEvaluation');
        Route::get('/checkEval/{idProjet}/{idEmploye}', [EvaluationController::class, 'checkEval']);
        Route::get('/checkPresence/{idProjet}/{idEmploye}', [ApprenantController::class, 'getPresenceUnique']);
    });

    // AgendaEmps / CalendrierEmps
    Route::prefix('agenda')->group(function () {
        Route::get('/', [AgendaEmpController::class, 'index'])->name('agendaEmps.index');
        Route::get('/getEvent', [AgendaEmpController::class, 'getEvent']);
        Route::get('/getEvents', [AgendaEmpController::class, 'getEvents']);
        Route::get('/countSeance/{month}/{year}', [AgendaEmpController::class, 'countSeance']);
        Route::get('/calendar', [AgendaEmpController::class, 'indexCalendar'])->name('calendar.employe.index');
    });

    //Repport
    Route::get('projetsEmp/{idProjet}/repport', [ApprenantController::class, 'repport'])->name('repport.emp');

    // Drawer Projet List
    Route::get('employes/projets/{idFormateur}/mini-cv', [ApprenantController::class, 'getMiniCv']);
    Route::get('employes/modules/detail/{idModule}/drawer', [ApprenantController::class, 'getModule']);
    Route::get('employes/etp-drawer/{idEtp}', [ApprenantController::class, 'getEtp']);
    Route::get('employes/projets/{idProjet}/session-drawer', [ApprenantController::class, 'getSession']);
    Route::get('employes/projets/{idProjet}/apprenant-drawer', [ApprenantController::class, 'DrawerApprenant']);
    Route::get('employes/projets/{idProjet}/document-drawer', [ApprenantController::class, 'DrawerDoc']);
    Route::get('employes/projets/session-drawer/{idProjet}', [ShowDrawerController::class, 'showSessionDrawer']);
    Route::get('employes/projets/planreperage-drawer/{idProjet}', [ShowDrawerController::class, 'showPLanDeReperageDrawer']);

    // List app et Form et Etp
    Route::get('employes/projets/apprenants/getApprenantProjets/{idEtp}', [ApprenantController::class, 'getApprenantProjets']);
    Route::get('employes/projets/apprenants/getApprenantAdded/{idProjet}', [ApprenantController::class, 'getApprenantAdded']);
    Route::get('employes/forms/getAllForms', [ApprenantController::class, 'getAllForms']);
    Route::get('employes/projets/{idProjet}/getFormAdded', [ApprenantController::class, 'getFormAdded']);
    Route::get('employes/invites/etp/getAllEtps', [ApprenantController::class, 'getAllEtps']);
    Route::get('employes/projets/{idProjet}/etp/assign', [ApprenantController::class, 'getEtpAssign']);

    //Règle de confidentialité
    Route::get('employes/confidentialite', [ApprenantController::class, 'confidentialite'])->name('emp.confidentialite');
    Route::get('employes/condition', [ApprenantController::class, 'condition'])->name('emp.condition');

    // Gallerie photos des employés emp.gallery
    Route::prefix('/employes/gallery')->group(function () {
        Route::post('/{idProjet}/addImage', [GalleryEmpController::class, 'addImageGallery'])->name('emp.gallery.addImage');
        Route::get('/', [GalleryEmpController::class, 'getAllGallery'])->name('emp.gallery.folder');
        Route::get('/folder', [GalleryEmpController::class, 'getAllFolder']);
        Route::get('/folderFilter', [GalleryEmpController::class, 'getAllFolderOrder']);
        Route::get('/getImage', [GalleryEmpController::class, 'getGalleryByFolder']);
        Route::get('/image', [GalleryEmpController::class, 'allImage']);
    });

    // Evaluation-froid
    Route::controller(EvaluationController::class)->prefix('employes/evaluations/froids')->group(function(){
        Route::get('/', 'index')->name('employes.evaluations.froids.index');
        Route::post('/store{idProjet}', 'storeColdEvaluation')->name('employes.evaluations.froids.store');
    });
});
