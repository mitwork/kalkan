<?php

use Illuminate\Support\Facades\Route;

Route::post('/document/prepare', [\Mitwork\Kalkan\Http\Controllers\TestController::class, 'prepareDocument'])->name('prepare-document');
Route::any('/document/content', [\Mitwork\Kalkan\Http\Controllers\TestController::class, 'prepareContent'])->name('prepare-content');

Route::get('/document/generate-qr-code', [\Mitwork\Kalkan\Http\Controllers\TestController::class, 'generateQrCode'])->name('generate-qr-code');
Route::get('/document/generate-cross-link', [\Mitwork\Kalkan\Http\Controllers\TestController::class, 'generateCrossLink'])->name('generate-cross-link');
Route::get('/document/generate-link', [\Mitwork\Kalkan\Http\Controllers\TestController::class, 'generateLink'])->name('generate-link');
