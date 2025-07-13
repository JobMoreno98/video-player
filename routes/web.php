<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ArchivoController;
use App\Http\Controllers\ChunkUploadController;
use App\Http\Controllers\VideosController;
use App\Http\Controllers\VideoStreamController;
use Illuminate\Support\Facades\URL;



Route::view('/', 'welcome');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');



Route::middleware(['auth'])->group(function () {
    Route::get('/subir', [ArchivoController::class, 'formulario']);
    Route::post('/subir', [ArchivoController::class, 'subir'])->name('archivo.subir');

    Route::get('/upload', fn() => view('subir-archivo'));
    Route::post('/upload-chunk-manual', [ChunkUploadController::class, 'upload'])->name('archivo.chunk');



    Route::get('/video/{filename}', [VideoStreamController::class, 'stream'])->name('video.stream');

    Route::get('/video/playlist/{playlist}', [VideoStreamController::class, 'playlist'])
        ->name('video.playlist')->middleware('signed');

    Route::get('/video/key/{key}', [VideoStreamController::class, 'key'])
        ->name('video.key');

    Route::get('/video/segment/{segment}', [VideoStreamController::class, 'segment'])
        ->name('video.segment');


    Route::get('/keys/{name}.key', function ($name) {
        $path = storage_path("app/keys/{$name}.key");

        if (!file_exists($path)) {
            abort(404);
        }

        return response()->file($path, [
            'Content-Type' => 'application/octet-stream',
            'Content-Disposition' => 'inline; filename="' . $name . '.key"',
        ]);
    });

    Route::resource('/videos-reproductor', VideosController::class)->names('videos-reproductor');
});


require __DIR__ . '/auth.php';
