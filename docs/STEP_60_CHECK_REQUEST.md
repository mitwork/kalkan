## Шаг 5 - Проверка статуса обработки запроса

На данном шаге можно получить статус запроса по его уникальному идентификатору из [Шага 2 - отправка документов](STEP_20_STORE_REQUEST.md).

Данный шаг необходим для изменения состояния приложения (редиректа) для пользователя, работающего с запросом.

### Пример запроса

`GET` /requests/check?id=acba8198-92d9-4297-905f-eb55ea69f9c4

### Пример ответа

```json
{
    "status": "created"
}
```

где:

- `status` - статус обработки запроса.

Допустимые статусы отражены в файле [Mitwork\Kalkan\Enums\RequestStatus.php](../src/Enums/RequestStatus.php):

```php
<?php

namespace Mitwork\Kalkan\Enums;

enum RequestStatus: string
{
    case CREATED = 'created';
    case PROGRESS = 'progress';
    case PROCESSED = 'processed';
    case REJECTED = 'rejected';
}
```

### Реализация

Пример реализации [Mitwork\Kalkan\Http\Actions\CheckRequest.php](../src/Http/Actions/CheckRequest.php)

Для использования реализации из текущего пакета необходимо добавить соответсвующий маршрут (`route`) в приложение:

```php
<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/requests/check/{id}', [\Mitwork\Kalkan\Http\Actions\CheckRequest::class, 'check'])->name(config('kalkan.actions.check-request'));
```

Так же, можно реализовать собственную логику данного шага, указав к конфигурации собственный именованный маршрут (`route`):

```php
// ...
'actions' => [
    'check-request' => 'custom-check-request',
]
// ..
```

Либо переопределить используемый класс и метод в маршрутах.
