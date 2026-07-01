<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AgendaCfpController;
use App\Http\Controllers\AgendaEmpController;
use App\Http\Controllers\AgendaEtpController;
use App\Http\Controllers\AgendaFormController;
use App\Http\Controllers\RentabilityController;
use App\Http\Controllers\AnalyticCfpController;
use App\Http\Controllers\ApprenantController;
use App\Http\Controllers\ApprenantEtpController;
use App\Http\Controllers\AtteandanceController;
use App\Http\Controllers\AttributionBadgeController;
use App\Http\Controllers\BadgeController;
use App\Http\Controllers\BankAccountController;
use App\Http\Controllers\BatchController;
use App\Http\Controllers\BatchLearnerController;
use App\Http\Controllers\BcContactController;
use App\Http\Controllers\BonCommandeController;
use App\Http\Controllers\BonCommandeEtpController;
use App\Http\Controllers\ModuleProgramContentController;
use App\Http\Controllers\CatalogueController;
use App\Http\Controllers\CentreController;
use App\Http\Controllers\CfpDashboardController;
use App\Http\Controllers\FmfpController;
use App\Http\Controllers\CfpInviteEtp;
use App\Http\Controllers\CfpProfilController;
use App\Http\Controllers\ChiffreAffaireRepportingCfp;
use App\Http\Controllers\ClientCfpController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\ContentRefreshController;
use App\Http\Controllers\CoursController;
use App\Http\Controllers\CreditsWalletController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DisponibileMatController;
use App\Http\Controllers\DossierController;
use App\Http\Controllers\DossierControllerEtp;
use App\Http\Controllers\EmargementController;
use App\Http\Controllers\EmargementEmployeController;
use App\Http\Controllers\EmargementEtpController;
use App\Http\Controllers\EmargementFormateurController;
use App\Http\Controllers\EmployecfpController;
use App\Http\Controllers\EmployeController;
use App\Http\Controllers\EmployeEtpController;
use App\Http\Controllers\EvaluationChaudController;
use App\Http\Controllers\EvaluationController;
use App\Http\Controllers\FactureController;
use App\Http\Controllers\FactureProfoController;
use App\Http\Controllers\FiltreApprenantController;
use App\Http\Controllers\FinancialGoalController;
use App\Http\Controllers\FolderController;
use App\Http\Controllers\FormateurController;
use App\Http\Controllers\FormDashboardController;
use App\Http\Controllers\GalleryController;
use App\Http\Controllers\GalleryFormController;
use App\Http\Controllers\GalleryEmpController;
use App\Http\Controllers\GamificationController;
use App\Http\Controllers\historiqueController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\InvoiceAccountController;
use App\Http\Controllers\InvoiceContactController;
use App\Http\Controllers\InvoicePaymentController;
use App\Http\Controllers\LieuxController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\MarketPlaceController;
use App\Http\Controllers\MaterialController;
use App\Http\Controllers\MenuRefreshController;
use App\Http\Controllers\MobileMoneyAccountController;
use App\Http\Controllers\ModuleController;

use App\Http\Controllers\ModuleRessourceController;
use App\Http\Controllers\ModuleSkillController;
use App\Http\Controllers\ModuleSkillFormateurController;
use App\Http\Controllers\ParticulierController;
use App\Http\Controllers\PdfController;
use App\Http\Controllers\ProfilController;
use App\Http\Controllers\ProfilEtpController;
use App\Http\Controllers\ProgrammeController;
use App\Http\Controllers\ProjectDetailController;
use App\Http\Controllers\ProjectMaterialController;
use App\Http\Controllers\ProjetController;
use App\Http\Controllers\ProjetInterController;
use App\Http\Controllers\ProjetInterneController;
use App\Http\Controllers\ProspectionController;
use App\Http\Controllers\QcmBaremeController;
use App\Http\Controllers\QcmCategoryEvaluationController;
use App\Http\Controllers\QcmController;
use App\Http\Controllers\QcmInvitationCfpController;
use App\Http\Controllers\QcmInvitationController;
use App\Http\Controllers\QcmInvitCampController;
use App\Http\Controllers\QcmInvitCfpCampController;
use App\Http\Controllers\RecoveryController;
use App\Http\Controllers\ReportingController;
use App\Http\Controllers\ReportingControllerEtp;
use App\Http\Controllers\RepportingClientController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\ReservationEtpController;
use App\Http\Controllers\RewardsController;
use App\Http\Controllers\RewardsEnterpriseController;
use App\Http\Controllers\SalleCfpController;
use App\Http\Controllers\SalleController;
use App\Http\Controllers\SalleEtpController;
use App\Http\Controllers\SeanceController;
use App\Http\Controllers\SeanceInterneController;
use App\Http\Controllers\SeanceIntraController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SectionController;
use App\Http\Controllers\SecurityController;
use App\Http\Controllers\ShowDrawerController;
use App\Http\Controllers\SkillMatrixController;
use App\Http\Controllers\SkillMatrixEmployeController;
use App\Http\Controllers\SkillMatrixEtpController;
use App\Http\Controllers\SkillMatrixFormateurController;
use App\Http\Controllers\StatisticsController;
use App\Http\Controllers\SubContractorController;
use App\Http\Controllers\SupportCoursController;
use App\Http\Controllers\TranslationController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VerificationBadgeController;
use App\Http\Controllers\WorkingDaysPolicyController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FactureAcompteController;
use App\Http\Controllers\PresenceController;
use App\Http\Controllers\AnalyticPortefeuilleClientController;
use App\Http\Controllers\AttestationController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\EvaluationChaudEtpController;
use App\Http\Controllers\FactureSoldeController;
use App\Http\Controllers\QualiopiController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', [UserController::class, 'getUser']);
Route::post('/translate', [TranslationController::class, 'translate']);
Route::post('/translate-bulk', [TranslationController::class, 'translateBulk']);

Route::post('/quote/company', [MarketPlaceController::class, 'quoteDemandCompany']);
Route::post('/quote/individual', [MarketPlaceController::class, 'quoteDemandIndividual']);

Route::prefix('auth')->group(function () {
    Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLink']);
    Route::post('/reset-password', [PasswordResetController::class, 'resetPassword']);
    Route::post('/verify-reset-token', [PasswordResetController::class, 'verifyToken']);
});

Route::prefix('marketplace')->group(function () {
    // customer
    Route::get('/getAllCustomer', [MarketPlaceController::class, 'getAllCustomer']);
    Route::get('/getAllCategoryByCenter', [MarketPlaceController::class, 'getAllCategory']);
    Route::get('/getAllRegionCenter', [MarketPlaceController::class, 'getAllRegion']);
    Route::get('/search', [MarketPlaceController::class, 'searchByCfp']);
    Route::get('/filter/cfp', [MarketPlaceController::class, 'filterCfp']);
    Route::get('/customer/getApercu/{idCfp}', [MarketPlaceController::class, 'getApercu']);
    Route::get('/customer/show/{idCfp}', [MarketPlaceController::class, 'showDetail']);
    Route::get('/customer/getContact/{idCfp}', [MarketPlaceController::class, 'getContact']);
    Route::get('/customer/getCoursProgram/{idCfp}', [MarketPlaceController::class, 'getCoursProgram']);
    Route::get('/customer/getAvisCfp/{idCfp}', [MarketPlaceController::class, 'getAvisCfp']);
    Route::get('/customer/showEvent/{idCfp}', [MarketPlaceController::class, 'getEvenementCfp']);

    // module
    Route::get('/getAllModule', [MarketPlaceController::class, 'getAllModule']);
    Route::get('/getAllCategoryHaveCourse', [MarketPlaceController::class, 'getAllCategoryHaveCourse']);
    Route::get('/filter/course', [MarketPlaceController::class, 'filterCourse']);
    Route::get('/getAllLevel', [MarketPlaceController::class, 'getAllLevel']);
    Route::get('/showCours/{idModule}', [MarketPlaceController::class, 'showDetailCours']);
    Route::get('/getApercuCours/{idModule}', [MarketPlaceController::class, 'getApercuCours']);
    Route::get('/module/showEvent/{idModule}', [MarketPlaceController::class, 'getEvenementModule']);
    Route::get('/getAvisModule/{idModule}', [MarketPlaceController::class, 'getAvisModule']);
    Route::get('/getAllCategoryCourse', [MarketPlaceController::class, 'getAllCategoryCourse']);
    Route::get('/getAllCustomerCourse', [MarketPlaceController::class, 'getAllCustomerCourse']);
    Route::get('/getAllLevelsCourse', [MarketPlaceController::class, 'getAllLevelsCourse']);
    Route::get('/getAllCitiesCourse', [MarketPlaceController::class, 'getAllCitiesCourse']);

    // Event
    Route::get('/getAllProject', [MarketPlaceController::class, 'getAllProjectInEvent']);
    Route::get('/filter/event', [MarketPlaceController::class, 'filterEvent']);
    Route::get('/getAllCategoryInEvent', [MarketPlaceController::class, 'getAllCategoryInEvent']);
    Route::get('/getAllCustomerInEvent', [MarketPlaceController::class, 'getAllCustomerInEvent']);
    Route::get('/getAllCitiesEvent', [MarketPlaceController::class, 'getAllCitiesEvent']);
    Route::get('/getAllLevelsEvent', [MarketPlaceController::class, 'getAllLevelsEvent']);
    Route::get('/getAllPeriodsEvent', [MarketPlaceController::class, 'getAllPeriodsEvent']);
    Route::get('/showEvent/{idProjet}', [MarketPlaceController::class, 'showDetailEvent']);

    // Reservation
    Route::get('/reservation/detail/{projectId}', [MarketPlaceController::class, 'getDetailProjectInReservation']);


    // Other
    Route::get('/getAutre/{idCfp}', [MarketPlaceController::class, 'getAutre']);

    // Contact
    Route::post('/send-email/{idCfp}', [MarketPlaceController::class, 'sendEmail']);



    Route::prefix('sections')->group(function () {
        Route::post('/reglement', [SectionController::class, 'storeReglement'])->name('sections.storeReglement');
        Route::post('/accueil', [SectionController::class, 'storeAccueil'])->name('sections.storeAccueil');
        Route::post('/conditions', [SectionController::class, 'storeConditions'])->name('sections.storeConditions');
        Route::post('/acces', [SectionController::class, 'storeAcces'])->name('sections.storeAcces');
        Route::post('/accompagnement', [SectionController::class, 'storeAccompagnement'])->name('sections.storeAccompagnement');
        Route::post('/organigramme', [SectionController::class, 'storeOrganigramme'])->name('sections.storeOrganigramme');
    });
});

