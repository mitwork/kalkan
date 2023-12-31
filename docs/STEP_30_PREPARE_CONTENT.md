## Шаг 3 - Работа с контентом документов

На данном шаге можно получить данные подписываемых документов для мобильного приложения по уникальному идентификатору запроса из [Шага 2 - Формирование запроса](STEP_20_STORE_REQUEST.md).

Документы используется при QR и кросс-подписании мобильными приложениями eGov mobile и eGov business.

### Пример запроса

**Важно:** данный запрос инициируется внешним приложением.

`GET` /api/requests/content/acba8198-92d9-4297-905f-eb55ea69f9c4

где:

 - `id` - уникальный идентификатор документа из шага 2.

### Примеры ответов

**Работы с файлами (CMS)**

```json
{
    "signMethod": "CMS",
    "documentsToSign": [
        {
            "id": "134372667717125",
            "nameRu": "document.docx",
            "nameKz": "document.docx",
            "nameEn": "document.docx",
            "meta": [
                {
                    "name": "Наименование документа",
                    "value": "Договор для тестирования"
                }
            ]
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
            "id": "134372667717125",
            "nameRu": "document.xml",
            "nameKz": "document.xml",
            "nameEn": "document.xml",
            "meta": [
                {
                    "name": "Наименование документа",
                    "value": "Электронная форма"
                }
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

Route::get('/requests/content/{id}', [\Mitwork\Kalkan\Http\Actions\PrepareContent::class, 'prepare'])->name(config('kalkan.actions.prepare-content'));
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
