## Шаг 2 - генерация сервисных данных

На данном шаге можно получить сервисную ссылку для мобильного приложения по уникальному идентификатору документа из [Шаг 1 - подготовка документа](STEP_10_STORE_DOCUMENT.md).

Данная сервисная ссылка используется при QR и кросс-подписании мобильными приложениями eGov mobile и eGov business.

### Пример запроса

`POST` /documents/generate-service-link?id=acba8198-92d9-4297-905f-eb55ea69f9c4

где:

 - `id` - уникальный идентификатор документа из шага 1.

### Пример ответа

```json
{
    "description": "Test",
    "expiry_date": "2023-10-16T05:29:00+00:00",
    "organisation": {
        "nameRu": "АО ТЕСТ",
        "nameKz": "ТЕСТ АК",
        "nameEn": "JS TEST",
        "bin": "123456789012"
    },
    "document": {
        "uri": "http:\/\/localhost\/api\/documents\/content?expires=1697434323&id=acba8198-92d9-4297-905f-eb55ea69f9c4&signature=e8ad6b13467532f02f3b54e7bd428939a5ac99d639b322294d281ff02961ab8f",
        "auth_type": "None",
        "auth_token": ""
    }
}
```

где:

- `document.uri` - уникальная ссылка для получения подписываемых и передачи подписанных документов.

**Важно:** эта же ссылка используется и при работе с подписанными документами методом `PUT`.

Прочие параметры являются статичными и берутся из настроек `config/kalkan.php`.

### Реализация

Пример реализации [Mitwork\Kalkan\Http\Actions\GenerateServiceLink.php](../src/Http/Actions/GenerateServiceLink.php)

Для использования реализации из текущего пакета необходимо добавить соответсвующий маршрут (`route`) в приложение:

```php
<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/documents/generate-service-link', [\Mitwork\Kalkan\Http\Actions\GenerateServiceLink::class, 'generate'])->name(config('kalkan.actions.generate-service-link'));
```

Так же, можно реализовать собственную логику данного шага, указав к конфигурации собственный именованный маршрут (`route`):

```php
// ...
'actions' => [
    'generate-service-link' => 'custom-generate-service-link',
]
// ..
```

Либо переопределить используемый класс и метод в маршрутах.