// ----------------CFP
Route::middleware(['auth:sanctum', 'isEmployeCfp'])->group(function () {
    Route::prefix('ai')->group(function () {
        Route::post('/rentability/predict', [RentabilityController::class, 'predict']);
        Route::post('/rentability/train', [RentabilityController::class, 'train']);
        Route::get('/rentability/health', [RentabilityController::class, 'health']);
        Route::get('/rentability/statistics', [RentabilityController::class, 'statistics']);
        Route::get('/rentability/getHistoricalData', [RentabilityController::class, 'getHistoricalDataForDebug']);
        Route::get('/getModuleCfp', [RentabilityController::class, 'getModuleCfp']);
        Route::get('/getClientCfp', [RentabilityController::class, 'getClientCfp']);
    });

    // PRESENCE CFP
    Route::prefix('cfp/presence')->group(function () {
        Route::get('/', [PresenceController::class, 'index']);
        Route::get('/emargement/{idProjet}', [PresenceController::class, 'showEmg']);
        Route::get('/{idProjet}/data', [AtteandanceController::class, 'getDataPresence']);
        Route::get('/projet/apprenants/getApprenantAdded/{idProjet}', [AtteandanceController::class, 'getAttendanceByProject']);
        Route::get('/projet/apprenants/getApprAddedInter/{idProjet}', [AtteandanceController::class, 'getAttendanceByProjectInter']);
        Route::post('/emargement', [EmargementController::class, 'store'])->name('emargements.cfp.store');
        Route::patch('/update/{idProjet}/{isPresent}', [EmargementController::class, 'update'])->name('emargements.update');
        Route::delete('/delete', [EmargementController::class, 'destroy'])->name('emargements.destroy');
    });

    Route::get('/cfp/all-entreprise', [CustomerController::class, 'getAllEntreprise']);
    Route::get('/cfp/all-folder', [DossierController::class, 'allFolder']);
    Route::get('/cfp/course', [ModuleController::class, 'allModuleCfp']);
    Route::get('/cfp/particulars', [ParticulierController::class, 'allParticulars']);
    Route::get('/cfp/get-entreprise', [CustomerController::class, 'getEntrepriseByIds']);
    Route::post('/cfp/create-project', [ProjetController::class, 'create']);
    /** PRESENCE CFP **/
    // Route::get('project/count', [AtteandanceController::class, 'getCountProject']);
    // Route::prefix('cfp/attendance/projets')->group(function () {
    //     Route::get('/{status}', [AtteandanceController::class, 'index']);
    //     Route::get('/{idProjet}/data/presence', [AtteandanceController::class, 'getDataPresence']);
    //     Route::get('/filtre/{select}', [AtteandanceController::class, 'getFiltre']);
    // });
    // Route::prefix('cfp/attendance/projet/apprenants')->group(function () {
    //     Route::get('/getApprenantAdded/{idProjet}', [AtteandanceController::class, 'getAttendanceByProject']);
    //     Route::get('/getApprAddedInter/{idProjet}', [AtteandanceController::class, 'getAttendanceByProjectInter']);
    // });
    // Route::prefix('cfp/attendance/emargement')->group(function () {
    //     Route::post('/', [EmargementController::class, 'store'])->name('emargements.cfp.store');
    //     Route::patch('/update/{idProjet}/{isPresent}', [EmargementController::class, 'update'])->name('emargements.update');
    //     Route::get('/{idProjet}', [EmargementController::class, 'edit'])->name('emargements.edit');
    //     Route::delete('/delete', [EmargementController::class, 'destroy'])->name('emargements.destroy');
    // });

    // Analytic current
    Route::get('/analytic/home/{year}', [AnalyticCfpController::class, 'index']);
    Route::get('/analytic/home/project/{month}/{year}/{type}', [AnalyticCfpController::class, 'getProject']);
    Route::get('/analytic/home/learner/{month}/{year}', [AnalyticCfpController::class, 'getLearner']);
    Route::get('/analytic/home/bon_commandes/{month}/{year}', [AnalyticCfpController::class, 'getDetailsBc']);

    Route::prefix('/cfp/fmfp')->group(function () {
        Route::get('/', [FmfpController::class, 'index']);
        Route::post('/', [FmfpController::class, 'storeFmfp']);
        Route::get('/{id}', [FmfpController::class, 'show']);
        Route::patch('/changeStatus/{id}', [FmfpController::class, 'changeStatus']);
        Route::get('/comments/{id}', [FmfpController::class, 'getAllCommentsInFolder']);
        Route::post('/comment/{id}', [FmfpController::class, 'commentFolder']);
        Route::delete('/deleteComment/{idComment}/{idFolder}', [FmfpController::class, 'deleteCommentInFolder']);
        Route::patch('/update/{id}', [FmfpController::class, 'update']);
        Route::delete('/delete/{id}', [FmfpController::class, 'delete']);
    });

    Route::get('/get_counts', [StatisticsController::class, 'getCounts']);
    Route::get('/home', [CfpDashboardController::class, 'index'])->name('home');
    Route::get('/projet/{idProjet}/detail', [ProjetController::class, 'show'])->name('cfp.projets.show');

    // recherche global
    Route::get('/search-generality', [SearchController::class, 'searchGenerality']);
    Route::get('/key-suggestion', [SearchController::class, 'keySuggestion']);
    Route::prefix('cfp/search')->group(function () {
        Route::get('/learners', [SearchController::class, 'getLearner']);
        Route::get('/projects', [SearchController::class, 'getAllProject']);
        Route::get('/project-reference', [SearchController::class, 'getProjectCfpByReference']);
        Route::get('/project-neighborhood', [SearchController::class, 'getProjectCfpByNeighborhood']);
        Route::get('/project-place', [SearchController::class, 'getProjectCfpByPlace']);
        Route::get('/project-city', [SearchController::class, 'getProjectCfpByCity']);
        Route::get('/project-with-etp', [SearchController::class, 'getProjectWithEtpPaginate']);
        Route::get('/trainers', [SearchController::class, 'getTrainerCfp']);
        Route::get('/entreprises', [SearchController::class, 'getEntreprise']);
        Route::get('/referents', [SearchController::class, 'getReferentCfp']);
        Route::get('/referent-customers', [SearchController::class, 'getReferentCustomer']);
        Route::get('/courses', [SearchController::class, 'getCourse']);
        Route::get('/folders', [SearchController::class, 'getFolderCfp']);
        Route::get('/invoices', [SearchController::class, 'getInvoice']);
        Route::get('/proforma', [SearchController::class, 'getProforma']);
        Route::get('/purchase_order', [SearchController::class, 'getPurchaseOrder']);
        Route::get('/particulars', [SearchController::class, 'getParticular']);
    });

    // Analytics
    Route::get('/ca/{month}/{type}', [CfpDashboardController::class, 'currentCa']);
    Route::get('/learner/{month}/{type}', [CfpDashboardController::class, 'getLearner']);

    Route::prefix('cfp/objectif')->group(function () {
        Route::get('/', [FinancialGoalController::class, 'index'])->name('objectif.index');
        Route::post('/save', [FinancialGoalController::class, 'save']);
        Route::get('/getByYear', [FinancialGoalController::class, 'getGoalByYear']);
        Route::get('/getTotalYear', [FinancialGoalController::class, 'getTotalYear']);
        Route::get('/getByMonth', [FinancialGoalController::class, 'goalByMOnth']);
    });

    Route::prefix('cfp/dashboard/formateur')->group(function () {
        Route::get('/', [FormateurController::class, 'dashboardFormateur'])->name('cfp.dashForm.index');
        Route::get('/{id}', [FormateurController::class, 'getDetailsByForm']);
    });

    // Clients
    Route::prefix('cfp/clients')->group(function () {
        Route::get('/{idTypeEtp}', [ClientController::class, 'index'])->name('cfp.clients.index');
        Route::get('/search/{name}', [ClientController::class, 'searchName']);
        Route::get('/', [ClientController::class, 'getAllEtps']);
        Route::get('/frais', [ClientController::class, 'getAllFrais']);
        Route::get('/{id}/edit', [ClientController::class, 'edit'])->name('cfp.clients.edit');
        Route::get('/cities/get', function () {
            return response()->json(['villes' => [
                'idVille' => 1,
                'ville_name' => 'Paris',
            ]]);
        });
        Route::get('/type-customers/get', function () {
            return response()->json(['typeCustomers' => [
                ['idTypeCustomer' => 1, 'typeCustomerDesc' => 'Entreprise'],
                ['idTypeCustomer' => 2, 'typeCustomerDesc' => 'Particulier'],
            ]]);
        });
        Route::get('/{id}/edit', [ClientController::class, 'edit']);
        Route::patch('/{id}', [ClientController::class, 'update']);
        Route::post('/{id}/logo', [ClientController::class, 'updateLogo']);
        Route::delete('/{id}', [ClientController::class, 'destroy']);
    });

    // portefeuille client
    Route::prefix('cfp/client_portefeuille')->group(function () {
        Route::get('/', [AnalyticPortefeuilleClientController::class, 'getClientKPIs']);
    });
    // Invitation via "customerName"
    Route::controller(InvitationController::class)->prefix('cfp/invites')->group(function () {
        Route::get('/{name}/name', 'getCustomerName');
        Route::get('/{id}/{typeCustomer}', 'getCustomer');
        Route::post('/store/{typeCustomer}/{idCustomer}', 'inviteCustomer');
        Route::post('/new-customer', 'inviteNewCustomer');
    });

    // customer drawer
    Route::prefix('/cfp')->group(function () {
        Route::get('/etp-drawer/{idEtp}', [ShowDrawerController::class, 'showEtpDrawer'])->name('cfp.etp-drawer.index');
    });

    // Particulier
    Route::prefix('particulier')->group(function () {
        Route::get('/', [ParticulierController::class, 'index'])->name('cfp.particulier.index');
        Route::get('/projets/{id}', [ParticulierController::class, 'indexPart']);
        Route::post('/', [ParticulierController::class, 'store']);
        Route::get('/{idParticulier}', [ParticulierController::class, 'edit'])->name('particulier.edit');
        Route::patch('/{idParticulier}', [ParticulierController::class, 'update']);
        Route::delete('/destroy/{id}', [ParticulierController::class, 'destroy'])->name('particulier.destroy');
        Route::post('/{idParticulier}/updatePhoto', [ParticulierController::class, 'updatePhoto']);
    });

    // Gallery
    Route::prefix('/cfp/gallery')->group(function () {
        Route::get('/{idProjet}/image', [GalleryController::class, 'allImage']);
        Route::post('/{idProjet}/addImage', [GalleryController::class, 'addImageGallery'])->name('cfp.gallery.addImage');
        Route::delete('/deleteImage/{id}', [GalleryFormController::class, 'deleteImageGallery']);
        Route::put('/update/{id}', [GalleryFormController::class, 'updateDescriptionImageGallery']);
        Route::get('/', [GalleryController::class, 'getAllGallery'])->name('cfp.gallery.folder');
        Route::get('/folder/{year}', [GalleryController::class, 'getAllFolder']);
        Route::get('/project/{idDossier}', [GalleryController::class, 'getProjectFolder']);
        Route::get('/folderFilter/{year}', [GalleryController::class, 'getAllFolderOrder']);
        Route::get('/getImage/{idDossier}', [GalleryController::class, 'getGalleryByFolder']);
    });

    Route::prefix('cfp/images')->group(function () {
        Route::get('/{idDossier}', [ImageController::class, 'index']);
        Route::post('/{idProjet}', [ImageController::class, 'store']);
        Route::put('/{id}', [ImageController::class, 'update']);
        Route::delete('/{id}', [ImageController::class, 'destroy']);
    });

    // badge
    Route::prefix('cfp/badge')->group(function () {
        Route::get('/', [BadgeController::class, 'index'])->name('cfp.badge.index');
        Route::get('/getModules', [BadgeController::class, 'getModules'])->name('cfp.badge.getModules');
        Route::post('/', [BadgeController::class, 'store'])->name('cfp.badge.store');
        Route::get('/{id}/edit', [BadgeController::class, 'edit'])->name('cfp.badge.edit');
        Route::post('/{id}/update', [BadgeController::class, 'update'])->name('cfp.badge.update');
        Route::post('/{id}/delete', [BadgeController::class, 'destroy'])->name('cfp.badge.destroy');

        Route::prefix('/attribution')->group(function () {
            Route::get('/', [AttributionBadgeController::class, 'index'])->name('cfp.attribution.index');
            Route::get('/create', [AttributionBadgeController::class, 'create'])->name('cfp.attribution.create');
            Route::post('/store', [AttributionBadgeController::class, 'store'])->name('cfp.attribution.store');
            Route::post('/{id}/destroy', [AttributionBadgeController::class, 'destroy'])->name('cfp.attribution.destroy');
            Route::get('/apprenants-projet/{idProjet}', [AttributionBadgeController::class, 'getApprenantsProjet'])->name('cfp.attribution.getApprenantsProjet');
            Route::get('/projets-module/{idModule}', [AttributionBadgeController::class, 'getProjetsModule'])->name('cfp.attribution.getApprenantsProjet');
            Route::get('/badge-details/{idBadge}', [AttributionBadgeController::class, 'getBadgeDetails'])->name('cfp.attribution.getBadgeDetails');
            Route::get('/badge-send/{code}', [VerificationBadgeController::class, 'verify'])->name('cfp.badge.verification');
        });
    });

    //attestion numerka
    Route::prefix('cfp/attestation')->group(function () {
        Route::get('/', [AttestationController::class, 'index'])->name('cfp.attestation.index');
        Route::post('/', [AttestationController::class, 'store'])->name('cfp.attestation.store');
        Route::get('/{id}/edit', [AttestationController::class, 'edit'])->name('cfp.attestation.edit');
        Route::post('/{id}/update', [AttestationController::class, 'update'])->name('cfp.attestation.update');
        Route::post('/{id}/delete', [AttestationController::class, 'destroy'])->name('cfp.attestation.destroy');
        Route::get('/getProjet', [AttestationController::class, 'getProjet'])->name('cfp.attestation.getProjet');
        Route::get('/{id}/getApprenant', [AttestationController::class, 'getApprenant'])->name('cfp.attestation.getApprenant');
    });

    // facture
    // Dashboard
    Route::get('/dashboard', [FactureController::class, 'dashboard'])->name('cfp.factures.dashboard');

    // Facture
    Route::prefix('cfp/factures')->group(function () {
        Route::get('/id/{id}', [FactureController::class, 'index'])->name('cfp.factures.index');
        Route::get('/filtre', [FactureController::class, 'getFiltre'])->name('cfp.factures.filtre');
        Route::get('/create', [FactureController::class, 'create'])->name('cfp.factures.create');
        Route::post('/', [FactureController::class, 'store'])->name('cfp.factures.store');
        Route::get('/{id}', [FactureController::class, 'show'])->name('cfp.factures.show');
        Route::get('/{id}/edit', [FactureController::class, 'edit'])->name('cfp.factures.edit');
        Route::put('/{idInvoice}', [FactureController::class, 'update'])->name('cfp.factures.update');
        Route::post('/{id}/approve', [FactureController::class, 'approve'])->name('cfp.factures.approve');
        Route::get('/allEtps', [FactureController::class, 'getAllEtps']);
        Route::get('/export/{id}', [FactureController::class, 'exportInvoicePdf'])->name('exportInvoice');
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
        Route::get('/accueil', [FactureController::class, 'home'])->name('cfp.factures.accueil');
        Route::post('/{idInvoice}/changeStatus/{status}', [FactureController::class, 'changeStatus']);
        Route::get('/grouped/{id}', [FactureController::class, 'getInvoiceGrouped']);
    });

    Route::prefix('cfp/factures_acounts')->group(function () {
        Route::get('/getTypePaiements', [FactureController::class, 'getTypePaiements']);
        Route::get('/getBankAcounts', [FactureController::class, 'getBankAcounts']);
        Route::get('/getMobileMoneyAcounts', [FactureController::class, 'getMobileMoneyAcounts']);
    });

    // Recouvrement
    Route::get('/recouvrement', [RecoveryController::class, 'index']);
    Route::get('/recouvrement/{idEntreprise}', [RecoveryController::class, 'getInvoiceUnpaidByCustomer']);
    Route::get('/purchase_order/{idEntreprise}', [RecoveryController::class, 'getPurchaseOrderByCustomer']);
    Route::get('/recouvrement/{idEntreprise}/{date}', [RecoveryController::class, 'getInvoiceUnpaidByCustomerWithDate']);
    Route::get('/purchase_order/{idEntreprise}/{date}', [RecoveryController::class, 'getPurchaseOrderByCustomerWithDate']);

    // facture payments
    Route::get('/payments/{id}', [InvoicePaymentController::class, 'index']);
    Route::post('/payment/store', [InvoicePaymentController::class, 'store']);
    Route::post('/payments/groupe', [InvoicePaymentController::class, 'storeGroupedPayment']);
    Route::put('/payments/{payment}', [InvoicePaymentController::class, 'update']);
    Route::delete('/invoice/{idFacture}/payments/{payment}', [InvoicePaymentController::class, 'destroy']);

    // FACTURE PROFORMA
    Route::prefix('cfp/factureProfo')->group(function () {
        Route::get('/', [FactureProfoController::class, 'index'])->name('cfp.factureProfo.index');
        Route::get('/create', [FactureProfoController::class, 'create'])->name('cfp.factureProfo.create');
        Route::get('/{id}/edit', [FactureProfoController::class, 'edit'])->name('cfp.factureProfo.edit');
        Route::post('/{id}/convertir', [FactureController::class, 'convertir'])->name('cfp.factures.convertir');
    });
    // ACOMPTE
    Route::prefix('cfp/factureAcompte')->group(function () {
        Route::get('/', [FactureAcompteController::class, 'index'])->name('cfp.factureAcompte.index');
        Route::get('/create', [FactureAcompteController::class, 'create'])->name('cfp.factureAcompte.create');
        Route::get('/{id}/edit', [FactureAcompteController::class, 'edit'])->name('cfp.factureAcompte.edit');
    });
    // SOLDE
    Route::prefix('cfp/factureSolde')->group(function () {
        Route::get('/', [FactureSoldeController::class, 'index']);
        Route::get('/create', [FactureSoldeController::class, 'create']);
        Route::get('/{id}/edit', [FactureSoldeController::class, 'edit']);
        Route::get('/{idBC}/detailAcompte', [FactureSoldeController::class, 'getAllDetailAcompteByBC']);
        Route::get('/{idBC}/detailBc', [FactureSoldeController::class, 'getAllDetailBC']);
    });

    // refresh
    Route::prefix('cfp/refresh')->group(function () {
        Route::get('/', [MenuRefreshController::class, 'index'])->name('cfp.refresh.index');
        Route::post('/menu-refresh', [MenuRefreshController::class, 'store'])->name('cfp.refresh.store');
        Route::get('/{id}', [MenuRefreshController::class, 'getByModule'])->name('cfp.refresh.module');
        Route::get('/getModule', [MenuRefreshController::class, 'getModule'])->name('cfp.refresh.getModule');
        Route::get('/{id}/showModule', [MenuRefreshController::class, 'showModule'])->name('cfp.refresh.showModule');
        Route::get('/menu-refresh/module/{idModule}', [MenuRefreshController::class, 'getMenusByModule'])->name('cfp.refresh.getMenusByModule');

        // Nouvelles routes pour les actions des boutons
        Route::put('/menu-refresh/{menuId}', [MenuRefreshController::class, 'update'])->name('menu.update');
        Route::delete('/menu-refresh/{menuId}', [MenuRefreshController::class, 'destroy'])->name('menu.delete');
    });

    Route::prefix('/cfp/module/programmes')->group(function () {
        Route::get('/menu-refresh/module/{idModule}', [ProgrammeController::class, 'getProgrammes'])->name('cfp.refresh.getMenusByModule'); // controlleur seulement pour la récupération du programme
        Route::get('/{id}', [ModuleProgramContentController::class, 'index']); // pour les contenue
        Route::post('/{id}', [ModuleProgramContentController::class, 'store']);
        Route::get('/{id}/content/{idContent}', [ModuleProgramContentController::class, 'show']);
        Route::put('/{id}/content/{idContent}', [ModuleProgramContentController::class, 'update']);
        Route::delete('/{id}/content/{idContent}', [ModuleProgramContentController::class, 'destroy']);
        Route::get('/', [ModuleProgramContentController::class, 'test']); // pour les contenue
    });

    Route::prefix('contents')->group(function () {
        Route::get('/module/{idModuleContent}', [ContentRefreshController::class, 'index']);
        Route::get('/{id}', [ContentRefreshController::class, 'show']);
        Route::post('/', [ContentRefreshController::class, 'store']);
        Route::get('content/{idContent}/edit', [ContentRefreshController::class, 'edit']);
        Route::put('/{id}', [ContentRefreshController::class, 'update']);
        Route::delete('/{id}', [ContentRefreshController::class, 'destroy']);

        // Gestion fichiers individuels
        Route::post('/{idContent}/files', [ContentRefreshController::class, 'addFile']);
        Route::delete('/files/{idFile}', [ContentRefreshController::class, 'deleteFile']);
    });

    // Company adresse de facturation
    Route::apiResource('companies', CompanyController::class);

    Route::prefix('cfp/bankAccount')->group(function () {
        Route::get('/', [BankAccountController::class, 'index'])->name('cfp.bankAcount.index');
        Route::post('/', [BankAccountController::class, 'store'])->name('cfp.bankAcount.store');
        Route::put('/{id}', [BankAccountController::class, 'update'])->name('cfp.bankAcount.update');
        Route::delete('/{id}', [BankAccountController::class, 'destroy'])->name('cfp.bankAcount.destroy');
    });

    // cfp.profil
    Route::prefix('/cfp/profils')->group(function () {
        Route::get('/', [ProfilController::class, 'indexCfp']);
        Route::get('/user', [ProfilController::class, 'getUserInfo']);
        Route::get('/{idCustomer}/index', [ProfilController::class, 'indexCfp'])->name('cfp.profils.index');
        Route::put('/update', [ProfilController::class, 'update']);
        Route::put('/update-user', [ProfilController::class, 'updateUser']);
        Route::put('/updateLogo', [ProfilController::class, 'updateLogo']);
    });

    // mobileMoney
    Route::prefix('cfp/mobilemoneyAccount')->group(function () {
        // Route::get('/', [MobileMoneyAccountController::class, 'index'])->name('cfp.mobilemoneyAcount.index');
        Route::post('/', [MobileMoneyAccountController::class, 'store'])->name('cfp.mobilemoneyAcount.store');
        Route::put('/{id}', [MobileMoneyAccountController::class, 'update'])->name('cfp.mobilemoneyAcount.update');
        Route::delete('/{id}', [MobileMoneyAccountController::class, 'destroy'])->name('cfp.mobilemoneyAcount.destroy');
    });

    // Agendas
    Route::prefix('cfp/agenda')->group(function () {
        Route::get('/', [AgendaCfpController::class, 'index'])->name('cfp.agendas.index');
        Route::get('/event-groups', [AgendaCfpController::class, 'getEventsGroupBy']);
        Route::get('/event-semaine', [AgendaCfpController::class, 'getEventsSemaine']);
        Route::get('/events', [AgendaCfpController::class, 'getEvents']);
        Route::patch('/events-update-form', [AgendaCfpController::class, 'updateEventForms']);
        Route::get('/events-resources', [AgendaCfpController::class, 'getEventResources']);
        Route::get('/{year}', [AgendaCfpController::class, 'setStatut']);
    });
    Route::prefix('cfp/dispoForm')->group(function () {
        Route::get('/working_days_policy', [WorkingDaysPolicyController::class, 'index']);
        Route::put('/working_days_policy/{id}', [WorkingDaysPolicyController::class, 'update']);
        Route::post('/working_days_policy', [WorkingDaysPolicyController::class, 'createOrUpdate']);
        Route::get('/sessionFormateurUneDate', [WorkingDaysPolicyController::class, 'sessionFormateurUneDate']);
        Route::get('/sessionFormateurDeuxDate', [WorkingDaysPolicyController::class, 'sessionFormateurDeuxDate']);
        Route::get('/dayAvailabilityFormateur', [WorkingDaysPolicyController::class, 'dayAvailabilityFormateur']);
    });
    Route::prefix('cfp/dispo_materiel')->group(function () {
        Route::get('/working_days_policy', [WorkingDaysPolicyController::class, 'index']);
        Route::put('/working_days_policy/{id}', [WorkingDaysPolicyController::class, 'update']);
        Route::post('/working_days_policy', [WorkingDaysPolicyController::class, 'createOrUpdate']);
        Route::get('/sessionmaterielUneDate', [DisponibileMatController::class, 'sessionMaterielUneDate']);
        Route::get('/sessionFormateurDeuxDate', [WorkingDaysPolicyController::class, 'sessionFormateurDeuxDate']);
        Route::get('/dayAvailabilityFormateur', [WorkingDaysPolicyController::class, 'dayAvailabilityFormateur']);
        Route::get('/equipment/status', [DisponibileMatController::class, 'getEquipmentStatusSimple']);
        Route::get('/availability/materiel', [DisponibileMatController::class, 'dayAvailabilityMaterielTimeBased']);
    });

    // Prospections
    Route::prefix('cfp/prospection')->group(function () {
        Route::get('/events', [ProspectionController::class, 'getEvents']);
        Route::patch('/{id}/update-id', [ProspectionController::class, 'updateIdCalendarOpportunity']);
    });

    // customer drawer
    Route::prefix('/cfp')->group(function () {
        Route::get('/dossier-drawer/{idProjet}', [ShowDrawerController::class, 'showDossierDrawer'])->name('cfp.dossier-drawer.index');
        Route::get('/etp-drawer/{idEtp}', [ShowDrawerController::class, 'showEtpDrawer'])->name('cfp.etp-drawer.index');
        Route::get('/apprenant-drawer/{idProjet}', [ShowDrawerController::class, 'showApprenantDrawer'])->name('cfp.apprenant-drawer.index');
        Route::get('/session-drawer/{idProjet}', [ShowDrawerController::class, 'showSessionDrawer'])->name('cfp.session-drawer.index');
        Route::get('/planreperage-drawer/{idProjet}', [ShowDrawerController::class, 'showPLanDeReperageDrawer'])->name('cfp.dossier-drawer.plan');
    });

    // Projets
    Route::prefix('/cfp/projets')->group(function () {
        Route::get('/formateur/{id}/mini-cv', [ProjetController::class, 'getMiniCV']);
        Route::get('/invoice/{idProjet}', [ProjetController::class, 'getInvoiceProject']);
        Route::get('/getFormAdded/{idProjet}', [AgendaCfpController::class, 'getIdFormateurByProject']);
        Route::get('/{idProjet}', [ProjetController::class, 'show'])->name('cfp.projets.show');
        Route::patch('/{idProjet}/confirm', [ProjetController::class, 'confirm']);
        Route::delete('/{idProjet}/destroy', [ProjetController::class, 'destroy'])->name('cfp.projets.destroy');
        Route::post('/{idProjet}/duplicate', [ProjetController::class, 'duplicate'])->name('cfp.projets.duplicate');
        Route::patch('/{idProjet}/cancel', [ProjetController::class, 'cancel']);
        Route::patch('/{idProjet}/close', [ProjetController::class, 'close']);
        Route::patch('/{idProjet}/repport', [ProjetController::class, 'repport']);
        Route::patch('/{idProjet}/update-privacy', [ProjetController::class, 'updatePrivacy']);
        Route::patch('/{idProjet}/update-place', [ProjetController::class, 'updateNbPlace']);
        Route::patch('/{id}/archive', [ProjetController::class, 'makeArchive']);
        Route::patch('/{id}/archive/restore', [ProjetController::class, 'restoreArchive']);
        Route::get('/{id}/apprenants', [ProjetController::class, 'getApprListProjet']);
        Route::post('/{idProjet}/{idFormateur}/form/assign', [ProjetController::class, 'formAssign']);
        Route::delete('/{idProjet}/{idFormateur}/form/assign', [ProjetController::class, 'formRemove']);
        Route::get('/{idProjet}/formateur', [ProjetController::class, 'getFormAdded']);
        Route::get('/{id}/sessions', [ProjetController::class, 'getSessionProject']);
        Route::get('/{id}/get-learner-added', [ProjetController::class, 'getLearnerAddedByProject']);
        Route::get('/{id}/get-learner-by-key', [ProjetController::class, 'getLearnerByKey']);
        Route::get('/all/particular', [ProjetController::class, 'getAllParticular']);
        Route::get('/{id}/get-learner-by-etp', [ProjetController::class, 'getLearnerByEntreprise']);
        Route::get('/{id}/get-learner-by-project', [ProjetController::class, 'getLearnerByProject']);
        Route::get('/{id}/get-entreprise', [ProjetController::class, 'getEntrepriseByProject']);
        Route::get('/{id}/etp-assigned', [ProjetController::class, 'getEntrepriseAssigned']);
        Route::get('/{projectId}/get-total-cost', [ProjetController::class, 'getTotalCost']);
    });

    // Cfp.dossiers
    Route::prefix('cfp/projet/folder-first')->group(function () {
        Route::get('/year/{year}', [DossierController::class, 'getAllDossier']);
        Route::get('/detail/{idDossier}', [DossierController::class, 'getDossierDetail']);
        Route::post('/', [DossierController::class, 'store']);
        Route::post('/upload/document/{idDossier}', [DossierController::class, 'uploadFichier']);
        Route::get('/detail/{idDossier}', [DossierController::class, 'getDossierDetail']);
        Route::post('/document/{idDossier}/{idProjet}', [DossierController::class, 'ajoutProjetInFolder'])->name('dossier.ajouter.fichier.dossier');
        Route::get('/selected/{idProjet}', [DossierController::class, 'getSelectedDossier'])->name('dossier.showSelected');
    });

    // Projets ETP inter
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
        Route::get('/{idEtp}', [ApprenantController::class, 'getApprenantProjets']);
        Route::post('/{idProjet}/{idApprenant}', [ProjetController::class, 'assignLearnerToProject']);
        Route::delete('/{idProjet}/{idApprenant}', [ProjetController::class, 'deassignLearnerToProject']);
        Route::get('/added/{idProjet}', [ApprenantController::class, 'getApprenantAdded']);
    });

    // cfp.seances
    Route::prefix('cfp/seances')->group(function () {
        Route::post('/', [SeanceController::class, 'store']);
        Route::delete('/{idSeance}/delete', [SeanceController::class, 'destroy']);
        Route::patch('/{idSeance}', [SeanceController::class, 'update']);
        Route::post('/updateTypeSeance/{idSeance}', [SeanceController::class, 'updateTypeSeance']);
        Route::get('/last-field', [SeanceController::class, 'getLastFieldVueSeances']);
        Route::get('/{idProjet}/seance-total-time', [SeanceController::class, 'getSeanceAndTotalTime']);
        Route::get('{idSeance}/last-field-seance', [SeanceController::class, 'getFieldVueSeanceOfId']);
        Route::patch('/last-session/update-id', [SeanceController::class, 'updateIdCalendarLastSession']);
        Route::patch('/last-sessions/update-ids', [SeanceController::class, 'updateIdListCalendarSession']);
        Route::get('/all/{idProjet}', [SeanceController::class, 'getAllSeances']);
    });

    Route::patch('/cfp/seance-reports/{seanceId}', [SeanceController::class, 'reportSession']);
    // Modules
    Route::prefix('cfp/modules')->group(function () {
        Route::get('/{idModule}/drawer', [ModuleController::class, 'detailModule']);
        Route::get('/getAllModule', [ModuleController::class, 'getAllModuleCfp']);
    });

    // Salles
    Route::prefix('cfp/salles')->group(function () {
        Route::get('/list', [SalleCfpController::class, 'list']);
        Route::get('/{idEtp}/index', [SalleCfpController::class, 'getAllSalle'])->name('cfp.salles.index');
    });

    // Formateurs
    Route::prefix('cfp/forms')->group(function () {
        Route::get('/', [FormateurController::class, 'getAllForms']);
    });

    Route::prefix('/home')->group(function () {
        Route::get('/api/config', [AgendaCfpController::class, 'getConfigApi']);
    });

    // Catalogues
    Route::prefix('cfp/modules')->group(function () {
        Route::get('/', [CatalogueController::class, 'index']);
        Route::post('/', [CatalogueController::class, 'store']);
        Route::post('/public', [CatalogueController::class, 'storeModulePublic']);
        Route::patch('/{id}/online', [CatalogueController::class, 'makeOnline']);
        Route::patch('/{id}/offline', [CatalogueController::class, 'makeOffline']);
        Route::patch('/{id}/trash', [CatalogueController::class, 'makeTrashed']);
        Route::patch('/{id}/restore', [CatalogueController::class, 'restore']);
        Route::delete('/{id}/destroy', [CatalogueController::class, 'destroy'])->name('cfp.modules.delete');
        Route::get('/{id}/edit', [CatalogueController::class, 'edit'])->name('cfp.modules.edit');
        Route::patch('/{id}', [CatalogueController::class, 'update'])->name('cfp.modules.update');
        Route::post('/{id}/update-image', [CatalogueController::class, 'updateImage'])->name('cfp.modules.update.images');
        Route::get('/status/{status}', [CatalogueController::class, 'getModulesByStatus']);
        Route::get('/{id}/scores', [CatalogueController::class, 'getModuleScores']);

        // Objectifs
        Route::post('/{id}/objectifs', [CatalogueController::class, 'addObjectif']);
        Route::get('/{id}/objectifs', [CatalogueController::class, 'getObjectif']);
        Route::delete('/{id}/objectifs/{idObjectif}', [CatalogueController::class, 'deleteObjectif']);
        Route::get('/{module}/objectifs/{objectif}', [CatalogueController::class, 'showObjectif']);
        Route::put('/{module}/objectifs/{objectif}', [CatalogueController::class, 'updateObjectif']);

        // Prestations
        Route::post('/{id}/prestations', [CatalogueController::class, 'addPrestation']);
        Route::get('/{id}/prestations', [CatalogueController::class, 'getPrestation']);
        Route::delete('/{id}/prestations/{idPrestation}', [CatalogueController::class, 'deletePrestation']);
        Route::get('/{module}/prestations/{prestation}', [CatalogueController::class, 'showPrestation']);
        Route::put('/{module}/prestations/{prestation}', [CatalogueController::class, 'updatePrestation']);

        // Prerequis
        Route::post('/{id}/prerequis', [CatalogueController::class, 'addPrerequis']);
        Route::get('/{id}/prerequis', [CatalogueController::class, 'getPrerequis']);
        Route::delete('/{id}/prerequis/{idPrerequis}', [CatalogueController::class, 'deletePrerequis']);
        Route::get('/{module}/prerequis/{prerequis}', [CatalogueController::class, 'showPrerequis']);
        Route::put('/{module}/prerequis/{prerequis}', [CatalogueController::class, 'updatePrerequis']);

        // Cibles
        Route::post('/{id}/cibles', [CatalogueController::class, 'addCible']);
        Route::get('/{id}/cibles', [CatalogueController::class, 'getCible']);
        Route::delete('/{id}/cibles/{idCible}', [CatalogueController::class, 'deleteCible']);
        Route::get('/{module}/cibles/{cible}', [CatalogueController::class, 'showCible']);
        Route::put('/{module}/cibles/{cible}', [CatalogueController::class, 'updateCible']);

        // Modifie le qualité de progress bar
        Route::get('/{id}/quality', [CatalogueController::class, 'getSumQuality']);

        // evaluation étoile
        Route::get('/get-note/{idProjet}', [CatalogueController::class, 'getNote']);

        // count moudule public by status
        Route::get('/get_module_public_count', [CatalogueController::class, 'getCountModuleByStatus']);

        // get module public by status
        Route::get('/module_public_by_status/{status}', [CatalogueController::class, 'getModulePublicByStatus']);

        // update catalog is public and private
        Route::patch('/make_is_public/{idModule}', [CatalogueController::class, 'makeIsPublic']);
        Route::patch('/make_is_private/{idModule}', [CatalogueController::class, 'makeIsPrivate']);
        Route::patch('/make_is_restore/{idModule}', [CatalogueController::class, 'makeIsRestore']);
    });
    // ModuleRessource
    Route::prefix('cfp/module/ressources')->group(function () {
        // Route::post('/{idModule}', [ModuleRessourceController::class, 'store'])->name('cfp.module.ressources.store');
        Route::get('/{id}', [ModuleRessourceController::class, 'index'])->name('cfp.module.ressources.index');
        Route::post('/{id}', [ModuleRessourceController::class, 'store'])->name('cfp.module.ressources.store');
        Route::delete('/{idModuleRessource}', [ModuleRessourceController::class, 'destroy'])->name('cfp.module.ressources.destroy');
        Route::get('/modules/{idModule}/ressources/count', [ModuleRessourceController::class, 'countByModule']);
        Route::get('/{idModuleRessource}/download', [ModuleRessourceController::class, 'download'])->name('cfp.module.ressources.download');
    });
    // Programme
    Route::prefix('cfp/module-programmes')->group(function () {
        Route::post('/{id}', [ProgrammeController::class, 'store'])->name('cfp.programmes.store');
        Route::get('/{id}', [ProgrammeController::class, 'getProgrammes']);
        Route::delete('/{id}/{idProgramme}', [ProgrammeController::class, 'destroy'])->name('programmes.destroy');
        Route::get('/{id}/{idProgramme}/edit', [ProgrammeController::class, 'edit']);
        Route::put('/{id}/{idProgramme}/update', [ProgrammeController::class, 'update'])->name('cfp.programmes.update');
    });
    // Programmes contnues
    Route::prefix('/cfp/module/programmes')->group(function () {
        Route::get('/{id}', [ModuleProgramContentController::class, 'index']);
        Route::post('/{id}', [ModuleProgramContentController::class, 'store']);
        Route::get('/{id}/content/{idContent}', [ModuleProgramContentController::class, 'show']);
        Route::put('/{id}/content/{idContent}', [ModuleProgramContentController::class, 'update']);
        Route::delete('/{id}/content/{idContent}', [ModuleProgramContentController::class, 'destroy']);
    });

    Route::prefix('/cfp/security')->group(function () {
        Route::put('change-password', [SecurityController::class, 'changePassword']);
    });

    // dossier

    Route::prefix('cfp/dossier')->group(function () {
        Route::get('/showAllDossier', [DossierController::class, 'getAllDossier'])->name('dossier.showAll');
        Route::post('/ajouter', [DossierController::class, 'store'])->name('dossier.store');
        Route::get('/show', [DossierController::class, 'showByIdCfp'])->name('dossier.show');
        Route::post('/update/{idDossier}', [DossierController::class, 'edit'])->name('dossier.update');
        Route::delete('/delete/{idDossier}', [DossierController::class, 'destroy'])->name('dossier.destroy');
        Route::get('/getDossierDetail/{idDossier}', [DossierController::class, 'getDossierDetail'])->name('dossier.getDossierDetail');
        Route::get('/search-global', [DossierController::class, 'searchGlobal']);

        Route::get('/document/liste/{idDossier}', [DossierController::class, 'getFichier'])->name('dossier.liste.fichier');
        Route::post('/document/ajouter/{idDossier}/{idProjet}', [DossierController::class, 'ajoutProjetInFolder'])->name('dossier.ajouter.fichier.dossier');
        Route::get('/document/show/{idDossier}', [DossierController::class, 'showByDossier'])->name('dossier.show.fichier');
        Route::post('/document/edit/{idDocument}', [DossierController::class, 'editDocument'])->name('dossier.editDocument');
        Route::post('/document/delete/{idDocument}', [DossierController::class, 'destroyDocument'])->name('dossier.destroyDocument');
        Route::post('/document/supprimer/{idDossier}/{idProjet}', [DossierController::class, 'supprimeProjetInFolder'])->name('dossier.supprimer.fichier.dossier');
        Route::post('/document/upload/{idDossier}', [DossierController::class, 'uploadFichier'])->name('dossier.uploadFichier');
        Route::get('/document/projets/{idProjet}', [DossierController::class, 'getDocumentProjet'])->name('dossier.getDocumentProjet');
        Route::get('/document/section/', [DossierController::class, 'getSectionDocument'])->name('dossier.getDocumentSectionDocument');
        Route::get('/document/type/{idSectionDocument}', [DossierController::class, 'getTypeDocument'])->name('dossier.getTypeDocument');
        Route::get('/document/download/{idDocument}', [DossierController::class, 'downloadDocument'])->name('document.download');
        Route::get('/document/view/{idDocument}', [DossierController::class, 'viewDocument'])->name('document.view');

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
    });

    // Reporting
    Route::prefix('cfp/reporting')->group(function () {
        // -- formation
        Route::get('/exportXl', [ReportingController::class, 'exportXl'])->name('exportXl');
        Route::get('/exportPdf', [ReportingController::class, 'exportPdf'])->name('exportPdf');
        Route::get('/formation', [ReportingController::class, 'formation'])->name('ReportingFormation');
        Route::post('/filterFormation', [ReportingController::class, 'filterFormation'])->name('reporting.filter.formation');
        Route::get('/{idProjet}/detail', [ProjectDetailController::class, 'show'])->name('cfp.projets.show');

        // -- apprenant
        Route::get('/exportXlApp', [historiqueController::class, 'exportXlApp'])->name('exportXlApp');
        Route::get('/exportPdfApp', [historiqueController::class, 'exportPdfApp'])->name('exportPdfApp');
        Route::get('/apprenant', [historiqueController::class, 'apprenant'])->name('reporting.filter.apprenant');
        Route::get('/learner', [historiqueController::class, 'getLearner']);
        Route::get('/formation/exportPdf/{id}', [PdfController::class, 'exportPdf'])->name('formation.exportPdf');
        Route::get('/formation_inter/detail/{id}/{idProjet}', [historiqueController::class, 'getDetailFormationInter'])->name('formationInter.detail');
        // Route::get('/learner/project', [historiqueController::class, 'getProjectLearner'])->name('reporting.project-customer');
        Route::get('/historique/search/{name}/name', [historiqueController::class, 'searchName']);

        // -- client
        Route::get('/exportXlCl', [RepportingClientController::class, 'exportXlCl'])->name('exportXlCl');
        Route::get('/exportPdfCl', [RepportingClientController::class, 'exportPdfCl'])->name('exportPdfCl');
        Route::get('/client_list', [RepportingClientController::class, 'getCustomer']);
        Route::post('/search/customer', [RepportingClientController::class, 'searchByCustomer'])->name('reporting.customer.search');
        Route::get('/etp/search/{name_etp}/name', [RepportingClientController::class, 'searchEtp'])->name('etp.search.name');

        // -- cours
        Route::get('/cours', [CoursController::class, 'cours'])->name('reporting.filter.cours');
        Route::get('cours/{id}', [CoursController::class, 'searchByModule']);

        // -- chiffre d'affaire
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

        Route::get('/month-simple/{year}', [ChiffreAffaireRepportingCfp::class, 'caMonthYearSimple']);
        // Route d'export Excel
        Route::get('/month-export/{year}', [ChiffreAffaireRepportingCfp::class, 'caMonthYearExport']);
    });

    // Projet
    Route::prefix('cfp/projets')->group(function () {
        Route::get('/', [ProjetController::class, 'getAllProject'])->name('cfp.projets.index');
        Route::get('/{status}', [ProjetController::class, 'getProjectByStatus'])->name('cfp.projets.index');
        Route::get('/getProjectByStatus/status', [ProjetController::class, 'getAllProjectStatus']);
        Route::get('/getProjetByIdFormateur/{idFormateur}/formateur', [ProjetController::class, 'getProjetByIdFormateur']);
        Route::get('/{id}/detail', [ProjetController::class, 'show'])->name('cfp.projets.show');
        Route::get('/{id}/base-information', [ProjetController::class, 'getInformationBaseProject'])->name('cfp.projets.show');
        Route::post('/', [ProjetController::class, 'store'])->name('cfp.projets.store');
        Route::get('/list', [ProjetController::class, 'getProjetList'])->name('cfp.projets.list');
        Route::post('/photos/{idProjet}', [ProjetController::class, 'uploadPhotoMomentum'])->name('uploadphoto.momentum');
        Route::delete('/photos/{idProjet}/{url}', [ProjetController::class, 'destroyPhoto'])->name('deletephoto.destroy');
        Route::get('/photos/{idProjet}/detail/momentum', [ProjetController::class, 'showmomentum'])->name('cfp.projets.showmomentum');
        Route::post('/{idProjet}/duplicate', [ProjetController::class, 'duplicate'])->name('cfp.projets.duplicate');
        Route::patch('/{idProjet}/update-date', [ProjetController::class, 'updateDate'])->name('cfp.projets.updateDate');
        Route::patch('/{idProjet}/update-module', [ProjetController::class, 'updateModule'])->name('cfp.projets.updateModule');
        Route::post('/{idProjet}/update-numeroBc', [ProjetController::class, 'updateNumeroBc']);
        Route::patch('/{idProjet}/cancel', [ProjetController::class, 'cancel']);
        Route::patch('/{idProjet}/repport', [ProjetController::class, 'repport']);
        Route::patch('/{idProjet}/close', [ProjetController::class, 'close']);
        Route::patch('/{idProjet}/confirm', [ProjetController::class, 'confirm']);
        Route::delete('/{idProjet}/destroy', [ProjetController::class, 'destroy'])->name('cfp.projets.destroy');
        Route::patch('/{idProjet}/restore', [ProjetController::class, 'restore'])->name('projets.cfp.restore');
        Route::get('/formateur/{id}/mini-cv', [ProjetController::class, 'getMiniCV']);
        Route::patch('/{idProjet}/updatePrivacy', [ProjetController::class, 'updatePrivacy']);
        Route::patch('/{idProjet}/trash', [ProjetController::class, 'trash'])->name('projets.cfp.trash');
        Route::get('/detailProjetCfpPdf/{id}', [ProjetController::class, 'detailProjetCfpPdf'])->name('cfp.projets.detailProjetCfpPdf');
        Route::patch('/{id}/archive', [ProjetController::class, 'makeArchive']);
        Route::patch('/{id}/restoreArchive', [ProjetController::class, 'restoreArchive']);
        Route::get('/{idProjet}/note', [ProjetController::class, 'getNote']);
        Route::post('/notes', [ProjetController::class, 'getNotesBatch']);

        // Frais
        Route::get('/{idProjet}/{isEtp}/frais', [ProjetController::class, 'fraisdetails'])->name('cfp.projets.fraisdetails');
        Route::post('/{idProjet}/{idFrais}/{isEtp}/fraisprojet/assign', [ProjetController::class, 'fraisAssign'])->name('cfp.projets.fraisAssign');
        Route::post('/update-frais', [ProjetController::class, 'updateFrais'])->name('cfp.projets.updateFrais');
        Route::post('/{idProjet}/{idFraisProjet}/delete-frais', [ProjetController::class, 'fraisRemove'])->name('cfp.projets.deleteFrais');
        Route::post('/{idProjet}/total-frais', [ProjetController::class, 'fraisTotal'])->name('cfp.projets.fraisTotal');
        Route::get('/{idFraisProjet}/idProjet', [ProjetController::class, 'getIdProjetByIdFraisProjet'])->name('cfp.projets.getIdProjet');
        Route::delete('/{idProjet}/{isEtp}/removeEtpFraisProjet', [ProjetController::class, 'removeEtpFraisProjet'])->name('cfp.projets.removeEtpFraisProjet');
        Route::get('/fermeturefrais', [ProjetController::class, 'fermeturefrais'])->name('cfp.projets.fermeturefrais');
        Route::post('/{idProjet}/update-taxe', [ProjetController::class, 'updateTaxe']);
        Route::get('/frais-formations/all', [CfpInviteEtp::class, 'getAllFrais']);

        // Formateurs
        Route::get('/{idProjet}/form/assign', [ProjetController::class, 'getFormAssign'])->name('cfp.projets.form.assign.index');
        Route::post('/{idProjet}/{idFormateur}/form/assign', [ProjetController::class, 'formAssign']);
        Route::get('/{idProjet}/getFormAdded', [ProjetController::class, 'getFormAdded']);
        Route::delete('/{idProjet}/{idFormateur}/form/assign', [ProjetController::class, 'formRemove']);
        Route::get('/formateur/all', [FormateurController::class, 'getAllForms']);

        // entreprises

        // get entreprise by key
        Route::get('/entreprise/key', [CfpInviteEtp::class, 'getAllEtpsByKey']);

        Route::get('/{idProjet}/etp/assign', [ProjetController::class, 'getEtpAssign']);
        Route::get('/entreprise/{idEtp}', [ProjetController::class, 'getProjetsByIdEtp']);
        Route::patch('/{idProjet}/{idEtp}/assign_etp', [ProjetController::class, 'addEtpInProject']);
        Route::delete('/{projectId}/{etpId}/deassign_etp', [ProjetController::class, 'deassignEtp']);
        Route::patch('/{idProjet}/{idEtp}/etp/assign', [ProjetController::class, 'etpAssign']);
        Route::get('/{idProjet}/mainGetIdEtp', [ProjetController::class, 'mainGetIdEtp']);
        Route::get('/{idProjet}/mainGetIdModule', [ProjetController::class, 'mainGetIdModule']);
        Route::patch('/{idProjet}/date/assign', [ProjetController::class, 'dateAssign']);
        Route::get('/entreprises/all', [CfpInviteEtp::class, 'getAllEtps']);

        // Module et programmes
        Route::patch('/{idProjet}/{idModule}/module/assign', [ProjetController::class, 'moduleAssign']);
        Route::get('/{idModule}/getProgrammeProject', [ProjetController::class, 'getProgramme']);
        Route::get('/{idModule}/getModuleRessourceProject', [ProjetController::class, 'getModuleRessourceProject']);

        // Financement
        Route::patch('/update/financement/{idProjet}', [ProjetController::class, 'updateFinancement'])->name('cfp.projets.updateFinancement');
        Route::patch('/{idProjet}/update/price', [ProjetController::class, 'updatePrice'])->name('cfp.projets.updatePrice');

        // Salle
        Route::patch('/{idProjet}/{idSalle}/salle/assign', [LieuxController::class, 'roomAssignInProject']);
        Route::delete('/{idProjet}/{idSalle}/salle/deassign', [LieuxController::class, 'roomDeassignInProject']);
        Route::get('/{idProjet}/getSalleAdded', [ProjetController::class, 'getSalleAdded']);

        Route::patch('/{idProjet}/updateProjet', [ProjetController::class, 'updateProjet']);
        Route::patch('/{idProjet}/updateNbPlace', [ProjetController::class, 'updateNbPlace']);

        // filtres
        Route::get('/filter/getDropdownItem', [ProjetController::class, 'getDropdownItem']);
        Route::get('/filter/items', [ProjetController::class, 'filterItems']);
        Route::get('/filter/item', [ProjetController::class, 'filterItem']);

        // Villes
        Route::get('/getVille', [ProjetController::class, 'getVille']);
        Route::patch('/{idProjet}/update', [ProjetController::class, 'updateVille']);

        Route::post('/{idProjet}/{idEtp}', [ProjetController::class, 'etpAssignInter']);

        // Modalite
        Route::get('/getModalite/all', [ProjetController::class, 'getModalite']);
        Route::patch('/{idProjet}/update/modalite', [ProjetController::class, 'updateModalite'])->name('cfp.projets.updateModalite');

        // Particuliers
        Route::get('/parts/getAllParts', [ProjetController::class, 'getAllParts'])->name('parts.getAllParts');
        Route::post('/{idProjet}/{idParticulier}/part/assign', [ProjetController::class, 'assignPart']);
        Route::get('/{idProjet}/getPartAdded', [ProjetController::class, 'getPartAdded']);
        Route::delete('/{idProjet}/{idParticulier}/part/assign', [ProjetController::class, 'unassignPart'])->name('parts.unassign');

        Route::patch('/{id}/linkInvitation', [ProjetController::class, 'linkInvitation']);
        Route::get('/{id}/getApprListProjet', [ProjetController::class, 'getApprListProjet']);
        Route::get('/{id}/getFormProject', [ProjetController::class, 'getFormProject']);
        Route::get('/{id}/getSessionProject', [ProjetController::class, 'getSessionProject']);
        Route::get('/getEtpClient/{id}/{idCfp_inter}', [ProjetController::class, 'getEtpProjectInter']);

        Route::get('/getVille/all', [ProjetController::class, 'getVille']);
        Route::patch('/{idProjet}/update', [ProjetController::class, 'updateVille']);

        //bon de commande
        Route::get('/purchase-order/{projectId}', [ProjetController::class, 'geePurchaseOrderByProject']);
        Route::put('/{projectId}/update-order', [ProjetController::class, 'updatePurchaseOrder']);
    });

    Route::get('/cfp/projet/module-first', [ModuleController::class, 'getFirstModules']);
    Route::get('/cfp/projet/module-first/key', [ModuleController::class, 'getAllModulesByKey']);

    // reservation
    Route::prefix('/cfp/rsv')->group(function () {
        Route::get('/', [ReservationController::class, 'allRsv']);
        Route::get('/{id}', [ReservationController::class, 'showReservationById']);
    });

    // Evaluation
    Route::prefix('cfp/evaluation')->group(function () {
        Route::get('/list', [ProjetController::class, 'getProjectList'])->name('cfp.projets.list');
        Route::get('/projets', [EvaluationChaudController::class, 'getProjectForEvaluation']);
        Route::get('/filters', [EvaluationChaudController::class, 'getFilters']);
        Route::get('/projet/{projectId}', [EvaluationChaudController::class, 'getLearnerAdded']);
    });

    // Apprenant_project
    Route::prefix('cfp/projet/apprenants')->group(function () {
        Route::get('/getApprenantAdded/{idProjet}', [ApprenantController::class, 'getApprenantAdded']);
        Route::get('/getApprAddedInter/{idProjet}', [ApprenantController::class, 'getApprAddedInter']);
        Route::get('/getApprenantProjets/{idEtp}', [ApprenantController::class, 'getApprenantProjets']);
    });

    Route::prefix('cfp/projet/evaluation')->group(function () {
        Route::post('/chaud', [EvaluationController::class, 'store'])->name('cfp.evaluation');
        Route::patch('/editEval', [EvaluationController::class, 'editEval'])->name('cfp.editEvaluation');
        Route::get('/checkEval/{idProjet}/{idEmploye}', [EvaluationController::class, 'checkEval']);
        Route::get('/project-evaluations/{idProjet}', [EvaluationController::class, 'getProjectEvaluations']);
    });

    // EvaluationChaud
    Route::get('evaluation/cfp/chaud/{idProjet}/{idEmploye}', [EvaluationController::class, 'evalCfp']);
    Route::get('evaluation/cfp/chaud/{idProjet}/{idEmploye}/pdf', [EvaluationController::class, 'pdfForm']);

    // Evaluation apprenant
    Route::post('/evaluation/aprrenant', [EvaluationController::class, 'save'])->name('evaluation.apprenant');
    Route::get('/evaluation/aprrenant/{idEmploye}/{idProjet}', [EvaluationController::class, 'get']);

    // CFP
    Route::prefix('cfp/evaluations/froids')->group(function () {
        Route::get('/', [EvaluationController::class, 'indexFroid'])->name('cfp.evaluations.froids.index');
        Route::post('/{idProjet}', [EvaluationController::class, 'sendEvaluation'])->name('cfp.evaluations.froids.send');
        Route::get('/apprenants/{idProjet}', [EvaluationController::class, 'getApprenants']);
        Route::get('/detail/{idProjet}', [EvaluationController::class, 'getApprenantByProjectResult']);
        Route::get('/result/{idProjet}/{idEmploye}', [EvaluationController::class, 'apprenantEvaluationResult'])->name('cfp.evaluations.result.apprenant.pdf');
    });

    // Evaluation-froid
    Route::controller(EvaluationController::class)->prefix('cfp/avis')->group(function () {
        Route::get('/', 'indexAvis')->name('cfp.avis.index');
    });

    // Apprenants
    Route::prefix('cfp/apprenants')->group(function () {
        Route::get('/entreprise', [ApprenantController::class, 'getEtps'])->name('cfp.apprenants.entreprise');
        Route::get('/', [ApprenantController::class, 'index'])->name('cfp.apprenants.index');
        Route::post('/', [ApprenantController::class, 'addEmp'])->name('cfp.apprenants.store');
        Route::get('/index', [ApprenantController::class, 'getApprenants'])->name('cfp.apprenants.getApprenants');
        Route::get('/{id}/edit', [ApprenantController::class, 'edit'])->name('cfp.apprenants.edit');
        Route::put('/update/learner/{id}', [ApprenantController::class, 'update']);
        Route::get('/search/{name}/name', [ApprenantController::class, 'searchName'])->name('cfp.apprenants.search.name');
        Route::get('/search/getEtpFilter', [ApprenantController::class, 'getEtpFilter'])->name('cfp.apprenants.search.getEtpFilter');
        Route::get('/search/{idEtp}/getEmpFiltered', [ApprenantController::class, 'getEmpFiltered'])->name('cfp.apprenants.search.getEmpFiltered');
        Route::post('/{idApprenant}/updatePhoto', [ApprenantController::class, 'updateImageAppr'])->name('cfp.apprenants.updateImageAppr');
        Route::get('/filter/dropdown', [ApprenantController::class, 'getDropdownItem'])->name('cfp.apprenants.filter.getDropdownItem');
        Route::get('/filter/items', [ApprenantController::class, 'filterItems']);
        Route::get('/filter/item', [ApprenantController::class, 'filterItem']);
        Route::post('/excel', [ApprenantController::class, 'addEmpExcel'])->name('cfp.apprenants.addEmpExcel');
        Route::delete('/{id}', [ApprenantController::class, 'destroy'])->name('cfp.apprenants.destroy');
    });

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

    // Formateurs
    Route::prefix('cfp/formateurs')->group(function () {
        Route::get('/', [FormateurController::class, 'index'])->name('cfp.formateur.index');
        Route::post('/formateurs/{idFormateur}/toggle', [FormateurController::class, 'toggleActive']);
        Route::post('/', [FormateurController::class, 'invite'])->name('cfp.formateur.store');
        Route::get('/{id}/edit', [FormateurController::class, 'edit']);
        Route::patch('/{id}', [FormateurController::class, 'update']);
        // route pour mettre actif ou desactif un formateur
        Route::patch('/updateActifForm/{id}', [FormateurController::class, 'updateFormteurActif']);
        Route::post('/{id}/photos', [FormateurController::class, 'updateImageForm'])->name('cfp.formateur.updateImageForm');
        Route::delete('/{id}', [FormateurController::class, 'destroy'])->name('cfp.formateur.delete');
    });
    // qcm
    Route::prefix('qcm')->group(function () {
        // Route::post('/invitations/cfp', [QcmInvitationCfpController::class, 'store_invitation'])->name('qcm.invitations.cfp.store');

        // Routes pour CRUD QCM
        Route::get('/create', [QcmController::class, 'create_qcm'])->name('create.qcm.form'); // Route menant au formulaire de création d'un QCM
        Route::post('/store', [QcmController::class, 'storeQcm'])->name('store.qcm'); // Route mettre la création du qcm effective
        Route::get('/{id}/edit', [QcmController::class, 'edit_qcm'])->name('qcm.edit'); // Route vers le formulaire pour mettre à jour un QCM
        Route::post('/{id}', [QcmController::class, 'update_qcm'])->name('qcm.update'); // Route pour mettre la mise à jour effective
        Route::delete('/{id}/delete', [QcmController::class, 'destroy_qcm'])->name('qcm.destroy'); // Route pour supprimer un qcm avec ses questions et réponses
        Route::post('/{id}/update-status', [QcmController::class, 'updateStatus'])->name('qcm.update.status'); // Route pour le toggle button des qcm pour les mettrent actif ou non actif
        // Routes pour CRUD QCM
        // Routes pour les barèmes
        Route::get('/bareme/create/{id}', [QcmBaremeController::class, 'create_qcm_bareme'])->name('qcm.bareme.create'); // Route vers la vue pour créer / modifier le barème d'un qcm
        Route::get('/get/{id}', [QcmBaremeController::class, 'getBaremes']); // Route pour avoir les barèmes d'un qcm
        Route::get('/qcm_bareme/{id}', [QcmBaremeController::class, 'getBareme']); // Appelée directement dans la vue "create_qcm_bareme"
        Route::post('/qcm-bareme/store/{id}', [QcmBaremeController::class, 'storeQcmBareme']); // Appelée directement dans la vue "create_qcm_bareme"
        Route::post('/qcm-bareme/update/{id}', [QcmBaremeController::class, 'updateQcmBareme']); // Appelée directement dans la vue "create_qcm_bareme"
        Route::delete('/qcm-bareme/delete/{id}', [QcmBaremeController::class, 'deleteQcmBareme']); // Appelée directement dans la vue "create_qcm_bareme"
        // Routes pour les barèmes
    });

    // Sous-traitant
    Route::get('/sub-contractors/cfp', [SubContractorController::class, 'getCfps']);
    Route::get('/sub-contractors/index', [SubContractorController::class, 'index']);
    Route::post('/sub-contractors/send-invitations', [SubContractorController::class, 'addSubcontractor']);
    Route::post('/sub-contractors/create', [SubContractorController::class, 'addNewSubcontractor']);
    Route::prefix('cfp/subcontractors')->group(function () {
        Route::get('/', [SubContractorController::class, 'index']);
        Route::get('/{id}/edit', [SubContractorController::class, 'edit']);
        Route::post('/', [SubContractorController::class, 'store']);
        Route::get('/{idProjet}/getAssign', [SubContractorController::class, 'getAssign']);
        Route::post('/assign/{idProjet}/{idSubContractor}', [SubContractorController::class, 'assign']);
        Route::delete('/assign/{idSubContractor}', [SubContractorController::class, 'removeAssign']);
    });

    // TESTING CENTER CFP

    Route::prefix('qcm')->group(function () {
        Route::post('/invitations/cfp', [QcmInvitationCfpController::class, 'store_invitation'])->name('qcm.invitations.cfp.store');

        // Routes pour CRUD QCM
        Route::get('/create', [QcmController::class, 'create_qcm'])->name('create.qcm.form'); // Route menant au formulaire de création d'un QCM
        Route::post('/store', [QcmController::class, 'storeQcm'])->name('store.qcm'); // Route mettre la création du qcm effective
        Route::get('/{id}/edit', [QcmController::class, 'edit_qcm'])->name('qcm.edit'); // Route vers le formulaire pour mettre à jour un QCM
        Route::post('/{id}', [QcmController::class, 'update_qcm'])->name('qcm.update'); // Route pour mettre la mise à jour effective
        Route::delete('/{id}/delete', [QcmController::class, 'destroy_qcm'])->name('qcm.destroy'); // Route pour supprimer un qcm avec ses questions et réponses
        Route::post('/{id}/update-status', [QcmController::class, 'updateStatus'])->name('qcm.update.status'); // Route pour le toggle button des qcm pour les mettrent actif ou non actif
        // Routes pour CRUD QCM
        // Routes pour les barèmes
        Route::get('/bareme/create/{id}', [QcmBaremeController::class, 'create_qcm_bareme'])->name('qcm.bareme.create'); // Route vers la vue pour créer / modifier le barème d'un qcm
        Route::get('/get/{id}', [QcmBaremeController::class, 'getBaremes']); // Route pour avoir les barèmes d'un qcm
        Route::get('/qcm_bareme/{id}', [QcmBaremeController::class, 'getBareme']); // Appelée directement dans la vue "create_qcm_bareme"
        Route::post('/qcm-bareme/store/{id}', [QcmBaremeController::class, 'storeQcmBareme']); // Appelée directement dans la vue "create_qcm_bareme"
        Route::post('/qcm-bareme/update/{id}', [QcmBaremeController::class, 'updateQcmBareme']); // Appelée directement dans la vue "create_qcm_bareme"
        Route::delete('/qcm-bareme/delete/{id}', [QcmBaremeController::class, 'deleteQcmBareme']); // Appelée directement dans la vue "create_qcm_bareme"
        // Routes pour les barèmes
    });
    // Routes Resultats
    Route::get('/accueilCfp', [HomeController::class, 'indexCfp'])->name('accueilCfp');
    Route::get('/qualiopiCfp', [QualiopiController::class, 'qualiopiCfp'])->name('accueilCfp');

    Route::prefix('/cfp/skill-matrix')->group(function () {
        Route::get('/', [SkillMatrixController::class, 'index']);
        Route::get('/projects/{idProjet}', [SkillMatrixController::class, 'show']);
        Route::post('/projects/{idProjet}/employes/{idEmploye}/skills/{skillId}', [SkillMatrixController::class, 'store']);
        Route::put('/projects/{idProjet}/employes/{idEmploye}/skills/{skillId}/update', [SkillMatrixController::class, 'update']);
        Route::get('/projects/{idProjet}/skill-levels', [SkillMatrixController::class, 'getSkillLevel']);
    });

    Route::prefix('/cfp/module_skills')->group(function () {
        Route::get('/', [ModuleSkillController::class, 'getModuleCfp']);
        Route::get('/{id}/skills', [ModuleSkillController::class, 'index']);
        Route::post('/{id}/skills', [ModuleSkillController::class, 'store']);
        Route::put('/{id}/skills/{idSkill}/update', [ModuleSkillController::class, 'edit']);
        Route::delete('/{id}/skills/{idSkill}', [ModuleSkillController::class, 'destroy']);
    });

    // Referent (utilisateurs)

    Route::prefix('cfp/referents')->group(function () {
        Route::get('/', [EmployecfpController::class, 'index'])->name('cfp.referents.index');
        Route::get('/{idEmploye}/edit', [EmployecfpController::class, 'edit']);
    });

    // Loyalty Rewards
    Route::prefix('/cfp/rewards')->group(function () {
        Route::get('/', [RewardsController::class, 'index']);
        Route::get('/allRewards', [RewardsController::class, 'getAllRewardsCfp']);
        Route::get('/edit/{id}', [RewardsController::class, 'edit']);
        Route::post('/', [RewardsController::class, 'store']);
        Route::delete('/delete/{id}', [RewardsController::class, 'destroy']);
    });

    // Lieux
    Route::get('cfp/lieux', [LieuxController::class, 'index'])->name('cfp.lieux.index');
    Route::get('cfp/rooms-cfp', [LieuxController::class, 'getRoomCfp']);
    Route::get('cfp/room/{projectId}', [LieuxController::class, 'getRoomByProject']);
    Route::get('cfp/listOfPlaces', [LieuxController::class, 'listOfPlaces']); // efa vita
    Route::get('cfp/lieux/{id}', [LieuxController::class, 'search'])->name('cfp.lieux.search'); // efa vita
    Route::get('cfp/lieuxsearch', [LieuxController::class, 'searchNoId']); // efa vita
    Route::post('cfp/lieux', [LieuxController::class, 'store'])->name('cfp.lieux.store');
    Route::post('cfp/place/room', [LieuxController::class, 'storePlaceAndRoom'])->name('cfp.lieux.store'); // efa vita
    Route::delete('cfp/lieux_delete/{id}', [LieuxController::class, 'deleteLieu']); // efa vita
    Route::get('cfp/lieux/getEtps', [LieuxController::class, 'getAllEtps']); // efa vita

    // Salles
    Route::get('cfp/salles', [SalleController::class, 'index'])->name('cfp.salles.index'); // efa vita
    Route::get('cfp/listOfPlaces', [LieuxController::class, 'listOfPlaces']);
    Route::post('cfp/salles', [SalleController::class, 'store'])->name('cfp.salles.store'); // efa vita
    Route::delete('cfp/salles/{id}', [SalleController::class, 'destroy'])->name('cfp.salles.destroy'); // efa vita
    Route::get('cfp/getSalleDetails', [SalleController::class, 'getSalleDetails']);
    Route::get('cfp/getSalleDetailsByLieu/{idLieu}', [SalleController::class, 'getSalleDetailsByLieu']);
    Route::post('cfp/updateSalleLieu', [SalleController::class, 'updateSalleLieu'])->name('updateAllLieuxSalles'); // efa vita
    Route::post('cfp/updateNameSalle/{idSalle}', [SalleController::class, 'updateNameSalle'])->name('cfp.salles.updateName'); // efa vita
    Route::post('cfp/imgSalles/{idSalle}/update', [SalleController::class, 'storeImgSalle']); // efa vita

    // Contacts(Lieu et Salle)
    Route::prefix('cfp/contacts')->group(function () {
        Route::get('/', [ContactController::class, 'index'])->name('cfp.contact.index'); // efa vita
        Route::post('/', [ContactController::class, 'store'])->name('cfp.contact.store'); // efa vita
        Route::patch('{idContact}/update', [ContactController::class, 'update'])->name('cfp.contact.update'); // efa vita
        Route::delete('/{idContact}/delete', [ContactController::class, 'destroy'])->name('cfp.contact.destroy'); // efa vita
    });

    // Invoice Contacts
    Route::prefix('invoice-contacts')->group(function () {
        Route::get('/', [InvoiceContactController::class, 'index']);
        Route::get('/{id}', [InvoiceContactController::class, 'show']);
        Route::post('/', [InvoiceContactController::class, 'store']);
        Route::put('/{id}', [InvoiceContactController::class, 'update']);
        Route::delete('/{id}', [InvoiceContactController::class, 'destroy']);
        Route::get('/getContact/{id}', [InvoiceContactController::class, 'getContactClientById']);
    });

    Route::prefix('cfp/invoice-account')->group(function () {
        Route::get('/', [InvoiceAccountController::class, 'index']);
    });

    // Invoice Contacts
    Route::prefix('cfp/bc-contacts')->group(function () {
        Route::get('/', [BcContactController::class, 'index']);
        Route::get('/{id}', [BcContactController::class, 'show']);
        Route::post('/', [BcContactController::class, 'store']);
        Route::put('/{id}', [BcContactController::class, 'update']);
        Route::delete('/{id}', [BcContactController::class, 'destroy']);
    });

    Route::prefix('cfp/bon-commande')->group(function () {
        Route::get('/', [BonCommandeController::class, 'index']);
        Route::get('/getBonCommandByEtp/{etpId}', [BonCommandeController::class, 'getBonCommandByEtp']);
        Route::get('/{id}', [BonCommandeController::class, 'show']);
        Route::post('/', [BonCommandeController::class, 'store']);
        Route::put('/{id}', [BonCommandeController::class, 'update']);
        Route::delete('/{id}', [BonCommandeController::class, 'destroy']);
        Route::get('/getDevis', [BonCommandeController::class, 'getDevisByEtp']);
        Route::get('/getAllEtpDevis', [BonCommandeController::class, 'getAllEtpDevis']);
        Route::post('/{idBc}/changeStatus/{idStatus}', [BonCommandeController::class, 'changeStatus']);
        Route::get('/{idBc}/projet', [BonCommandeController::class, 'getProjetsByBonCommande']);
        Route::get('/getBcByIdEtp/{idEtp}', [BonCommandeController::class, 'getBcByIdEtp']);
        Route::get('/purchase_order_by_etp', [BonCommandeController::class, 'getPurchaseOrderByEtp']);
    });
    Route::prefix('bon-commandes/doc')->group(function () {
        Route::get('/{idBC}/documents', [BonCommandeController::class, 'getDocuments']);
        Route::get('/{idBC}/first-document', [BonCommandeController::class, 'getFirstDocument']);
        Route::post('/{idBC}/upload-document', [BonCommandeController::class, 'uploadDocument']);
        Route::get('/{idDocument}/download', [BonCommandeController::class, 'downloadDocument']);
        Route::delete('/{idDocument}/documents', [BonCommandeController::class, 'deleteDocument']);
        Route::get('/{id}/preview', [BonCommandeController::class, 'previewDocument'])->name('bc_document.preview');
    });

    // start Materials with projects
    Route::get('/cfp/materials', [MaterialController::class, 'index']);
    Route::post('/cfp/materials', [MaterialController::class, 'store']);
    Route::get('/cfp/materials/{id}', [MaterialController::class, 'show']);
    Route::patch('/cfp/materials/{id}', [MaterialController::class, 'update']);
    Route::delete('/cfp/materials/{id}', [MaterialController::class, 'destroy']);

    // mila [projectId en URI params ito index ito]
    Route::get('/cfp/project-materials/{projectId}', [ProjectMaterialController::class, 'getMaterialsByProject']);
    Route::get('/cfp/materials-drawer/{idProjet}', [ProjectMaterialController::class, 'materialDrawer']);
    Route::post('/cfp/project-materials', [ProjectMaterialController::class, 'store']);
    Route::put('/cfp/project-materials/update-number', [ProjectMaterialController::class, 'updateNumber']);
    // mila [projectId & materialId en URI params ito destroy ito]
    Route::delete('/cfp/project-materials', [ProjectMaterialController::class, 'destroy']);
    // end Materials with projects

    // MARKETPLACE DETAIL CFP
    Route::prefix('cfp/marketplace')->group(function () {
        Route::get('/espace', [SectionController::class, 'getInfoProfil']);
        Route::get('/espace/profil/', [MarketPlaceController::class, 'profilCustomer']);
    });

    Route::prefix('cfp/traits')->group(function () {
        Route::post('/add', [CfpProfilController::class, 'addTrait'])->name('traits.add');
        Route::delete('/remove/{id}', [CfpProfilController::class, 'removeTrait'])->name('traits.remove');
    });

    Route::prefix('cfp/reasons')->group(function () {
        Route::post('/add', [CfpProfilController::class, 'addReason'])->name('reasons.add');
        Route::delete('/remove/{id}', [CfpProfilController::class, 'removeReason'])->name('reasons.remove');
    });

    Route::prefix('cfp/picture')->group(function () {
        Route::post('/add', [SectionController::class, 'storePictures'])->name('sections.storePictures');
        Route::delete('/remove/{id}', [SectionController::class, 'destroy'])->name('sections.destroy');
    });

    Route::prefix('cfp/espace')->group(function () {
        Route::get('/reglement', [MarketPlaceController::class, 'regualationCustomer']);
        Route::post('/store_by_section', [SectionController::class, 'storeBySection']);
        Route::post('/organigramme', [SectionController::class, 'storeOrganigramme']);
        Route::get('/get_by_section', [SectionController::class, 'show']);
    });
});

