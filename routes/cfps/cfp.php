<?php

use App\Http\Controllers\AgenceController;
use App\Http\Controllers\EmployecfpController;
use App\Http\Controllers\LieuxController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'isCfp'])->group(function () {
    // Referents
    Route::prefix('cfp/referents')->group(function () {
        Route::post('/', [EmployecfpController::class, 'store'])->name('cfp.referents.store');
        Route::delete('/{id}', [EmployecfpController::class, 'destroy'])->name('cfp.referents.destroy');
    });

    // Agences
    Route::post('cfp/agences', [AgenceController::class, 'store'])->name('cfp.agences.store');
    Route::delete('cfp/agences/{id}', [AgenceController::class, 'destroy'])->name('cfp.agences.destroy');
});
