<?php

use Illuminate\Support\Facades\Route;
use Mitwork\Kalkan\Http\Actions\CheckDocument;
use Mitwork\Kalkan\Http\Actions\CheckRequest;
use Mitwork\Kalkan\Http\Actions\GenerateCrossLink;
use Mitwork\Kalkan\Http\Actions\GenerateQrCode;
use Mitwork\Kalkan\Http\Actions\GenerateServiceLink;
use Mitwork\Kalkan\Http\Actions\PrepareContent;
use Mitwork\Kalkan\Http\Actions\ProcessContent;
use Mitwork\Kalkan\Http\Actions\StoreDocument;
use Mitwork\Kalkan\Http\Actions\StoreRequest;

// Шаг 1 - Загрузка документов
Route::post('/documents', [StoreDocument::class, 'store'])->name(config('kalkan.actions.store-document'));

// Шаг 2 - Формирование запроса
Route::post('/requests', [StoreRequest::class, 'store'])->name(config('kalkan.actions.store-request'));

// Шаг 2.1 - Генерация сервисной ссылки
Route::get('/requests/generate/{id}', [GenerateServiceLink::class, 'generate'])->name(config('kalkan.actions.generate-service-link'));

// Шаг 2.2 - Формирование QR-кода
Route::get('/requests/qr-code/{id}', [GenerateQrCode::class, 'generate'])->name(config('kalkan.actions.generate-qr-code'));

// Щаг 2.3 - Формирование кросс-ссылок
Route::get('/requests/links/{id}', [GenerateCrossLink::class, 'generate'])->name(config('kalkan.actions.generate-cross-links'));

// Шаг 3 - Работа с данными - отдача
Route::get('/requests/{id}', [PrepareContent::class, 'prepare'])->name(config('kalkan.actions.prepare-content'));

// Шаг 4 - Работа с данными - обработка
Route::put('/requests/{id}', [ProcessContent::class, 'process'])->name(config('kalkan.actions.process-content'));

// Шаг 5 - Проверка статуса подписания документа
Route::get('/check/document/{id}', [CheckDocument::class, 'check'])->name(config('kalkan.actions.check-document'));

// Шаг 6 - Проверка статуса заявки
Route::get('/check/request/{id}', [CheckRequest::class, 'check'])->name(config('kalkan.actions.check-request'));
