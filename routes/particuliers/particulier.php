<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ParticulierController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\CreditsPacksController;

Route::middleware(['auth', 'isParticulier'])->group(function () {
    // Route::get('homeParticulier', [HomeController::class, 'indexParticulier'])->name('homeParticulier');
    Route::get('homeParticulier', [ClientController::class, 'indexFormation']);
    Route::get('/particulier/projet', [ParticulierController::class, 'projetParticulier'])->name('particulier.projet');
    Route::get('/particulier/agendaForms', [ParticulierController::class, 'calendarParticulier'])->name('agenda.particulier');

    //Projets particuliers
    Route::get('projetParticuliers', [ParticulierController::class, 'indexPart'])->name('projets.particulier.indexPart');
    Route::get('projetsParticuliers/{idProjet}/detailPart', [ParticulierController::class, 'show'])->name('projets.particulier.detail');
    Route::get('projetsParticuliers/{idProjet}/getFormAdded', [ParticulierController::class, 'getFormAdded'])->name('projets.particulier.getFormAdded');
    Route::get('projetsParticuliers/formateur/{id}/mini-cv', [ParticulierController::class, 'getMiniCV']);

    # Routes Testing Center
    # Routes pour l'achat de crédits pour les particuliers
    Route::prefix('/particulier')->group(function () {
        Route::get('/credits-pack/buy', [CreditsPacksController::class, 'index_buy_credits_pack'])->name('credits.index.particulier'); # Route pour afficher les packs de crédits disponible à l'achat pour les particuliers
        Route::get('/credits/{id}/recap', [CreditsPacksController::class, 'recapPurchase'])->name('credits.recap.particulier'); # Route pour afficher le recap d'un pack de crédits disponible à l'achat, avant de l'acheter pour les particuliers
        Route::post('/credits/{id}/process', [CreditsPacksController::class, 'processPurchase'])->name('credits.process.particulier'); # Route pour procéder à l'achat de crédits pour les particuliers
        Route::get('/credits/history', [CreditsPacksController::class, 'history'])->name('credits.history.particulier'); # Route pour voir l'historique d'achat de crédits pour les particuliers
    });
    # Routes pour l'achat de crédits pour les particuliers
    # Routes Testing Center
});