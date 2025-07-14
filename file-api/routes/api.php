<?php
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\FileController;

Route::prefix('files')->group(function () {
    Route::post('/', [FileController::class, 'upload']);
    Route::get('/', [FileController::class, 'index']);
    Route::delete('/{id}', [FileController::class, 'destroy']);
    Route::get('/{id}/preview', [FileController::class, 'preview'])->name('files.preview');
});