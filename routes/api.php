<?php

use Illuminate\Support\Facades\Route;

// Шаг 1 - Сохранение документа
Route::post('/documents', [\Mitwork\Kalkan\Http\Controllers\TestController::class, 'store'])->name('store-document');

// Шаг 1.1 - Генерация QR-кода
Route::get('/documents/generate-qr-code', [\Mitwork\Kalkan\Http\Controllers\TestController::class, 'generateQrCode'])->name('generate-qr-code');

// Щаг 1.2 - Генерация кросс-ссылок
Route::get('/documents/generate-cross-link', [\Mitwork\Kalkan\Http\Controllers\TestController::class, 'generateCrossLink'])->name('generate-cross-link');

// Щаг 2 - Генерация сервисных данных
Route::get('/documents/generate-link', [\Mitwork\Kalkan\Http\Controllers\TestController::class, 'generateLink'])->name('generate-link');

// Шаг 3 - Работа с данными - отдача и сохранение
Route::any('/documents/content', [\Mitwork\Kalkan\Http\Controllers\TestController::class, 'prepareContent'])->name('prepare-content');
