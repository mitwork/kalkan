## Шаг 2.1 - Генерация сервисной ссылки

На данном шаге можно получить сервисную ссылку по идентификатору запроса. Шаг является опциональным, поскольку все
необходимые данные можно получить в [Шаге 2 - Формирование запроса](STEP_20_STORE_REQUEST.md).

Данная сервисная ссылка используется при QR и кросс-подписании мобильными приложениями eGov mobile и eGov business.

### Пример запроса

`POST` /documents/generate-service-link?id=acba8198-92d9-4297-905f-eb55ea69f9c4

где:

 - `id` - уникальный идентификатор документа из шага 2.

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
