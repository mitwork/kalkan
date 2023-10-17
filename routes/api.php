<?php

use Illuminate\Support\Facades\Route;

// Шаг 1 - Сохранение документа
Route::post('/documents', [\Mitwork\Kalkan\Http\Actions\StoreDocument::class, 'store'])->name(config('kalkan.actions.store-document'));

// Шаг 2 - Сохранение запроса
Route::post('/requests', [\Mitwork\Kalkan\Http\Actions\StoreRequest::class, 'store'])->name(config('kalkan.actions.store-request'));

// Шаг 1.1 - Генерация QR-кода
Route::get('/documents/generate-qr-code', [\Mitwork\Kalkan\Http\Actions\GenerateQrCode::class, 'generate'])->name(config('kalkan.actions.generate-qr-code'));

// Щаг 1.2 - Генерация кросс-ссылок
Route::get('/documents/generate-cross-links', [\Mitwork\Kalkan\Http\Actions\GenerateCrossLink::class, 'generate'])->name(config('kalkan.actions.generate-cross-links'));

// Щаг 2 - Генерация сервисной ссылки
Route::get('/documents/generate-service-link', [\Mitwork\Kalkan\Http\Actions\GenerateServiceLink::class, 'generate'])->name(config('kalkan.actions.generate-service-link'));

// Шаг 3 - Работа с данными - отдача
Route::get('/documents/content', [\Mitwork\Kalkan\Http\Actions\PrepareContent::class, 'prepare'])->name(config('kalkan.actions.prepare-content'));

// Шаг 4 - Работа с данными - обработка
Route::put('/documents/content', [\Mitwork\Kalkan\Http\Actions\ProcessContent::class, 'process'])->name(config('kalkan.actions.process-content'));

// Шаг 5 - Проверка статуса подписания документа
Route::get('/documents/check/{id}', [\Mitwork\Kalkan\Http\Actions\CheckDocument::class, 'check'])->name(config('kalkan.actions.check-document'));

// Шаг 6 - Проверка статуса заявки
Route::get('/requests/check/{id}', [\Mitwork\Kalkan\Http\Actions\CheckRequest::class, 'check'])->name(config('kalkan.actions.check-request'));
