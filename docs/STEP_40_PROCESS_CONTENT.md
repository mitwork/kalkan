## Шаг 4 - Получение и обработка подписанных данных

На данном шаге выполняется обработка подписываемых данных из мобильного приложения по уникальному идентификатору запроса из [Шага 2 - Формирование запроса](STEP_20_STORE_REQUEST.md).

Данные документов передаются при QR и кросс-подписании из мобильного приложениями eGov mobile и eGov business.

### Пример запроса

**Важно:** данный запрос инициируется внешним приложением.

`PUT` /documents/process-content?id=acba8198-92d9-4297-905f-eb55ea69f9c4

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
            "meta": [{
                "name": "0",
                "value": {
                    "mime": "application/vnd.openxmlformats-officedocument.wordprocessingml.document"
                }
            }],
            "documentCms": "cms..."
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
                {"name": "mime", "value": "text\/xml"}
            ],
            "documentXml": "<?xml..."
        }
    ]
}
```

где:

- `signMethod` - способ подписания;
- `documentsToSign` - массив подписанных документов.

### Пример ответа

```json
[]
```

### Реализация

Пример реализации [Mitwork\Kalkan\Http\Actions\ProcessContent.php](../src/Http/Actions/ProcessContent.php)

Для использования реализации из текущего пакета необходимо добавить соответсвующий маршрут (`route`) в приложение:

```php
<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::put('/documents/content', [\Mitwork\Kalkan\Http\Actions\ProcessContent::class, 'process'])->name(config('kalkan.actions.process-content'));
```

Так же, можно реализовать собственную логику данного шага, указав к конфигурации собственный именованный маршрут (`route`):

```php
// ...
'actions' => [
    'process-content' => 'custom-process-content',
]
// ..
```

Либо переопределить используемый класс и метод в маршрутах.
