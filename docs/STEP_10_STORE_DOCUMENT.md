## Шаг 1 - Загрузка документов

На данном шаге необходимо передать наименование файла, его содержимое и тип.
В предлагаемой реализации файл сохраняется в кэш.

### Пример запроса

`POST` /documents

```json
{
    "name":"document.docx",
    "data": "base64...",
    "size": 9,
    "mime": "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
    "meta": [
        {
            "name": "Наименование документа",
            "value": "Договор для тестирования"
        }
    ]
}
```

где:

- `id` - уникальный идентификатор документа для последующих запросов (необязательно);
- `name` - наименование документа;
- `size` - размер документа;
- `data` - содержимое документа (XML, либо base64 для прочих документов);
- `mime` - тип файла;
- `meta` - массив дополнительных атрибутов (необязательно) - при наличии будут отображены в мобильном приложении.

В случае с работой с XML-данными пример запроса будет следующим:

```json
{
    "name":"document.xml",
    "data": "<?xml...",
    "size": 8,
    "mime": "text/xml",
    "meta": [
        {
            "name": "Название документа",
            "value": "Пользовательское соглашение"
        }
    ]
}
```

### Пример ответа

```json
{
    "id": "134372667717125"
}
```

где:

- `id` - уникальный идентификатор документа, в случае отсутствия его в запросе, формируется `hrtime(true)`;

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
