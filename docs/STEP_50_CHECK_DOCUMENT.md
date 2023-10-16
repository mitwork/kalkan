## Шаг 5 - Проверка статуса подписания документа

На данном шаге можно получить статус подписания документа по его уникальному идентификатору из [Шаг 1 - подготовка документа](STEP_10_STORE_DOCUMENT.md).

Данный шаг необходим для изменения состояния приложения (редиректа) для пользователя, работающего с документом.

### Пример запроса

`GET` /documents/check?id=acba8198-92d9-4297-905f-eb55ea69f9c4

### Пример ответа

```json
{
    "status": true,
    "result": {
        "status": 200,
        "message": "OK",
        "valid": true,
        "signers": [
            {
                "certificates": [
                    {
                        "valid": true,
                        "revocations": [
                            {
                                "revoked": false,
                                "by": "CRL",
                                "revocationTime": null,
                                "reason": null
                            },
                            {
                                "revoked": false,
                                "by": "OCSP",
                                "revocationTime": null,
                                "reason": "OK"
                            }
                        ],
                        "notBefore": "2023-03-27T03:55:28.000+00:00",
                        "notAfter": "2024-03-26T03:55:28.000+00:00",
                        "keyUsage": "SIGN",
                        "serialNumber": "2208a45da204bebed23c2682426a970749a0d257",
                        "signAlg": "ECGOST3410-2015-512",
                        "keyUser": [
                            "CEO",
                            "ORGANIZATION"
                        ],
                        "publicKey": "MIGsMCMGCSqDDgMKAQECAjAWBgoqgw4DCgEBAgIBBggqgw4DCgEDAwOBhAAEgYDRF2HvtHCcgLVhaCi4Ge1weXWWtkN1KGVczGOxRwlHTbcCU7rD/yPFp4bJM9MHfOSN8W9a7tWGp9bnQHouvVQbeiwXFChwTnwbiCgHbUpVCjNFt6RzX1iR5sVsUJJnQdV6UeMRx7OvCDQN2XhW3C6og7J9IBmFS+H8XR+EeRoVSg==",
                        "signature": "NfXT/xAQUqRv9l+eLSuYjC9uFzT0hUAxzTcCeRnOJDEcZHzYcf5rZdOlg+Y/dgtcqO0FsZtH3J3uW3mUSanZTtt0dgdhz+ZkEVnXobwAvPhe6lKCbBMbDh/k6RoJx9i7ozcv6e29DFCdr1t906oCbNSp4MQesxhDNxTUz5qkEJE=",
                        "subject": {
                            "commonName": "ТЕСТОВ ТЕСТ",
                            "surName": "ТЕСТОВ",
                            "organization": "АО \"ТЕСТ\"",
                            "iin": "123456789011",
                            "bin": "123456789021",
                            "country": "KZ",
                            "dn": "GIVENNAME=ТЕСТОВИЧ, OU=BIN123456789021, O=\"АО \\\"ТЕСТ\\\"\", C=KZ, SERIALNUMBER=IIN123456789011, SURNAME=ТЕСТОВ, CN=ТЕСТОВ ТЕСТ"
                        },
                        "issuer": {
                            "commonName": "ҰЛТТЫҚ КУӘЛАНДЫРУШЫ ОРТАЛЫҚ (GOST) TEST 2022",
                            "country": "KZ",
                            "dn": "C=KZ, CN=ҰЛТТЫҚ КУӘЛАНДЫРУШЫ ОРТАЛЫҚ (GOST) TEST 2022"
                        }
                    }
                ],
                "tsp": {
                    "serialNumber": "f9b3c6e5b8bed49d3b17d78e62c0813acb3f744e",
                    "genTime": "2023-10-16T06:46:59.000+00:00",
                    "policy": "1.2.398.3.3.2.6.1",
                    "tsa": null,
                    "tspHashAlgorithm": "GOST34311",
                    "hash": "921a7f9c7ed6e6b06c02d14d3b931dedc9d9948c9d0e005f594cc306ef433b2f"
                }
            }
        ]
    }
}
```

где:

- `status` - статус обработки документа;
- `result` - результат проверки подлинности.

### Реализация

Пример реализации [Mitwork\Kalkan\Http\Actions\CheckDocument.php](../src/Http/Actions/CheckDocument.php)

Для использования реализации из текущего пакета необходимо добавить соответсвующий маршрут (`route`) в приложение:

```php
<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/documents/check/{id}', [\Mitwork\Kalkan\Http\Actions\CheckDocument::class, 'check'])->name(config('kalkan.actions.check-document'));
```

Так же, можно реализовать собственную логику данного шага, указав к конфигурации собственный именованный маршрут (`route`):

```php
// ...
'actions' => [
    'check-document' => 'custom-check-document',
]
// ..
```

Либо переопределить используемый класс и метод в маршрутах.
