<?php

use App\Http\Controllers\AgendaFormInterneController;
use App\Http\Controllers\ApprenantController;
use App\Http\Controllers\EmargementController;
use App\Http\Controllers\FiltreFormInterneController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProjetController;
use App\Http\Controllers\RessourceController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'isFormateurInterne'])->group(function () {
    Route::get('homeFormInterne', [HomeController::class, 'indexFormInterne']);

    // ProjetFormInternes
    Route::get('projetFormInternes', [ProjetController::class, 'indexFormInterne'])->name('projetFormInternes.indexFormInterne');
    Route::get('/api/calendar-events', [ProjetController::class, 'getEvents']);
    Route::get('projetFormInternes/{idProjet}', [ProjetController::class, 'detailFormInterne'])->name('projetFormInternes.detailFormInterne');
    Route::get('apprenantFormInterne/{idProjet}/{idSession}', [ApprenantController::class, 'getAllApprenantFormInterne']);

    //Entreprises
    Route::get('entrepriseFormInterne', [FiltreFormInterneController::class, 'index'])->name('etpFormInt.index');

    // AgendaFormInterne
    Route::get('agendaFormInternes', [AgendaFormInterneController::class, 'index'])->name('agenda.formInterne');

    //Filtre FormInternes
    Route::get('/formInterne/filter/getDropdownItem', [FiltreFormInterneController::class, 'getDropdownItem'])->name('formateurInterne.filter.getDropdownItem');
    Route::get('/formInterne/filter/items', [FiltreFormInterneController::class, 'filterItems'])->name('formateurInterne.filter.filterItems');
    Route::get('/formInterne/filter/item', [FiltreFormInterneController::class, 'filterItem'])->name('formateurInterne.filter.filterItem');

    // Emargement(formateurInterne)
    Route::get('emargementInternes/{idProjet}/{idEmp}/{idSession}/{idSeance}/show', [EmargementController::class, 'show']);
    Route::post('emargementInternes', [EmargementController::class, 'store'])->name('emargementInternes.store');

    // Ajax emg
    Route::get('checkFinish/{idSeance}/{idEmploye}', [ProjetController::class, 'checkFinishF']);

    // Ressources
    Route::get('ressources/formInterne/{idSession}', [RessourceController::class, 'indexFormInterne']);
});