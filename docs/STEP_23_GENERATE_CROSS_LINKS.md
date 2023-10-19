## Шаг 1.2 - Формирование кросс-ссылок

На данном шаге можно получить кросс-ссылки для подписания по уникальному идентификатору документа. Шаг является опциональным, поскольку все
необходимые данные можно получить в [Шаге 2 - Формирование запроса](STEP_20_STORE_REQUEST.md).

### Пример запроса

`GET` /api/requests/cross-links/acba8198-92d9-4297-905f-eb55ea69f9c4

где:

 - `id` - уникальный идентификатор документа из шага 1.

### Пример ответа

```json
{
    "mobile": "https://mgovsign.page.link/?link=http%3A%2F%2Flocalhost%2Fdocuments%2Fgenerate-link%3Fexpires%3D1697428841%26id%3D134372667717125%26signature%3D388119f23e5cdec7e7c7a58476b3aa7953ec5bb2f6c9b47b411e08222ee5e9eb&isi=1476128386&ibi=kz.egov.mobile&apn=kz.mobile.mgov",
    "business": "https://egovbusiness.page.link/?link=http%3A%2F%2Flocalhost%2Fdocuments%2Fgenerate-link%3Fexpires%3D1697428841%26id%3D134372667717125%26signature%3D388119f23e5cdec7e7c7a58476b3aa7953ec5bb2f6c9b47b411e08222ee5e9eb&isi=1597880144&ibi=kz.mobile.mgov.business&apn=kz.mobile.mgov.business"
}
```

### Реализация

Пример реализации [Mitwork\Kalkan\Http\Actions\GenerateCrossLink,php](../src/Http/Actions/GenerateCrossLink.php)

Для использования реализации из текущего пакета необходимо добавить соответсвующий маршрут (`route`) в приложение:

```php
<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/requests/cross-links/{id}', [\Mitwork\Kalkan\Http\Actions\GenerateCrossLink::class, 'generate'])->name(config('kalkan.actions.generate-cross-links'));
```

Так же, можно реализовать собственную логику данного шага, указав к конфигурации собственный именованный маршрут (`route`):

```php
// ...
'actions' => [
    'generate-cross-links' => 'custom-generate-cross-links',
]
// ..
```

Либо переопределить используемый класс и метод в маршрутах.
