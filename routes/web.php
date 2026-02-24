<?php

use App\Http\Controllers\SupportCoursController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\File;

Route::get('/{any}', function () {
    $path = public_path('react/index.html');

    if (!File::exists($path)) {
        abort(404, 'React app not found.');
    }

    return response()->file($path, [
        'Content-Type' => 'text/html',
    ]);
})->where('any', '.*');
