<?php

use Illuminate\Support\Facades\Route;

// Шаг 1 - Сохранение документа
Route::post('/documents', [\Mitwork\Kalkan\Http\Controllers\DocumentsController::class, 'store'])->name('store-document');

// Шаг 1.1 - Генерация QR-кода
Route::get('/documents/generate-qr-code', [\Mitwork\Kalkan\Http\Controllers\DocumentsController::class, 'generateQrCode'])->name('generate-qr-code');

// Щаг 1.2 - Генерация кросс-ссылок
Route::get('/documents/generate-cross-link', [\Mitwork\Kalkan\Http\Controllers\DocumentsController::class, 'generateCrossLink'])->name('generate-cross-link');

// Щаг 2 - Генерация сервисных данных
Route::get('/documents/generate-link', [\Mitwork\Kalkan\Http\Controllers\DocumentsController::class, 'generateLink'])->name('generate-link');

// Шаг 3 - Работа с данными - отдача
Route::get('/documents/content', [\Mitwork\Kalkan\Http\Controllers\DocumentsController::class, 'prepareContent'])->name('prepare-content');

// Шаг 4 - Работа с данными - обработка
Route::put('/documents/content', [\Mitwork\Kalkan\Http\Controllers\DocumentsController::class, 'processContent'])->name('process-content');

// Шаг 5 - Проверка статуса подписания документа
Route::get('/documents/check/{id}', [\Mitwork\Kalkan\Http\Controllers\DocumentsController::class, 'check'])->name('check-document');