// ----------------CFP (Référent principal)
Route::middleware(['auth:sanctum', 'isCfp'])->group(function () {
    // Referents
    Route::prefix('cfp/referents')->group(function () {
        Route::post('/', [EmployecfpController::class, 'store'])->name('cfp.referents.store');
        Route::delete('/{id}', [EmployecfpController::class, 'destroy'])->name('cfp.referents.destroy');
        Route::put('/{idEmploye}', [EmployecfpController::class, 'update']);
        Route::put('/updatePassword/{idEmploye}', [EmployecfpController::class, 'updatePassword']);
        Route::post('/{idEmploye}/updatePhoto', [EmployecfpController::class, 'updatePhoto']);
    });
});

// ----------------ENTREPRISE
Route::middleware(['auth:sanctum', 'isReferent'])->group(function () {
    Route::post('/marketplace/reservation', [MarketPlaceController::class, 'reservationStore']);
    Route::get('/marketplace/reservation/{id}', [MarketPlaceController::class, 'reservationShow']);
    Route::get('home-etp', [DashboardController::class, 'dashboardEtp'])->name('home.entreprise');
});

Route::middleware(['auth:sanctum', 'isReferent'])->group(function () {
    Route::get('/get_countsEtp', [StatisticsController::class, 'getCountsEtp'])->name('etp.getCounts');
    // Route::get('home-etp', [DashboardController::class, 'dashboardEtp'])->name('home.entreprise');
    Route::get('/accueilEtp', [HomeController::class, 'indexEtp'])->name('accueilEtp');

    // search
    Route::get('/search-generality-etp', [SearchController::class, 'searchGeneralityEtp']);
    Route::get('/key-suggestion-etp', [SearchController::class, 'keySuggestion']);
    Route::prefix('etp/search')->group(function () {
        Route::get('/projects', [SearchController::class, 'getProjectEtp']);
        Route::get('/project-with-etp', [SearchController::class, 'getProjectWithEtpPaginate']);
        Route::get('/cfp', [SearchController::class, 'getCfp']);
        Route::get('/referents', [SearchController::class, 'getReferentCfp']);
        Route::get('/employes', [SearchController::class, 'getEmployee']);
    });
    // bon de commande
    Route::prefix('/etp/bon_commande')->group(function () {
        Route::get('/', [BonCommandeEtpController::class, 'index']);
    });

    Route::prefix('/etp/bon-commandes/doc')->group(function () {
        Route::get('/{idBC}/documents', [BonCommandeEtpController::class, 'getDocuments']);
        Route::post('/{idBC}/upload-document', [BonCommandeEtpController::class, 'uploadDocument']);
        Route::get('/{idDocument}/download', [BonCommandeEtpController::class, 'downloadDocument']);
        Route::delete('/{idDocument}/documents', [BonCommandeEtpController::class, 'deleteDocument']);
        Route::get('/{id}/preview', [BonCommandeEtpController::class, 'previewDocument'])->name('bc_document.preview');
    });

    // Evaluation à chaud
    Route::prefix('etp/evalChaud')->group(function () {
        Route::get('/', [EvaluationChaudEtpController::class, 'index']);
        Route::get('/{idProjet}', [EvaluationChaudEtpController::class, 'getLearnerAdded']);
    });

    // FMFP
    Route::prefix('etp/fmfp')->group(function () {
        Route::get('/', [FmfpController::class, 'indexEtp']);
    });

    // Projets
    Route::prefix('/etp/projets')->group(function () {
        Route::get('/', [ProjetInterneController::class, 'index'])->name('etp.projets.index');
        Route::get('/list', [ProjetInterneController::class, 'getProjectList'])->name('etp.projets.list');
        Route::get('/getProjetBystatus/status', [ProjetInterneController::class, 'getProjectListBystatus'])->name('etp.projets.list');
        Route::post('/', [ProjetInterneController::class, 'store'])->name('etp.projets.store');
        Route::get('/{idProjet}', [ProjetInterneController::class, 'show'])->name('etp.projets.show');
        Route::get('/formateur/{id}/mini-cv', [ProjetInterneController::class, 'getMiniCV']);
        Route::post('/{idProjet}/{idFormateur}/form/assign', [ProjetInterneController::class, 'formAssign']);
        Route::get('/{idProjet}/formateur-internes', [ProjetInterneController::class, 'getFormInterneAdded']);
        Route::get('/{idProjet}/formateurs', [ProjetInterneController::class, 'getFormAdded']);
        Route::delete('/{idProjet}/{idFormateur}/formateurs', [ProjetInterneController::class, 'formRemove']);
        Route::get('/{idProjet}/etp/assign', [ProjetInterneController::class, 'getEtpAssign']);
        Route::patch('/{idProjet}/{idEtp}/etp/assign', [ProjetInterneController::class, 'etpAssign']);
        Route::patch('/{idProjet}/{idModule}/module/assign', [ProjetInterneController::class, 'moduleAssign']);
        Route::patch('/{idProjet}/date/assign', [ProjetInterneController::class, 'dateAssign']);
        Route::delete('/{idProjet}/projet-internes', [ProjetInterneController::class, 'destroy'])->name('etp.projets.destroy');
        Route::get('/{idModule}/programmes', [ProjetInterneController::class, 'getProgramme']);
        Route::get('/{idModule}/module-ressources', [ProjetInterneController::class, 'getModuleRessourceProject']);
        Route::post('/{idProjet}/duplicate', [ProjetInterneController::class, 'duplicate'])->name('etp.projets.duplicate');
        Route::patch('/{idProjet}/update-date', [ProjetInterneController::class, 'updateDate'])->name('etp.projets.updateDate');
        Route::patch('/{idProjet}/update-module', [ProjetInterneController::class, 'updateModule'])->name('etp.projets.updateModule');
        Route::patch('/{idProjet}/{idSalle}/salles/assign', [ProjetInterneController::class, 'salleAssign']);
        Route::get('/{idProjet}/salles', [ProjetInterneController::class, 'getSalleAdded']);
        Route::patch('/{idProjet}/cancel', [ProjetInterneController::class, 'cancel']);
        Route::patch('/{idProjet}/repport', [ProjetInterneController::class, 'repport']);
        Route::patch('/{idProjet}/confirm', [ProjetInterneController::class, 'confirm']);
        Route::patch('/{idProjet}', [ProjetInterneController::class, 'updateProjet']);

        // Filtres
        Route::get('/filter/getDropdownItem', [ProjetInterneController::class, 'getDropdownItem']);
        Route::get('/filter/items', [ProjetInterneController::class, 'filterItems']);
        Route::get('/filter/item', [ProjetInterneController::class, 'filterItem']);

        Route::get('/{idProjet}/status', [ProjetInterneController::class, 'getStatutProjet']);

        // Momemtum images
        Route::get('/{idProjet}/momentums', [ProjetInterneController::class, 'showmomentum'])->name('etp.projets.showmomentum');
    });

    Route::get('etp/projets/{idProjet}/{isEtp}/frais', [ProjetController::class, 'fraisdetailsEtp'])->name('etp.projets.fraisdetails');
    Route::get('etp/projets/frais/all', [CfpInviteEtp::class, 'getAllFrais']);
    Route::post('etp/projets/{idProjet}/{idFrais}/{isEtp}/fraisprojet/assign', [ProjetController::class, 'fraisAssign'])->name('etp.projets.fraisAssign');
    Route::post('etp/projets/update-frais', [ProjetController::class, 'updateFrais'])->name('etp.projets.updateFrais');
    Route::post('etp/projets/{idProjet}/total-frais', [ProjetController::class, 'fraisTotalEtp'])->name('etp.projets.fraisTotal');
    Route::get('etp/projets/{idProjet}/getTotalHTJSON', [ProjetController::class, 'getTotalHTJSON'])->name('etp.projets.getTotalHTJSON');
    Route::get('etp/projets/{idFraisProjet}/idProjet', [ProjetController::class, 'getIdProjetByIdFraisProjet'])->name('etp.projets.getIdProjet');
    Route::post('etp/projets/{idProjet}/{idFraisProjet}/delete-frais', [ProjetController::class, 'fraisRemove'])->name('etp.projets.deleteFrais');
    Route::get('etp/projets/fermeturefrais', [ProjetController::class, 'fermeturefrais'])->name('etp.projets.fermeturefrais');
    Route::post('etp/projets/{idProjet}/update-taxe', [ProjetController::class, 'updateTaxe']);

    Route::prefix('etp/projet/etpInter')->group(function () {
        Route::get('/getEtpAdded/{idProjet}', [ProjetInterController::class, 'getEtpAdded']);
        Route::get('/apprenants/{idProjet}', [ProjetInterController::class, 'getApprenantProjetInter']);
        Route::get('/apprenant-addeds/{idProjet}', [ProjetInterController::class, 'getApprenantAddedInter']);
        Route::delete('/{idProjet}/{idEtp}', [ProjetInterController::class, 'removeEtpInter']);
        Route::post('/{idProjet}/{idApprenant}/{idEtp}', [ProjetInterController::class, 'addApprenantInter']);
        Route::delete('/{idProjet}/{idApprenant}/{idEtp}', [ProjetInterController::class, 'removeApprsEtp']);
    });

    // customer drawer
    Route::prefix('/etp')->group(function () {
        Route::get('/etp-drawer/{idEtp}', [ShowDrawerController::class, 'showCfpDrawer'])->name('etp.etp-drawer.index');
        Route::get('/form-drawer/{idFormateur}', [ShowDrawerController::class, 'showFormDrawer'])->name('etp.form-drawer.index');
        Route::get('/session-drawer/{idProjet}', [ShowDrawerController::class, 'showSessionDrawer'])->name('etp.session-drawer.index');
        Route::get('/dossier-drawer/{idProjet}', [ShowDrawerController::class, 'showDossierDrawer'])->name('etp.dossier-drawer.index');
        Route::get('/apprenant-drawer/{idProjet}', [ShowDrawerController::class, 'showApprenantDrawer'])->name('etp.apprenant-drawer.index');
        Route::get('/etp-drawers/apprenant/{id}', [ShowDrawerController::class, 'showApprenantWithProject'])->name('etp.etp-drawer.apprenant');
        Route::get('/planreperage-drawer/{idProjet}', [ShowDrawerController::class, 'showPLanDeReperageDrawer'])->name('etp.dossier-drawer.index');
    });

    // etp.seances
    Route::prefix('etp/seance-internes')->group(function () {
        Route::post('/', [SeanceInterneController::class, 'store']);
        Route::get('/{idProjet}/seances', [SeanceInterneController::class, 'getAllSeances']);
        Route::patch('/{idSeance}', [SeanceInterneController::class, 'update']);
        Route::delete('/{idSeance}', [SeanceInterneController::class, 'destroy']);
        Route::get('/{idProjet}/getSeanceAndTotalTime', [SeanceInterneController::class, 'getSeanceAndTotalTime']);
        Route::get('/latest', [SeanceInterneController::class, 'getLastFieldVueSeances']);
    });

    Route::prefix('etp/seance-intras')->group(function () {
        Route::get('/{idProjet}/seances', [SeanceIntraController::class, 'getAllSeances']);
        Route::patch('/{idSeance}/update', [SeanceIntraController::class, 'update']);
        Route::delete('/{idSeance}/delete', [SeanceIntraController::class, 'destroy']);
    });

    // Apprenant projet
    Route::prefix('etp/projet/apprenants')->group(function () {
        Route::get('/', [ApprenantEtpController::class, 'getApprenantProjets']);
        Route::get('/{idProjet}', [ApprenantEtpController::class, 'getApprenantAdded']);
        Route::get('/inter/{idProjet}', [ApprenantEtpController::class, 'getAllApprenantInter']);
        Route::post('/{idProjet}/{idApprenant}', [ApprenantEtpController::class, 'addApprenant']);
        Route::delete('/{idProjet}/{idApprenant}', [ApprenantEtpController::class, 'removeApprenant']);
        Route::get('/checkPresence/{idProjet}/{idEmploye}', [ApprenantEtpController::class, 'getPresenceUnique']);
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

    Route::prefix('etp/projet/etpInter')->group(function () {
        Route::get('/getEtpAdded/{idProjet}', [ProjetInterController::class, 'getEtpAdded']);
        Route::get('/apprenants/{idProjet}', [ProjetInterController::class, 'getApprenantProjetInter']);
        Route::get('/apprenant-addeds/{idProjet}', [ProjetInterController::class, 'getApprenantAddedInter']);
        Route::delete('/{idProjet}/{idEtp}', [ProjetInterController::class, 'removeEtpInter']);
        Route::post('/{idProjet}/{idApprenant}/{idEtp}', [ProjetInterController::class, 'addApprenantInter']);
        Route::delete('/{idProjet}/{idApprenant}/{idEtp}', [ProjetInterController::class, 'removeApprsEtp']);
    });

    // customer drawer
    Route::prefix('/etp')->group(function () {
        Route::get('/etp-drawer/{idEtp}', [ShowDrawerController::class, 'showCfpDrawer'])->name('etp.etp-drawer.index');
        Route::get('/form-drawer/{idFormateur}', [ShowDrawerController::class, 'showFormDrawer'])->name('etp.form-drawer.index');
        Route::get('/session-drawer/{idProjet}', [ShowDrawerController::class, 'showSessionDrawer'])->name('etp.session-drawer.index');
        Route::get('/dossier-drawer/{idProjet}', [ShowDrawerController::class, 'showDossierDrawer'])->name('etp.dossier-drawer.index');
        Route::get('/apprenant-drawer/{idProjet}', [ShowDrawerController::class, 'showApprenantDrawer'])->name('etp.apprenant-drawer.index');
        Route::get('/etp-drawers/apprenant/{id}', [ShowDrawerController::class, 'showApprenantWithProject'])->name('etp.etp-drawer.apprenant');
        Route::get('/planreperage-drawer/{idProjet}', [ShowDrawerController::class, 'showPLanDeReperageDrawer'])->name('etp.dossier-drawer.index');
    });

    // etp.seances
    Route::prefix('etp/seance-internes')->group(function () {
        Route::post('/', [SeanceInterneController::class, 'store']);
        Route::get('/{idProjet}/seances', [SeanceInterneController::class, 'getAllSeances']);
        Route::patch('/{idSeance}', [SeanceInterneController::class, 'update']);
        Route::delete('/{idSeance}', [SeanceInterneController::class, 'destroy']);
        Route::get('/{idProjet}/getSeanceAndTotalTime', [SeanceInterneController::class, 'getSeanceAndTotalTime']);
        Route::get('/latest', [SeanceInterneController::class, 'getLastFieldVueSeances']);
    });

    Route::prefix('etp/seance-intras')->group(function () {
        Route::get('/{idProjet}/seances', [SeanceIntraController::class, 'getAllSeances']);
        Route::patch('/{idSeance}/update', [SeanceIntraController::class, 'update']);
        Route::delete('/{idSeance}/delete', [SeanceIntraController::class, 'destroy']);
    });

    // Apprenant projet
    Route::prefix('etp/projet/apprenants')->group(function () {
        Route::get('/', [ApprenantEtpController::class, 'getApprenantProjets']);
        Route::get('/{idProjet}', [ApprenantEtpController::class, 'getApprenantAdded']);
        Route::get('/inter/{idProjet}', [ApprenantEtpController::class, 'getAllApprenantInter']);
        Route::post('/{idProjet}/{idApprenant}', [ApprenantEtpController::class, 'addApprenant']);
        Route::delete('/{idProjet}/{idApprenant}', [ApprenantEtpController::class, 'removeApprenant']);
        Route::get('/checkPresence/{idProjet}/{idEmploye}', [ApprenantEtpController::class, 'getPresenceUnique']);
    });

    // // dossier ETP
    Route::prefix('etp/dossier')->group(function () {
        Route::get('/show', [DossierControllerEtp::class, 'showByIdEtp'])->name('etp.dossier.show');
        Route::get('/getDossierDetail/{idDossier}', [DossierControllerEtp::class, 'getDossierDetailEtp'])->name('etp.dossier.getDossierDetail');
        Route::get('/document/liste/{idDossier}', [DossierControllerEtp::class, 'getFichierEtp'])->name('etp.dossier.liste.fichier');
        Route::get('/document/section/', [DossierController::class, 'getSectionDocument'])->name('dossier.getDocumentSectionDocument');
        Route::get('/document/type/{idSectionDocument}', [DossierController::class, 'getTypeDocument'])->name('dossier.getTypeDocument');
        Route::post('/document/upload/{idDossier}', [DossierController::class, 'uploadFichier'])->name('dossier.uploadFichier');
        Route::post('/document/supprimer/{idDossier}/{idProjet}', [DossierController::class, 'supprimeProjetInFolder'])->name('dossier.supprimer.fichier.dossier');
        Route::get('/document/download/{idDocument}', [DossierController::class, 'downloadDocument'])->name('dossier.downloadDocument');
        Route::get('/document/view/{idDocument}', [DossierController::class, 'viewDocument'])->name('dossier.viewDocument');
        Route::post('/document/delete/{idDocument}', [DossierController::class, 'destroyDocument'])->name('dossier.destroyDocument');
    });

    Route::prefix('etp/agenda')->group(function () {
        Route::get('/', [AgendaEtpController::class, 'index'])->name('agendaEtps.index');
        Route::get('/event-groups', [AgendaEtpController::class, 'getEventsGroupBy']);
        Route::get('/events', [AgendaEtpController::class, 'getEvents']);
        Route::get('/events-resources', [AgendaEtpController::class, 'getEventResources']);
    });

    // customer drawer
    Route::prefix('/etp')->group(function () {
        Route::get('/planreperage-drawer/{idProjet}', [ShowDrawerController::class, 'showPLanDeReperageDrawer'])->name('etp.dossier-drawer.index');
        Route::get('/session-drawer/{idProjet}', [ShowDrawerController::class, 'showSessionDrawer'])->name('etp.session-drawer.index');
        Route::get('/apprenant-drawer/{idProjet}', [ShowDrawerController::class, 'showApprenantDrawer'])->name('etp.apprenant-drawer.index');
        Route::get('/etp-drawer/{idEtp}', [ShowDrawerController::class, 'showCfpDrawer'])->name('etp.etp-drawer.index');
        Route::get('/dossier-drawer/{idProjet}', [ShowDrawerController::class, 'showDossierDrawer'])->name('etp.dossier-drawer.index');
    });
    // PHOTO ENTREPRISE

    Route::prefix('etp/folders')->group(function () {
        Route::get('/by/{year}', [FolderController::class, 'index']);
        Route::get('/', [FolderController::class, 'getAllFolders'])->name('cfp.gallery.folder');
    });

    Route::prefix('etp/projets/dossiers')->group(function () {
        Route::get('/{idDossier}', [FolderController::class, 'getProjects']);
    });

    Route::prefix('etp/images')->group(function () {
        Route::get('/{idProjet}', [ImageController::class, 'index']);
        Route::post('/{idProjet}', [ImageController::class, 'store']);
        Route::put('/{id}', [ImageController::class, 'update']);
        Route::delete('/{id}', [ImageController::class, 'destroy']);
    });

    // Skill matrix entreprise
    Route::prefix('/entreprises/skill-matrix')->group(function () {
        Route::get('/', [SkillMatrixEtpController::class, 'index']);
        Route::get('/projects/{idProjet}', [SkillMatrixEtpController::class, 'show']);
        Route::get('/projects/{idProjet}/skill-levels', [SkillMatrixEtpController::class, 'getSkillLevel']);
    });
    // Loyalty & Rewards entreprise
    Route::prefix('entreprises/rewards')->group(function () {
        Route::get('/', [RewardsEnterpriseController::class, 'index']);
        Route::get('/total', [RewardsEnterpriseController::class, 'countRewardByEntreprise']);
    });

    // Analytic entreprise
    Route::prefix('entreprises/analytic')->group(function () {
        Route::get('/home-etp', [DashboardController::class, 'dashboardEtp']);
    });

    Route::prefix('entreprise/clients')->group(function () {
        Route::get('/', [ClientCfpController::class, 'index']);
        Route::get('/{idCfp}', [ClientCfpController::class, 'show']);
        Route::delete('/destroy/{idCfp}', [ClientCfpController::class, 'destroy']);
    });

    // Invitation via "customerName"
    Route::controller(InvitationController::class)->prefix('entreprise/invites')->group(function () {
        Route::get('/{name}/name', 'getCustomerName');
        Route::get('/{id}/{typeCustomer}', 'getCustomer');
        Route::post('/store/{typeCustomer}/{idCustomer}', 'inviteCustomer');
        Route::post('/new-customer', 'inviteNewCustomer');
    });

    // reservation
    Route::prefix('/entreprises/rsv')->group(function () {
        Route::get('/', [ReservationEtpController::class, 'allRsvEtp']);
        Route::get('/{id}', [ReservationEtpController::class, 'showReservationById']);
    });

    Route::prefix('/etp/profils')->group(function () {
        Route::get('/', [ProfilEtpController::class, 'index'])->name('profil.etp.index');
        Route::patch('/{idCustomer}/update', [ProfilEtpController::class, 'update']);
        Route::post('/{idCustomer}/updateLogo', [ProfilEtpController::class, 'updateLogo']);
        Route::get('/user', [ProfilController::class, 'getUserInfo']);
        Route::put('/update-user', [ProfilController::class, 'updateUser']);
        Route::put('/update', [ProfilController::class, 'update']);

        Route::get('/listVille_coded', [SalleEtpController::class, 'getVillesByPostalCode']);
    });
});
// .................REPORTING
Route::middleware(['auth:sanctum', 'isReferent'])->group(function () {
    // --- Formation ---
    Route::prefix('etp/reporting')->group(function () {
        Route::get('/exportXl', [ReportingControllerEtp::class, 'exportXl'])->name('exportXlEtp');
        Route::get('/exportPdf', [ReportingControllerEtp::class, 'exportPdf'])->name('exportPdfEtp');
        Route::get('/formation', [ReportingControllerEtp::class, 'formation'])->name('ReportingFormationEtp');
        Route::post('/filterFormation', [ReportingControllerEtp::class, 'filterFormation'])->name('reporting.filter.formationEtp');
    });
    // --- Apprenant ---
    Route::prefix('etp/reporting')->group(function () {
        Route::post('/exportXl/app', [ReportingControllerEtp::class, 'exportAppEtpXl'])->name('exportAppEtpXl');
        Route::post('/exportPdf/app', [ReportingControllerEtp::class, 'exportAppEtpPdf'])->name('exportAppEtpPdf');
        Route::get('/apprenant', [ReportingControllerEtp::class, 'apprenantEtp'])->name('ReportingApprenantEtp');
    });
    // --- Cfp ---
    Route::prefix('etp/reporting')->group(function () {
        Route::post('/exportXl/cl', [ReportingControllerEtp::class, 'exportXlCl'])->name('exportXlEtpCl');
        Route::post('/exportPdf/cl', [ReportingControllerEtp::class, 'exportPdfCl'])->name('exportPdfEtpCl');
        Route::get('/client', [ReportingControllerEtp::class, 'client'])->name('ReportingClientEtp');
    });
    // --- Cours ---
    Route::prefix('etp/reporting')->group(function () {
        Route::post('/exportXlCours', [ReportingControllerEtp::class, 'exportXlCours'])->name('exportXlEtp');
        Route::post('/exportPdfCours', [ReportingControllerEtp::class, 'exportPdfCours'])->name('exportPdfEtp');
        Route::get('/cours', [ReportingControllerEtp::class, 'cours'])->name('ReportingCoursEtp');
        Route::get('/chiffre', [ReportingControllerEtp::class, 'chiffreAEtp'])->name('ReportingChiffreEtp');
    });
    // --- Chiffre d'affaire ---
    Route::prefix('etp/reporting')->group(function () {
        Route::get('/chiffre', [ReportingControllerEtp::class, 'chiffreAEtp'])->name('ReportingChiffreEtp');
    });
});

// ..............APPRENANT OU EMPLOYE

Route::middleware(['auth:sanctum', 'isReferent'])->group(function () {
    // Employés
    Route::prefix('etp/employes')->group(function () {
        Route::get('/', [EmployeController::class, 'index'])->name('etp.employes.index');
        Route::get('/{id}/edit', [EmployeController::class, 'edit'])->name('employes.idEtp');
        Route::post('/', [EmployeController::class, 'store'])->name('etp.employes.store');
        Route::patch('/{id}', [EmployeController::class, 'update'])->name('employes.etp.update');
        Route::post('/{id}/update-photo', [EmployeController::class, 'updateImageEmpl'])->name('etp.employes.updateImageEmpl');
        Route::get('/search/{name}/name', [EmployeController::class, 'searchName'])->name('etp.apprenants.search.name');
        Route::get('/search/getEmpFiltered', [EmployeController::class, 'getEmpFiltered'])->name('etp.apprenants.search.getEmpFiltered');
        Route::get('/filter/getDropdownItem', [EmployeController::class, 'getDropdownItem'])->name('etp.employes.filter.getDropdownItem');
        Route::get('/filter/items', [EmployeController::class, 'filterItems']);
        Route::get('/filter/item', [EmployeController::class, 'filterItem']);
        Route::patch('/{id}/service', [EmployeController::class, 'updateService']);
        Route::post('/excel', [EmployeController::class, 'addEmpExcel'])->name('etp.employes.addEmpExcel');
        Route::get('/add/getEtpType', [EmployeController::class, 'getEtpType']);
        Route::delete('/{id}', [EmployeController::class, 'destroy'])->name('etp.employes.destroy');
        Route::post('/import-excel', [EmployeController::class, 'importExcel']);
    });
    // customer drawer
    Route::prefix('/etp')->group(function () {
        Route::get('/etp-drawer/{idEtp}', [ShowDrawerController::class, 'showCfpDrawer'])->name('etp.etp-drawer.index');
        Route::get('/form-drawer/{idFormateur}', [ShowDrawerController::class, 'showFormDrawer'])->name('etp.form-drawer.index');
        Route::get('/session-drawer/{idProjet}', [ShowDrawerController::class, 'showSessionDrawer'])->name('etp.session-drawer.index');
        Route::get('/dossier-drawer/{idProjet}', [ShowDrawerController::class, 'showDossierDrawer'])->name('etp.dossier-drawer.index');
        Route::get('/apprenant-drawer/{idProjet}', [ShowDrawerController::class, 'showApprenantDrawer'])->name('etp.apprenant-drawer.index');
        Route::get('/etp-drawers/apprenant/{id}', [ShowDrawerController::class, 'showApprenantWithProject'])->name('etp.etp-drawer.apprenant');
        Route::get('/planreperage-drawer/{idProjet}', [ShowDrawerController::class, 'showPLanDeReperageDrawer'])->name('etp.dossier-drawer.index');
    });

    // Batch
    Route::prefix('etp/batch')->group(function () {
        Route::get('/', [BatchController::class, 'index']);
        Route::get('/getAll', [BatchController::class, 'getAll']);
        Route::post('/create', [BatchController::class, 'store']);
        Route::put('/update/{batch}', [BatchController::class, 'update']);
        Route::delete('/delete/{batch}', [BatchController::class, 'destroy']);
        Route::get('/{id}', [BatchController::class, 'edit']);
    });

    // Batch learner
    Route::prefix('etp/batch_learner')->group(function () {
        Route::get('/', [BatchLearnerController::class, 'index']);
        Route::get('/getAll', [BatchLearnerController::class, 'getAll']);
        Route::get('/non_participant/{id}', [BatchLearnerController::class, 'getNoParticipantLearner']);
        Route::get('/participant/{id}', [BatchLearnerController::class, 'getParticipantLearner']);
        Route::post('/create', [BatchLearnerController::class, 'store']);
        Route::put('update/{batch}', [BatchLearnerController::class, 'update']);
        Route::delete('/delete/{batch_learner}', [BatchLearnerController::class, 'destroy']);
    });

    Route::prefix('etp/agenda')->group(function () {
        Route::get('/', [AgendaEtpController::class, 'index'])->name('agendaEtps.index');
        Route::get('/event-groups', [AgendaEtpController::class, 'getEventsGroupBy']);
        Route::get('/events', [AgendaEtpController::class, 'getEvents']);
        Route::get('/events-resources', [AgendaEtpController::class, 'getEventResources']);
    });

    // customer drawer
    Route::prefix('/etp')->group(function () {
        Route::get('/planreperage-drawer/{idProjet}', [ShowDrawerController::class, 'showPLanDeReperageDrawer'])->name('etp.dossier-drawer.index');
        Route::get('/session-drawer/{idProjet}', [ShowDrawerController::class, 'showSessionDrawer'])->name('etp.session-drawer.index');
        Route::get('/apprenant-drawer/{idProjet}', [ShowDrawerController::class, 'showApprenantDrawer'])->name('etp.apprenant-drawer.index');
        Route::get('/etp-drawer/{idEtp}', [ShowDrawerController::class, 'showCfpDrawer'])->name('etp.etp-drawer.index');
        Route::get('/dossier-drawer/{idProjet}', [ShowDrawerController::class, 'showDossierDrawer'])->name('etp.dossier-drawer.index');
    });
});

// REFERENTS(entreprise)
Route::middleware(['auth:sanctum', 'isReferent'])->group(function () {
    Route::prefix('etp/referents')->group(function () {
        Route::get('/', [EmployeEtpController::class, 'index'])->name('etp.referents.index');
        Route::post('/', [EmployeEtpController::class, 'store'])->name('etp.referents.store');
        Route::get('/{idEmploye}/edit', [EmployeEtpController::class, 'edit']);
        Route::put('/{idEmploye}', [EmployeEtpController::class, 'update']);
        Route::get('/{idEmploye}/show', [EmployeEtpController::class, 'show']);
        Route::delete('/{id}', [EmployeEtpController::class, 'destroy'])->name('etp.referents.destroy');
        Route::post('/{idEmploye}/updatePhoto', [EmployeEtpController::class, 'updatePhoto']);
        Route::put('/updatePassword/{idEmploye}', [EmployeEtpController::class, 'updatePassword']);
    });
    // PRESENCE(entreprise)
    Route::prefix('entreprise/emargements')->group(function () {
        Route::get('/projets', [EmargementEtpController::class, 'getProjects']);
        Route::get('/projets/{idProjet}', [EmargementEtpController::class, 'show']);
    });
});

// ----------------FORMATEUR
Route::middleware(['auth:sanctum', 'isFormateur'])->group(function () {

    // gallery
    // Gallery
    Route::prefix('/form/gallery')->group(function () {
        Route::get('/{idProjet}/image', [GalleryFormController::class, 'allImage']);
        Route::post('/{idProjet}/addImage', [GalleryFormController::class, 'addImageGallery']);
        Route::delete('/deleteImage/{id}', [GalleryFormController::class, 'deleteImageGallery']);
        Route::put('/update/{id}', [GalleryFormController::class, 'updateDescriptionImageGallery']);
        Route::get('/', [GalleryFormController::class, 'getAllGallery'])->name('cfp.gallery.folder');
        Route::get('/folder/{year}', [GalleryFormController::class, 'getAllFolder']);
        Route::get('/project/{idDossier}', [GalleryFormController::class, 'getProjectFolder']);
        Route::get('/folderFilter/{year}', [GalleryFormController::class, 'getAllFolderOrder']);
        Route::get('/getImage/{idDossier}', [GalleryFormController::class, 'getGalleryByFolder']);
    });
    Route::get('/accueilForm', [HomeController::class, 'indexForm'])->name('accueilForm');
    Route::get('formateur/dashboard', [FormDashboardController::class, 'index'])->name('form.dashboard');
    // AgendaForm et Annuaire
    Route::prefix('form/agenda')->group(function () {
        Route::get('/', [AgendaFormController::class, 'index'])->name('agenda.form');
        Route::get('/{idProjet}/event', [AgendaFormController::class, 'getEvent']);
        Route::get('/events', [AgendaFormController::class, 'getEvents']);
        Route::get('/seance/{month}/{year}', [AgendaFormController::class, 'countSeance']);
    });

    Route::prefix('/home')->group(function () {
        Route::get('/api/config', [AgendaCfpController::class, 'getConfigApi']);
    });

    Route::get('formateur/folders/by/{year}', [FolderController::class, 'foldersFomateurs']);
    Route::get('formateurs/folders', [FolderController::class, 'getFoldersFomateurs']);

    // Evaluation
    Route::post('/projetsForm/evaluation/aprrenant', [EvaluationController::class, 'save'])->name('evaluation.apprenant.form');
    Route::get('/projetsForm/evaluation/aprrenant/{idEmploye}/{idProjet}', [EvaluationController::class, 'get']);

    Route::prefix('projetsForm')->group(function () {
        Route::post('/chaud', [EvaluationController::class, 'store']);
        Route::patch('/editEval', [EvaluationController::class, 'editEval']);
        Route::get('/checkEval/{idProjet}/{idEmploye}', [EvaluationController::class, 'checkEval']);
    });

    // list projet
    Route::get('/list_project', [FormateurController::class, 'getListProjectTrainer']);

    Route::get('/list_etp/{projectId}/{projectType}', [FormateurController::class, 'getLisEtpByProject']);

    Route::get('/check_name_employee/{idEtp}', [FormateurController::class, 'checkNameLearnerByEtp']);

    Route::get('/check_fullname_employee/{idEtp}', [FormateurController::class, 'checkFullnameLearnerByEtp']);

    Route::prefix('projetsForm')->group(function () {
        Route::get('/', [FormateurController::class, 'indexForm'])->name('projetForms.indexForm');
        Route::get('/list', [FormateurController::class, 'getProjectListForm'])->name('projetForms.list');
        Route::get('/{idProjet}/detailForm', [FormateurController::class, 'detailForm'])->name('projetForms.detailForm');
        Route::get('/{idFormateur}/mini-cv', [FormateurController::class, 'getMiniCV']);
        Route::get('/getProjetForm/{statut}', [FormateurController::class, 'getProjetForm']);
        Route::get('/getEtpAdded/{idProjet}', [FormateurController::class, 'getEtpAdded']);
        Route::get('/{idProjet}/etp/assign', [FormateurController::class, 'getEtpAssign']);
        Route::get('/etp-drawer/{idEtp}', [FormateurController::class, 'showEtpDrawer']);
        Route::get('/{idProjet}/detailForm', [FormateurController::class, 'detailForm'])->name('projetForms.detailForm');
        Route::get('/{idProjet}/session-drawer', [ShowDrawerController::class, 'showSessionDrawer']);
        Route::post('/add-apprenant/{idProjet}/{idApprenant}', [FormateurController::class, 'addApprenant']);
        Route::delete('/add-apprenant/{idProjet}/{idApprenant}', [FormateurController::class, 'removeApprenant']);
        Route::get('/{idProjet}/detailForm/momentum', [FormateurController::class, 'showmomentum'])->name('projetForms.detailForm.showmomentum');
        Route::post('/uploadphoto/{idProjet}', [FormateurController::class, 'uploadPhotoMomentum'])->name('projetForms.uploadphoto.momentum');
        Route::delete('/deletephoto/{idProjet}/{url}', [FormateurController::class, 'destroyPhoto'])->name('projetForms.deletephoto.destroy');

        // filtres
        Route::get('/filter/getDropdownItem', [FormateurController::class, 'getDropdownItem']);
        Route::get('/filter/items', [FormateurController::class, 'filterItems']);
        Route::get('/filter/item', [FormateurController::class, 'filterItem']);
    });

    // seance Formateur
    Route::prefix('projetsForm')->group(function () {
        Route::post('/', [AgendaFormController::class, 'store']);
        Route::get('/{idProjet}/getAllSeances', [AgendaFormController::class, 'getAllSeances']);
        Route::get('/getLastFieldSeances', [AgendaFormController::class, 'getLastFieldSeances']);
        Route::get('/getLastFieldVueSeances', [AgendaFormController::class, 'getLastFieldVueSeances']);
        Route::get('/{idProjet}/homeForm/get-id-customer', [FormateurController::class, 'getIdCustomer']);
    });

    // Drawer
    Route::prefix('/projetsForm')->group(function () {
        Route::get('/{idProjet}/session-drawer', [ShowDrawerController::class, 'showSessionDrawer']);
        Route::get('/apprenants-drawer/{idProjet}', [ShowDrawerController::class, 'showApprenantDrawer']);
        Route::get('/{idProjet}/document-drawer', [ShowDrawerController::class, 'showDocumentDrawer']);
        Route::get('/planreperage-drawer/{idProjet}', [ShowDrawerController::class, 'showPLanDeReperageDrawer']);
    });

    Route::get('projetsForm/detail/{idModule}/drawer', [ModuleController::class, 'detailModule']);

    // Apprenant
    Route::get('projetsForm/getApprenantProjets/{idEtp}', [FormateurController::class, 'getApprenantProjets']);

    // Customer
    Route::prefix('projetsForm')->group(function () {
        Route::get('/etp-drawer/{idEtp}', [FormateurController::class, 'showEtpDrawer']);
        Route::get('/form-drawer/{idFormateur}', [FormateurController::class, 'showFormDrawer']);
        Route::get('/getApprenantAddedInter/{idProjet}', [FormateurController::class, 'getApprenantAddedInter']);
        Route::get('/getApprenantAdded/{idProjet}', [FormateurController::class, 'getApprenantAdded']);
        Route::get('/getApprAddedInter/{idProjet}', [ApprenantController::class, 'getApprAddedInter']);
        Route::get('/checkPresence/{idProjet}/{idEmploye}', [FormateurController::class, 'getPresenceUnique']);
        Route::get('getApprenantProjetInter/{idProjet}', [FormateurController::class, 'getApprenantProjetInter']);
    });

    // Get Etps
    Route::get('projetsForm/etp/getAllEtps', [FormateurController::class, 'getAllEtps']);

    // Apprenants
    // Route::controller(ApprenantController::class)->group(function () {
    //     Route::prefix('form/apprenants')->group(function () {
    //         Route::get('/', 'indexFormAppr')->name('form.apprenants.index');
    //         Route::post('/', 'addEmpForm')->name('form.apprenants.store');
    //         Route::get('/entreprises', 'getEntreprises');
    //         Route::get('/{id}', 'editEmpForm');
    //         Route::patch('/{id}', 'update');
    //         Route::post('/{idApprenant}/updatePhoto', 'updateImageAppr');
    //     });
    // });

    // Mini CV
    Route::get('miniCv', [FormateurController::class, 'indexCv'])->name('miniCv.index');
    Route::get('formateur/{idFormateur}/mini-cv', [FormateurController::class, 'getMiniCV']);
    Route::post('/update-langue-note/{id}', [FormateurController::class, 'updateNote'])->name('update.langue.note');
    Route::post('/update-competence-note/{id}', [FormateurController::class, 'updateNote'])->name('update.competence.note');
    Route::post('miniCv', [FormateurController::class, 'storeCv'])->name('miniCv.index.store');
    Route::get('/profile/edit', [FormateurController::class, 'editProfile'])->name('profile.edit.form');
    Route::put('/profile/update/{id}', [FormateurController::class, 'updateProfile'])->name('profile.update.form');
    Route::post('form/photo/update/{id}', [FormateurController::class, 'updatePhoto'])->name('photo.update.form');
    Route::delete('miniCv/{id}/destroy', [FormateurController::class, 'destroy'])->name('miniCv.index.destroy');

    // apprenants
    Route::controller(ApprenantController::class)->group(function () {
        Route::prefix('form/apprenants')->group(function () {
            Route::get('/index', [ApprenantController::class, 'getProjectTrainer']);
            Route::get('/{id}/edit', [ApprenantController::class, 'edit']);
            Route::post('/', [ApprenantController::class, 'addEmp']);
            Route::put('/update/learner/{id}', [ApprenantController::class, 'update']);
            Route::delete('/{id}', [ApprenantController::class, 'destroy']);
            Route::post('/', [ApprenantController::class, 'addEmpForm'])->name('form.apprenants.store');
            Route::get('/entreprise', [ApprenantController::class, 'getEntreprises']);
            Route::get('/{id}', [ApprenantController::class, 'editEmpForm']);
            Route::patch('/{id}', [ApprenantController::class, 'update']);
            Route::post('/{idApprenant}/updatePhoto', [ApprenantController::class, 'updateImageAppr']);
            Route::post('/excel', [ApprenantController::class, 'addEmpExcel'])->name('form.apprenants.addEmpExcel');
        });
    });

    // skill_matrix

    Route::prefix('/formateurs/skill-matrix')->group(function () {
        Route::get('/', [SkillMatrixFormateurController::class, 'index']);
        Route::get('/projects/{idProjet}', [SkillMatrixFormateurController::class, 'show']);
        Route::post('/projects/{idProjet}/employes/{idEmploye}/skills/{skillId}', [SkillMatrixFormateurController::class, 'store']);
        Route::put('/projects/{idProjet}/employes/{idEmploye}/skills/{skillId}/update', [SkillMatrixFormateurController::class, 'update']);
        Route::get('/projects/{idProjet}/skill-levels', [SkillMatrixFormateurController::class, 'getSkillLevel']);

        Route::prefix('/module_skills')->group(function () {
            Route::get('/', [ModuleSkillFormateurController::class, 'getModuleTrainer']);
            Route::get('/{id}/skills', [ModuleSkillController::class, 'index']);
            Route::put('/{id}/skills/{idSkill}/update', [ModuleSkillFormateurController::class, 'edit']);
            Route::post('/{id}/skills', [ModuleSkillFormateurController::class, 'store']);
            Route::delete('/{id}/skills/{idSkill}', [ModuleSkillFormateurController::class, 'destroy']);
        });
    });

    Route::prefix('/formateur/eval-froid')->group(function () {
        Route::get('/', [EvaluationController::class, 'indexFroidForm']);
        Route::post('/{idProjet}', [EvaluationController::class, 'sendEvaluation']);
        Route::get('/apprenants/{idProjet}', [EvaluationController::class, 'getApprenants']);
        Route::get('/detail/{idProjet}', [EvaluationController::class, 'getApprenantByProjectResult']);
        Route::get('/result/{idProjet}/{idEmploye}', [EvaluationController::class, 'apprenantEvaluationResult']);
    });

    // Mini CV
    Route::prefix('/formateur')->group(function () {
        Route::get('/miniCv', [FormateurController::class, 'indexCv'])->name('miniCv.index');
        Route::get('/formateur/{idFormateur}/mini-cv', [FormateurController::class, 'getMiniCV']);
        Route::get('/miniCv/create', [FormateurController::class, 'createCv'])->name('miniCv.index.create');
        Route::get('/miniCv/createDp', [FormateurController::class, 'createDp'])->name('miniCv.index.createDp');
        Route::get('/miniCv/createCp', [FormateurController::class, 'createCp'])->name('miniCv.index.createCp');
        Route::get('/miniCv/createLg', [FormateurController::class, 'createLg'])->name('miniCv.index.createLg');
        Route::post('/update-langue-note/{id}', [FormateurController::class, 'updateNote'])->name('update.langue.note');
        Route::post('/update-competence-note/{id}', [FormateurController::class, 'updateNote'])->name('update.competence.note');
        Route::post('/miniCv', [FormateurController::class, 'storeCv'])->name('miniCv.index.store');
        Route::get('/profile/edit', [FormateurController::class, 'editProfile'])->name('profile.edit.form');
        Route::put('/profile/update/{id}', [FormateurController::class, 'updateProfile'])->name('profile.update.form');
        Route::post('/form/photo/update/{id}', [FormateurController::class, 'updatePhoto'])->name('photo.update.form');
        Route::delete('/miniCv/{id}/destroy', [FormateurController::class, 'destroyCv'])->name('miniCv.index.destroy');
    });

    // Refresh formateur
    Route::prefix('formateur/refresh')->group(function () {
        Route::get('/', [MenuRefreshController::class, 'index'])->name('formateur.refresh.index');
        Route::post('/menu-refresh', [MenuRefreshController::class, 'store'])->name('formateur.refresh.store');
        Route::get('/{id}', [MenuRefreshController::class, 'getByModule'])->name('formateur.refresh.module');
        Route::get('/getModuleFormateur', [MenuRefreshController::class, 'getModuleFormateur'])->name('formateur.refresh.getModuleFormateur');
        Route::get('/{id}/showModule', [MenuRefreshController::class, 'showModule'])->name('formateur.refresh.showModule');
        Route::get('/menu-refresh/module/{idModule}', [MenuRefreshController::class, 'getMenusByModule'])->name('formateur.refresh.getMenusByModule');

        // Nouvelles routes pour les actions des boutons
        Route::put('/menu-refresh/{menuId}', [MenuRefreshController::class, 'update'])->name('formateur.menu.update');
        Route::delete('/menu-refresh/{menuId}', [MenuRefreshController::class, 'destroy'])->name('formateur.menu.delete');
    });
    Route::prefix('/formateur/module/programmes')->group(function () {
        Route::get('/menu-refresh/module/{idModule}', [ProgrammeController::class, 'getProgrammesFormateur'])->name('formateur.refresh.getMenusByModule'); // controlleur seulement pour la récupération du programme
        Route::get('/{id}', [ModuleProgramContentController::class, 'index']); // pour les contenue
        Route::post('/{id}', [ModuleProgramContentController::class, 'store']);
        Route::get('/{id}/content/{idContent}', [ModuleProgramContentController::class, 'show']);
        Route::put('/{id}/content/{idContent}', [ModuleProgramContentController::class, 'update']);
        Route::delete('/{id}/content/{idContent}', [ModuleProgramContentController::class, 'destroy']);
        Route::get('/', [ModuleProgramContentController::class, 'test']); // pour les contenue
    });
    Route::prefix('contents/formateur')->group(function () {
        Route::get('/module/{idModuleContent}', [ContentRefreshController::class, 'index']);
        Route::get('/{id}', [ContentRefreshController::class, 'show']);
        Route::post('/', [ContentRefreshController::class, 'store']);
        Route::get('content/{idContent}/edit', [ContentRefreshController::class, 'edit']);
        Route::put('/{id}', [ContentRefreshController::class, 'update']);
        Route::delete('/{id}', [ContentRefreshController::class, 'destroy']);

        // Gestion fichiers individuels
        Route::post('/{idContent}/files', [ContentRefreshController::class, 'addFile']);
        Route::delete('/files/{idFile}', [ContentRefreshController::class, 'deleteFile']);
    });

    /* PRESENCE FORMATEUR * */
    Route::prefix('formateur/presence')->group(function () {
        Route::get('/', [EmargementFormateurController::class, 'index']);
        Route::get('/{idProjet}', [EmargementFormateurController::class, 'showEmgFormateur']);
        Route::get('/projects/{idProjet}/data', [EmargementFormateurController::class, 'getDataPresence']);
        Route::post('/emargement', [EmargementController::class, 'store']);
        Route::patch('/emargement/update/{idProjet}/{isPresent}', [EmargementController::class, 'update']);
        Route::get('/emargement/{idProjet}', [EmargementController::class, 'edit']);
        Route::delete('/emargement/delete', [EmargementController::class, 'destroy']);
    });

    Route::prefix('formateur/images')->group(function () {
        Route::get('/{idDossier}', [ImageController::class, 'index']);
        Route::post('/{idProjet}', [ImageController::class, 'store']);
        Route::put('/{id}', [ImageController::class, 'update']);
        Route::delete('/{id}', [ImageController::class, 'destroy']);
    });
});

// Apprenants
Route::middleware(['auth:sanctum', 'isEmploye'])->group(function () {
    Route::get('/accueilApp', [HomeController::class, 'indexApp'])->name('accueilApp');
    Route::get('/emp/getUser', [UserController::class, 'getProfilLearner']);
    Route::post('/update/image', [UserController::class, 'updateImageProfil']);
    Route::put('/update/profil', [UserController::class, 'updateProfil']);

    // ProjetEmps
    Route::prefix('employes/projets')->group(function () {
        Route::get('/list', [ApprenantController::class, 'getProjectListEmp']);
        Route::get('/list/status', [ApprenantController::class, 'getProjectListEmpBystatus']);
        Route::get('/list/{statut}', [ApprenantController::class, 'getProjetEmp']);
        Route::get('/{idProjet}', [ApprenantController::class, 'detailEmp'])->name('emps.detailEmp.index');

        // Apprenants projets
        Route::get('/apprenants/{idProjet}', [ApprenantController::class, 'getApprAddedInter']);
        Route::get('/projet/entreprises', [ApprenantController::class, 'getEtpAdded']);
        Route::get('/etpInter/getApprenantProjetInter/{idProjet}', [ApprenantController::class, 'getApprenantProjetInter']);
        Route::post('/etpIntra/{idProjet}/{idApprenant}', [ApprenantController::class, 'addApprenant']);
        Route::post('/etpInter/{idProjet}/{idApprenant}/{idEtp}', [ApprenantController::class, 'addApprenantInter']);
        Route::get('/{idModule}/programmes', [ApprenantController::class, 'programProject']);

        // filtres
        Route::get('/filter/getDropdownItem', [ApprenantController::class, 'getDropdownItem']);
        Route::get('/filter/items', [ApprenantController::class, 'filterItems']);
        Route::get('/filter/item', [ApprenantController::class, 'filterItem']);

        // Momentum photos
        Route::get('/{idProjet}/detailForm/momentum', [ApprenantController::class, 'showmomentum'])->name('emps.detailForm.showmomentum');
        Route::post('/uploadphoto/{idProjet}', [ApprenantController::class, 'uploadPhotoMomentum'])->name('emps.uploadphoto.momentum');
        Route::delete('/deletephoto/{idProjet}/{url}', [ApprenantController::class, 'destroyPhoto'])->name('emps.deletephoto.destroy');
    });

    // APPRENANT
    Route::prefix('/employes/skill-matrix')->group(function () {
        Route::get('/', [SkillMatrixEmployeController::class, 'index']);
        Route::get('/projects/{idProjet}', [SkillMatrixEmployeController::class, 'show']);
        Route::get('/projects/{idProjet}/skill-levels', [SkillMatrixEmployeController::class, 'getSkillLevel']);
    });

    // Refresh apprenant
    Route::prefix('apprenant/refresh')->group(function () {
        Route::get('/', [MenuRefreshController::class, 'index'])->name('apprenant.refresh.index');
        Route::post('/menu-refresh', [MenuRefreshController::class, 'store'])->name('apprenant.refresh.store');
        Route::get('/{id}', [MenuRefreshController::class, 'getByModule'])->name('apprenant.refresh.module');
        Route::get('/getModuleApprenant', [MenuRefreshController::class, 'getModuleApprenant'])->name('apprenant.refresh.getModuleApprenant');
        Route::get('/{id}/showModule', [MenuRefreshController::class, 'showModule'])->name('apprenant.refresh.showModule');
        Route::get('/menu-refresh/module/{idModule}', [MenuRefreshController::class, 'getMenusByModule'])->name('apprenant.refresh.getMenusByModule');

        // Nouvelles routes pour les actions des boutons
        Route::put('/menu-refresh/{menuId}', [MenuRefreshController::class, 'update'])->name('apprenant.menu.update');
        Route::delete('/menu-refresh/{menuId}', [MenuRefreshController::class, 'destroy'])->name('apprenant.menu.delete');
    });
    Route::prefix('/apprenant/module/programmes')->group(function () {
        Route::get('/menu-refresh/module/{idModule}', [ProgrammeController::class, 'getProgrammesApprenant'])->name('apprenant.refresh.getMenusByModule'); // controlleur seulement pour la récupération du programme
        Route::get('/{id}', [ModuleProgramContentController::class, 'index']); // pour les contenue
        Route::post('/{id}', [ModuleProgramContentController::class, 'store']);
        Route::get('/{id}/content/{idContent}', [ModuleProgramContentController::class, 'show']);
        Route::put('/{id}/content/{idContent}', [ModuleProgramContentController::class, 'update']);
        Route::delete('/{id}/content/{idContent}', [ModuleProgramContentController::class, 'destroy']);
        Route::get('/', [ModuleProgramContentController::class, 'test']); // pour les contenue
    });
    Route::prefix('contents/apprenant')->group(function () {
        Route::get('/module/{idModuleContent}', [ContentRefreshController::class, 'index']);
        Route::get('/{id}', [ContentRefreshController::class, 'show']);
        Route::post('/', [ContentRefreshController::class, 'store']);
        Route::get('content/{idContent}/edit', [ContentRefreshController::class, 'edit']);
        Route::put('/{id}', [ContentRefreshController::class, 'update']);
        Route::delete('/{id}', [ContentRefreshController::class, 'destroy']);

        // Gestion fichiers individuels
        Route::post('/{idContent}/files', [ContentRefreshController::class, 'addFile']);
        Route::delete('/files/{idFile}', [ContentRefreshController::class, 'deleteFile']);
    });

    // Drawer Projet List
    Route::get('employes/projets/{idFormateur}/mini-cv', [ApprenantController::class, 'getMiniCv']);
    Route::get('employes/modules/detail/{idModule}/drawer', [ApprenantController::class, 'getModule']);
    Route::get('employes/etp-drawer/{idEtp}', [ApprenantController::class, 'getEtp']);
    Route::get('employes/projets/{idProjet}/session-drawer', [ApprenantController::class, 'getSession']);
    Route::get('employes/projets/{idProjet}/apprenant-drawer', [ApprenantController::class, 'DrawerApprenant']);
    Route::get('employes/projets/{idProjet}/document-drawer', [ApprenantController::class, 'DrawerDoc']);
    Route::get('employes/projets/session-drawer/{idProjet}', [ShowDrawerController::class, 'showSessionDrawer']);
    Route::get('employes/projets/planreperage-drawer/{idProjet}', [ShowDrawerController::class, 'showPLanDeReperageDrawer']);

    // Employe Inter / Support de cours / Programme
    Route::prefix('projetsEmp')->group(function () {
        Route::get('/support', [SupportCoursController::class, 'supportEmp'])->name('support.emp');
        Route::get('{idModuleRessource}/download', [SupportCoursController::class, 'download'])->name('projetEmp.download');
        Route::get('/{idModule}/getModuleRessourceProject', [SupportCoursController::class, 'moduleRessource']);
    });

    // Images
    Route::prefix('employes/images')->group(function () {
        Route::get('/{idDossier}', [ImageController::class, 'index']);
        Route::post('/{idProjet}', [ImageController::class, 'store']);
        Route::put('/{id}', [ImageController::class, 'update']);
        Route::delete('/{id}', [ImageController::class, 'destroy']);
    });

    // Support de cours

    Route::get('homeEmp', [HomeController::class, 'indexEmp'])->name('api.home.employe');

    Route::prefix('centre')->group(function () {
        Route::get('/', [CentreController::class, 'index'])->name('api.centre.index');
        Route::get('/{id}/detail', [CentreController::class, 'show'])->name('api.centre.show');
    });

    Route::prefix('supportProgramme')->group(function () {
        Route::get('/', [SupportCoursController::class, 'getProgramme'])->name('api.programme.index');
        Route::get('/{id}/detail', [SupportCoursController::class, 'show'])->name('api.programme.show');
    });

    // agenda
    Route::prefix('employes/agenda')->group(function () {
        Route::get('/', [AgendaEmpController::class, 'index'])->name('agendaEmps.index');
        Route::get('/event', [AgendaEmpController::class, 'getEvent']);
        Route::get('/events', [AgendaEmpController::class, 'getEvents']);
        Route::get('/seances/{month}/{year}', [AgendaEmpController::class, 'countSeance']);
        Route::get('/calendar', [AgendaEmpController::class, 'indexCalendar'])->name('calendar.employe.index');
    });
    Route::prefix('/home')->group(function () {
        Route::get('/api/config', [AgendaCfpController::class, 'getConfigApi']);
    });

    Route::prefix('projetsEmp')->group(function () {
        Route::get('{idModuleRessource}/download', [SupportCoursController::class, 'download'])->name('projetEmp.download');
        Route::get('/{idProjet}/download-all', [SupportCoursController::class, 'downloadAllModuleRessources'])->name('projetEmp.downloadAll');
    });

    // Evaluation
    Route::prefix('employe/projet/evaluation')->group(function () {
        // evaluation chaud
        Route::post('/chaud', [EvaluationController::class, 'store'])->name('emp.evaluation');
        Route::patch('/editEval', [EvaluationController::class, 'editEval'])->name('emp.editEvaluation');
        Route::get('/checkEval/{idProjet}/{idEmploye}', [EvaluationController::class, 'checkEval']);
        Route::get('/checkPresence/{idProjet}/{idEmploye}', [ApprenantController::class, 'getPresenceUnique']);
        // evaluation froid
        Route::get('/froid', [EvaluationController::class, 'index'])->name('evaluation.index');
        Route::post('/add/froid/{idProjet}', [EvaluationController::class, 'storeColdEvaluation'])->name('evaluation.store');
    });
    Route::prefix('employes/projets')->group(function () {
        Route::get('/list', [ApprenantController::class, 'getProjectListEmp']);
        Route::get('/list/{statut}', [ApprenantController::class, 'getProjetEmp']);
        Route::get('/{idProjet}', [ApprenantController::class, 'detailEmp'])->name('emps.detailEmp.index');

        // Apprenants projets
        Route::get('/apprenants/{idProjet}', [ApprenantController::class, 'getApprAddedInter']);
        Route::get('/projet/entreprises', [ApprenantController::class, 'getEtpAdded']);
        Route::get('/etpInter/getApprenantProjetInter/{idProjet}', [ApprenantController::class, 'getApprenantProjetInter']);
        Route::post('/etpIntra/{idProjet}/{idApprenant}', [ApprenantController::class, 'addApprenant']);
        Route::post('/etpInter/{idProjet}/{idApprenant}/{idEtp}', [ApprenantController::class, 'addApprenantInter']);
        Route::get('/{idModule}/programmes', [ApprenantController::class, 'programProject']);

        // Momentum photos
        Route::get('/{idProjet}/detailForm/momentum', [ApprenantController::class, 'showmomentum'])->name('emps.detailForm.showmomentum');
        Route::post('/uploadphoto/{idProjet}', [ApprenantController::class, 'uploadPhotoMomentum'])->name('emps.uploadphoto.momentum');
        Route::delete('/deletephoto/{idProjet}/{url}', [ApprenantController::class, 'destroyPhoto'])->name('emps.deletephoto.destroy');
    });

    // Projets Emps
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

    // Filtre Projets Employe
    Route::prefix('employe')->group(function () {
        Route::get('/filter/getDropdownItem', [FiltreApprenantController::class, 'getDropdownItem']);
        Route::get('/filter/items', [FiltreApprenantController::class, 'filterItems']);
        Route::get('/filter/item', [FiltreApprenantController::class, 'filterItem']);
    });

    // Seance
    Route::get('employe/seances/{idProjet}/lists', [ApprenantController::class, 'getAllSeance']);

    // Salle
    Route::prefix('employe/salles')->group(function () {
        Route::get('/', [ApprenantController::class, 'getAllSalle'])->name('employes.salles.getAllSalle');
        Route::get('/{idProjet}', [ApprenantController::class, 'getSalleAdded']);
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

    // Gallerie photos des employés emp.gallery
    Route::prefix('/employes/gallery')->group(function () {
        Route::get('/', [GalleryEmpController::class, 'getAllGallery'])->name('emp.gallery.folder');
        Route::get('/folder', [GalleryEmpController::class, 'getAllFolder']);
        Route::get('/image', [GalleryEmpController::class, 'allImage']);
    });
    Route::controller(EvaluationController::class)->prefix('employes/evaluations/froids')->group(function () {
        Route::get('/', 'index')->name('employes.evaluations.froids.index');
        Route::post('/{idProjet}', 'storeColdEvaluation')->name('employes.evaluations.froids.store');
    });
});

// Testing
Route::middleware(['auth:sanctum', 'isEmpOrParticulier'])->group(function () {
    Route::get('/credits-wallet/{id}', [CreditsWalletController::class, 'show_user_credit_wallet'])->name('user.credits.wallet'); // Route pour avoir les crédits d'un utilisateur

    Route::prefix('employes/folders')->group(function () {
        Route::get('/by/{year}', [FolderController::class, 'index']);
        Route::get('/', [FolderController::class, 'getAllFolders'])->name('cfp.gallery.folder');
    });

    // Projets
    Route::prefix('employes/projets/dossiers')->group(function () {
        Route::get('/{idDossier}', [FolderController::class, 'getProjects']);
    });

    // Images
    Route::prefix('employes/images')->group(function () {
        Route::get('/{idDossier}', [ImageController::class, 'index']);
        Route::post('/{idProjet}', [ImageController::class, 'store']);
        Route::put('/{id}', [ImageController::class, 'update']);
        Route::delete('/{id}', [ImageController::class, 'destroy']);
    });

    Route::prefix('qcm/solve')->group(function () {
        Route::get('/{id}/review', [QcmController::class, 'review_qcm'])->name('qcm.review'); // Route pour revoir les réponses avant de soumettre les rponses choisies et ainsi donner la possibilité de modifier ces choix à l'utilisateur
        Route::post('/{id}/submit', [QcmController::class, 'submit_qcm'])->name('qcm.submit'); // Route pour soumettre et calculer les rsultats de l'utilisateur ayant effectuer le test
        Route::get('/{id}/results', [QcmController::class, 'show_qcm_results_after_test'])->name('qcm.results'); // Route pour afficher les résultats de l'utilisateur au QCM juste après le test
        Route::post('/{id}/start-test', [QcmController::class, 'start_test'])->name('qcm.start_test'); // Route pour démarrer un test de QCM (v6 de la fonction)
        Route::get('/{id}/start-test', [QcmController::class, 'get_start_test'])->name('qcm.get_start_test'); // Route pour dmarrer un test de QCM (v6 de la fonction)
        Route::get('/{id}/respond/{questionIndex?}', [QcmController::class, 'show_qcm_to_respond'])->name('qcm.show_respond'); // Route menant au formulaire pour la résolution d'un QCM (v6 de la fonction)
        Route::put('/{id}/finished-test', [QcmController::class, 'finished_test'])->name('qcm.finished_test'); // Route pour démarrer un test de QCM (v6 de la fonction)
    });

    // PRESENCE EMPLOYE OR PARTICULIER
    Route::prefix('employes/emargements')->group(function () {
        Route::get('/projets', [EmargementEmployeController::class, 'getProjects']);
        Route::get('/projets/{idProjet}', [EmargementEmployeController::class, 'show']);
    });

    Route::prefix('employes/refresh')->group(function () {
        Route::get('/getModule', [MenuRefreshController::class, 'getModuleEmp']);
    });

    Route::prefix('/employes/module/programmes')->group(function () {
        Route::get('/menu-refresh/module/{idModule}', [ProgrammeController::class, 'getProgrammes']);
    });
});

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('qcm/index', [QcmController::class, 'index_qcm'])->name('index.qcm'); // Index des QCM
    Route::get('/qcm/invitations-index', [QcmInvitationController::class, 'index_invitation'])->name('qcm.invitations.index');
    Route::get('/qcm/show/{id}', [QcmController::class, 'show_qcm_details'])->name('show.qcm.publics');
    Route::prefix('qcm')->group(function () {
        Route::get('/{id}/list-apprenants/results', [QcmBaremeController::class, 'list_apprenants_test'])->name('qcm.results.students.list'); // Route pour avoir la liste des apprenants avec leur résultat aprs un test
        Route::get('/{id}/appr/{idAppr}/session/{idSession}/result', [QcmBaremeController::class, 'result_appr_qcm_one_session'])->name('qcm.result.one.student'); // Route pour avoir les résulats dtaillés d'un apprenant aprs un QCM (les rponses qu'il a choisi, son total de points, son niveau)
        Route::get('/{id}/appr/{idAppr}/section/{idSection}/session/{idSession}/details-results', [QcmBaremeController::class, 'result_appr_sectiondetails_one_session'])->name('qcm.result.details.one.student'); // Route pour voir les détails des choix d'un utilisateur dans une section précises
        Route::get('/spider-chart/{id}/{idQCM}/{idSession}', [QcmBaremeController::class, 'showSpiderChart'])->name('qcm.spider-chart'); // Route menant vers le diagramme en araign d'un apprenant après un test (avec vue)
        Route::get('/spider-chart-data/{id}/{idQCM}/{idSession}', [QcmBaremeController::class, 'getSpiderChartData']); // Route menant vers le diagramme en araigné d'un apprenant après un test pour les formateurs (avec modal)
        Route::get('/spider-chart-data-global/{id}/{idCtf}', [QcmBaremeController::class, 'getGlobalSpiderChartData'])->name('qcm.spider-chart.data.global'); // Route pour obtenir les donnes du graphique (avec modal)

        // invitation
    });
    Route::get('/qcm/invitations-index', [QcmInvitationController::class, 'index_invitation'])->name('qcm.invitations.index'); // Route pour afficher la liste des invitations envoyés avec filtre
    Route::get('/qcm/invitations/{id}', [QcmInvitationController::class, 'getInvitation'])->name('qcm.invitations.details');

    Route::get('/qcm/{id}/apprenant/{idApprenant}/session/{idSession}/rapport', [QcmController::class, 'getAbilitiesReport'])->name('qcm.abilities.report'); // Route pour le rapport des compétences d'un apprenant après un test qcm
    Route::get('/qcm/{id}/apprenant/{idApprenant}/session/{idSession}/rapport/pdf', [QcmController::class, 'exportAbilitiesReportPDF'])->name('qcm.abilities.report.pdf'); // Route pour exporter le rapport des compétences d'un apprenant après un test qcm en pdf

    Route::get('/qcm/{id}/category-evaluations', [QcmCategoryEvaluationController::class, 'showEvaluationForm'])->name('qcm.category.evaluations'); // Route pour afficher le formulaire pour les évaluations des catégories
    Route::get('/qcm/{idQCM}/evaluations', [QcmCategoryEvaluationController::class, 'index']); // Route pour afficher les évaluations d'un QCM par sections en pourcentage
    Route::post('/qcm-evaluations/store', [QcmCategoryEvaluationController::class, 'store']); // Route pour stocker une évaluation d'un test
    Route::put('/qcm-evaluations/{id}', [QcmCategoryEvaluationController::class, 'update']); // Route pour mettre à jour une évaluation d'un test
    Route::get('/qcm-evaluations/{id}', [QcmCategoryEvaluationController::class, 'show']); // Route pour récupérer une évaluation spécifique
    Route::delete('/qcm-evaluations/{id}', [QcmCategoryEvaluationController::class, 'destroy']); // Route pour supprimer une évaluation d'un test
    Route::get('/qcm/invitation/cfp/campaign', [QcmInvitCfpCampController::class, 'index_campaign'])->name('qcm.invitation.cfp.campaign.index'); // Route menant à l'index des campagnes d'invitation

    Route::get('/apprs', [GamificationController::class, 'index']);
});

// GAMIFICATION

Route::post('register/customer', [AccountController::class, 'store']);
Route::post('/auth/check-email', [AccountController::class, 'checkEmail']);
Route::post('/auth/check-entity-name', [AccountController::class, 'checkCustomerName']);

Route::post('/login', [LoginController::class, 'login']);
Route::middleware('auth:sanctum')->post('/logout', [LoginController::class, 'logout']);

// route required menuService Provider
Route::get('/create', [QcmController::class, 'create_qcm'])->name('create.qcm.form'); // Route menant au formulaire de création d'un QCM
Route::get('/', [QcmBaremeController::class, 'index_global_results_allQcmOfUser'])->name('ctf.qcm.globalresults.index'); // Route vers la vue des résultats globals des qcm d'un centre de formation

Route::prefix('projetsEmp')->group(function () {
    Route::get('{idModuleRessource}/download', [SupportCoursController::class, 'download'])->name('projetEmp.download');
    Route::get('/{idProjet}/download-all', [SupportCoursController::class, 'downloadAllModuleRessources'])->name('projetEmp.downloadAll');
});

// Routes pour les Formateurs, Centre de formation (Cfp) et les employés de Cfp (EmpCfp)
Route::middleware(['auth:sanctum', 'isFormtOrCfpOrEmpCfp'])->group(function () {
    // Route pour résultat global de tous les Qcm d'un CTF
    Route::prefix('global-results/qcm')->group(function () {
        Route::get('/', [QcmBaremeController::class, 'index_global_results_allQcmOfUser'])->name('ctf.qcm.globalresults.index'); // Route vers la vue des résultats globals des qcm d'un centre de formation
        Route::get('/chart-data-global/{id}', [QcmBaremeController::class, 'getGlobalChartAllQcmOfCfp'])->name('qcm.chart.data.global'); // Route pour le diagramme en baton des résultats globals
    });
    // Route pour résultat global de tous les Qcm d'un CTF

    // Routes test pour campagne d'invitation QCM (improvement to do)
    Route::get('/qcm/invitation/campaign', [QcmInvitCampController::class, 'index_campaign'])->name('qcm.invitation.campaign.index'); // Route menant à l'index des campagnes d'invitation
    Route::get('/qcm/invitation/{id}', [QcmInvitCampController::class, 'getInvitationDetails'])->name('qcm.invitation.details'); // Route pour avoir une invitation
    Route::delete('/qcm/invit-camp/{id}', [QcmInvitCampController::class, 'destroy'])->name('qcm.invit-camp.destroy'); // Route pour supprimer une campagne

    Route::get('qcm/invitation/cfp/create', [QcmInvitationCfpController::class, 'create_invitation'])->name('qcm.invitation.cfp.create');
    Route::prefix('qcm/invitation/campaign')->name('qcm.invitation.campaign.')->group(function () {
        // Step 1: Campaign Name
        Route::get('/step-one', [QcmInvitCampController::class, 'stepOne'])->name('step-one'); // Route menant vers la vue de l'étape 1
        Route::post('/step-one', [QcmInvitCampController::class, 'storeStepOne'])->name('store-step-one'); // Route pour stocker les choix de l'étape 1 en session
        Route::post('/save-draft-name', [QcmInvitCampController::class, 'saveDraftName'])->name('save-draft-name'); // Route pour sauvegarder le nom de la campagne en session

        // Step 2: QCM Selection
        Route::get('/step-two', [QcmInvitCampController::class, 'stepTwo'])->name('step-two'); // Route menant vers la vue de l'étape 2
        Route::post('/step-two', [QcmInvitCampController::class, 'storeStepTwo'])->name('store-step-two'); // Route pour stocker les choix de l'étape 2 en session

        // Step 3: Employee Selection
        Route::get('/step-three', [QcmInvitCampController::class, 'stepThree'])->name('step-three'); // Route menant vers la vue de l'étape 3
        Route::post('/step-three', [QcmInvitCampController::class, 'storeStepThree'])->name('store-step-three'); // Route pour stocker les choix de l'étape 3 en session (fonction non disponible)
        Route::post('/ajax-update-employees', [QcmInvitCampController::class, 'ajaxUpdateEmployees'])->name('ajax-update-employees'); // Rpute pour l ajax de l employe

        // Step 4 : Dates (Valid From and Valid To) with optionnal messages
        Route::post('/invitation-campaign/save-step-four-data', [QcmInvitCampController::class, 'saveStepFourData'])->name('save-step-four-data'); // Route pour sauvegarder les données de l'étape 4
        Route::get('/step-four', [QcmInvitCampController::class, 'stepFour'])->name('step-four'); // Route menant vers la vue de l'étape 4

        Route::post('/create', [QcmInvitCampController::class, 'createCampaign'])->name('create'); // Route pour créer la campagne d invitation

        Route::get('/back-to-step-one', [QcmInvitCampController::class, 'backToStepOne'])->name('back-to-step-one'); // Go back routes to step one
        Route::get('/back-to-step-two', [QcmInvitCampController::class, 'backToStepTwo'])->name('back-to-step-two'); // Go back routes to step two
        Route::get('/back-to-step-three', [QcmInvitCampController::class, 'backToStepThree'])->name('back-to-step-three'); // Go back routes to step three
    });

    // New invitation route for the CFP
    Route::prefix('qcm/invitation/cfp/campaign')->name('qcm.invitation.cfp.campaign.')->group(function () {
        // Step 1: Campaign Name
        Route::get('/step-one', [QcmInvitCfpCampController::class, 'stepOne'])->name('step-one'); // Route menant vers la vue de l'étape 1
        Route::post('/step-one', [QcmInvitCfpCampController::class, 'storeStepOne'])->name('store-step-one'); // Route pour stocker les choix de l'étape 1 en session
        Route::post('/save-draft-name', [QcmInvitCfpCampController::class, 'saveDraftName'])->name('save-draft-name'); // Route pour sauvegarder le nom de la campagne en session

        // Step 2: QCM Selection
        Route::get('/step-two', [QcmInvitCfpCampController::class, 'stepTwo'])->name('step-two'); // Route menant vers la vue de l'étape 2
        Route::post('/step-two', [QcmInvitCfpCampController::class, 'storeStepTwo'])->name('store-step-two'); // Route pour stocker les choix de l'étape 2 en session

        // Step 3: Employee Selection
        Route::get('/step-three', [QcmInvitCfpCampController::class, 'stepThree'])->name('step-three'); // Route menant vers la vue de l'étape 3
        Route::post('/step-three', [QcmInvitCfpCampController::class, 'storeStepThree'])->name('store-step-three'); // Route pour stocker les choix de l'étape 3 en session (fonction non disponible)
        Route::post('/ajax-update-employees', [QcmInvitCfpCampController::class, 'ajaxUpdateEmployees'])->name('ajax-update-employees'); // Rpute pour l ajax de l employe

        // Step 4 : Dates (Valid From and Valid To) with optionnal messages
        Route::post('/invitation-campaign/save-step-four-data', [QcmInvitCfpCampController::class, 'saveStepFourData'])->name('save-step-four-data'); // Route pour sauvegarder les données de l'étape 4
        Route::get('/step-four', [QcmInvitCfpCampController::class, 'stepFour'])->name('step-four'); // Route menant vers la vue de l'étape 4

        Route::post('/create', [QcmInvitCfpCampController::class, 'createCampaign'])->name('create'); // Route pour créer la campagne d invitation

        Route::get('/back-to-step-one', [QcmInvitCfpCampController::class, 'backToStepOne'])->name('back-to-step-one'); // Go back routes to step one
        Route::get('/back-to-step-two', [QcmInvitCfpCampController::class, 'backToStepTwo'])->name('back-to-step-two'); // Go back routes to step two
        Route::get('/back-to-step-three', [QcmInvitCampController::class, 'backToStepThree'])->name('back-to-step-three'); // Go back routes to step three
    });

    Route::get('/qcm/{id}/category-evaluations', [QcmCategoryEvaluationController::class, 'showEvaluationForm'])->name('qcm.category.evaluations'); // Route pour afficher le formulaire pour les évaluations des catégories
    Route::get('/qcm/{idQCM}/evaluations', [QcmCategoryEvaluationController::class, 'index']); // Route pour afficher les évaluations d'un QCM par sections en pourcentage
    Route::post('/qcm-evaluations/store', [QcmCategoryEvaluationController::class, 'store']); // Route pour stocker une évaluation d'un test
    Route::put('/qcm-evaluations/{id}', [QcmCategoryEvaluationController::class, 'update']); // Route pour mettre à jour une évaluation d'un test
    Route::get('/qcm-evaluations/{id}', [QcmCategoryEvaluationController::class, 'show']); // Route pour récupérer une évaluation spécifique
    Route::delete('/qcm-evaluations/{id}', [QcmCategoryEvaluationController::class, 'destroy']); // Route pour supprimer une évaluation d'un test
    // Routes pour les CRUD des évaluations de niveau selon les pourcentages des points obtenues lors d'un test

    // Route::post('qcm/question/{idQuestion}/upload-image', [QcmImages::class, 'uploadQuestionPhoto']); // Route pour uploader une image pour une question
    // Route::delete('qcm/question-image/{idImageQ}/delete', [QcmImages::class, 'deleteQuestionPhoto']); // Route pour supprimer une image d'une question

    Route::prefix('qcm')->group(function () {
        Route::post('/invitations/cfp', [QcmInvitationCfpController::class, 'store_invitation'])->name('qcm.invitations.cfp.store');

        // Routes pour CRUD QCM
        Route::get('/create', [QcmController::class, 'create_qcm'])->name('create.qcm.form'); // Route menant au formulaire de création d'un QCM
        Route::post('/store', [QcmController::class, 'storeQcm'])->name('store.qcm'); // Route mettre la création du qcm effective
        Route::get('/{id}/edit', [QcmController::class, 'edit_qcm'])->name('qcm.edit'); // Route vers le formulaire pour mettre à jour un QCM
        Route::post('/{id}', [QcmController::class, 'update_qcm'])->name('qcm.update'); // Route pour mettre la mise à jour effective
        Route::delete('/{id}/delete', [QcmController::class, 'destroy_qcm'])->name('qcm.destroy'); // Route pour supprimer un qcm avec ses questions et réponses
        Route::post('/{id}/update-status', [QcmController::class, 'updateStatus'])->name('qcm.update.status'); // Route pour le toggle button des qcm pour les mettrent actif ou non actif
        // Routes pour CRUD QCM
        // Routes pour les barèmes
        Route::get('/bareme/create/{id}', [QcmBaremeController::class, 'create_qcm_bareme'])->name('qcm.bareme.create'); // Route vers la vue pour créer / modifier le barème d'un qcm
        Route::get('/get/{id}', [QcmBaremeController::class, 'getBaremes']); // Route pour avoir les barèmes d'un qcm
        Route::get('/qcm_bareme/{id}', [QcmBaremeController::class, 'getBareme']); // Appelée directement dans la vue "create_qcm_bareme"
        Route::post('/qcm-bareme/store/{id}', [QcmBaremeController::class, 'storeQcmBareme']); // Appelée directement dans la vue "create_qcm_bareme"
        Route::post('/qcm-bareme/update/{id}', [QcmBaremeController::class, 'updateQcmBareme']); // Appelée directement dans la vue "create_qcm_bareme"
        Route::delete('/qcm-bareme/delete/{id}', [QcmBaremeController::class, 'deleteQcmBareme']); // Appelée directement dans la vue "create_qcm_bareme"
        // Routes pour les barèmes

        // Routes Resultats
    });
});
Route::get('/formation/exportPdf/{id}', [ClientController::class, 'exportPdf'])->name('formation.exportPdf');
