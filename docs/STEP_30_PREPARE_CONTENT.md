## Шаг 3 - работа с контентом документа

На данном шаге можно получить данные подписываемых документов для мобильного приложения по уникальному идентификатору документа из [Шаг 1 - подготовка документа](STEP_10_STORE_DOCUMENT.md).

Данные документов используется при QR и кросс-подписании мобильными приложениями eGov mobile и eGov business.

### Пример запроса

**Важно:** данный запрос инициируется внешним приложением.

`GET` /documents/prepare-content?id=acba8198-92d9-4297-905f-eb55ea69f9c4

где:

 - `id` - уникальный идентификатор документа из шага 1.

### Примеры ответов

**Работы с файлами (CMS)**

```json
{
    "signMethod": "CMS",
    "documentsToSign": [
        {
            "id": "acba8198-92d9-4297-905f-eb55ea69f9c4",
            "nameRu": "document.docx",
            "nameKz": "document.docx",
            "nameEn": "document.docx",
            "meta": [{
                "name": "0",
                "value": {
                    "mime": "application/vnd.openxmlformats-officedocument.wordprocessingml.document"
                }
            }],
            "documentCms": "base64..."
        }
    ]
}
```

**Работы с формами (XML)**

```json
{
    "signMethod": "XML",
    "documentsToSign": [
        {
            "id": "acba8198-92d9-4297-905f-eb55ea69f9c4",
            "nameRu": "document.xml",
            "nameKz": "document.xml",
            "nameEn": "document.xml",
            "meta": [
                {"name": "mime", "value": "text\/xml"}
            ],
            "documentXml": "<?xml..."
        }
    ]
}
```

где:

- `signMethod` - способ подписания;
- `documentsToSign` - массив документов для подписания.

### Реализация

Пример реализации [Mitwork\Kalkan\Http\Actions\PrepareContent.php](../src/Http/Actions/PrepareContent.php)

Для использования реализации из текущего пакета необходимо добавить соответсвующий маршрут (`route`) в приложение:

```php
<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/documents/content', [\Mitwork\Kalkan\Http\Actions\PrepareContent::class, 'prepare'])->name(config('kalkan.actions.prepare-content'));
```

Так же, можно реализовать собственную логику данного шага, указав к конфигурации собственный именованный маршрут (`route`):

```php
// ...
'actions' => [
    'prepare-content' => 'custom-prepare-content',
]
// ..
```

Либо переопределить используемый класс и метод в маршрутах.
