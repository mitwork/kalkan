## Шаг 1 - подготовка документа

На данном шаге необходимо передать наименование файла, его содержимое, а так же тип подписания.
В предлагаемой реализации файл сохраняется в кэш.

### Пример запроса

`POST` /documents

```json
{
    "name":"document.docx",
    "content": "base64...",
    "meta": [
        {
            "mime": "application/vnd.openxmlformats-officedocument.wordprocessingml.document"
        }
    ],
    "type": "cms"
}
```

где:

- `id` - уникальный идентификатор документа для последующих запросов (необязательно);
- `name` - наименование документа;
- `content` - содержимое документа (XML, либо base64 для прочих документов);
- `meta` - массив дополнительных атрибутов (необязательно);
- `type` - тип подписания (`cms`, `xml`);
- `auth.type` - тип авторизации для сервисной ссылки (`None` - по-умолчанию, либо `Bearer`);
- `auth.token` - токен авторизации (при использовании `Bearer`).

В случае с работой с XML-данными пример запроса будут следующим:

```json
{
    "name":"document.xml",
    "content": "<?xml...",
    "meta": [
        {
            "mime": "text/xml"
        }
    ],
    "type": "xml"
}
```

### Пример ответа

```json
{
    "id": "acba8198-92d9-4297-905f-eb55ea69f9c4",
    "url": "http://localhost/documents/generate-link?expires=1697428841&id=134372667717125&signature=388119f23e5cdec7e7c7a58476b3aa7953ec5bb2f6c9b47b411e08222ee5e9eb",
    "links": {
        "qr": {
            "uri": "data:image/png;base64,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",
            "raw": "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"
        },
        "app": {
            "mobile": "https://mgovsign.page.link/?link=http%3A%2F%2Flocalhost%2Fdocuments%2Fgenerate-link%3Fexpires%3D1697428841%26id%3D134372667717125%26signature%3D388119f23e5cdec7e7c7a58476b3aa7953ec5bb2f6c9b47b411e08222ee5e9eb&isi=1476128386&ibi=kz.egov.mobile&apn=kz.mobile.mgov",
            "business": "https://egovbusiness.page.link/?link=http%3A%2F%2Flocalhost%2Fdocuments%2Fgenerate-link%3Fexpires%3D1697428841%26id%3D134372667717125%26signature%3D388119f23e5cdec7e7c7a58476b3aa7953ec5bb2f6c9b47b411e08222ee5e9eb&isi=1597880144&ibi=kz.mobile.mgov.business&apn=kz.mobile.mgov.business"
        }
    }
}
```

где:

- `id` - уникальный идентификатор документа, в случае отсутствия его в запросе, формируется `uuid4`;
- `url` - сервисная ссылка, используемая при формировании QR-кода и кросс-ссылок;
- `links.qr.uri` - изображение QR-кода для отображения в браузере;
- `links.qr.raw` - изображение QR-кода в исходном формате;
- `links.app.mobile` - кросс-ссылка для подписания в приложении eGov mobile (для индивидуальных предпринимателей и физических лиц);
- `links.app.mobile` - кросс-ссылка для подписания в приложении eGov business (для юридических лиц).

### Реализация

Пример реализации [Mitwork\Kalkan\Http\Actions\StoreDocument.php](../src/Http/Actions/StoreDocument.php)

Для использования реализации из текущего пакета необходимо добавить соответсвующий маршрут (`route`) в приложение:

```php
<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/documents', [\Mitwork\Kalkan\Http\Actions\StoreDocument::class, 'store'])->name(config('kalkan.actions.store-document'));
```

Так же, можно реализовать собственную логику данного шага, указав к конфигурации собственный именованный маршрут (`route`):

```php
// ...
'actions' => [
    'store-document' => 'custom-store-document',
]
// ..
```

Либо переопределить используемый класс и метод в маршрутах.
