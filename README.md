# Kalkan Laravel Package

---

![License:MIT](https://img.shields.io/badge/license-MIT-green.svg)
![Downloads](https://img.shields.io/github/downloads/mitwork/kalkan/total.svg)
[![Build Tests](https://github.com/mitwork/kalkan/actions/workflows/tests.yml/badge.svg)](https://github.com/mitwork/kalkan/actions/workflows/tests.yml/badge.svg)
![GitHub release (latest SemVer)](https://img.shields.io/github/v/release/mitwork/kalkan)

---

Данная библиотека реализует следующие возможности:

- Подписание XML данных;
- Подписание бинарных данных (CMS);
- Проверка и извлечение подписанных данных;
- QR, кросс-подписание.

Внешние зависимости:

- [NCALayer](https://ncl.pki.gov.kz/) - подписание данных на стороне клиента в браузере;
- [NCANode](https://v3.ncanode.kz/) - проверка, валидация и извлечение подписанных данных;
- eGov mobile ([App Store](https://apps.apple.com/kz/app/egov-mobile/id1476128386), [PlayMarket](https://play.google.com/store/apps/details?id=kz.mobile.mgov&hl=ru)), eGov business ([App Store](https://apps.apple.com/kz/app/egov-business/id1597880144), [PlayMarket](https://play.google.com/store/apps/details?id=kz.mobile.mgov.business&hl=ru)) - QR и кросс-подписание.

Для возможности QR и кросс-подписания ваша информационная система должна быть подключена к услуге [Сервис QR подписания посредством приложения Egov Mobile](https://sb.egov.kz/smart-bridge/services/passport/NITEC-S-5096),
и внешний адрес (адреса) должны быть добавлены на стороне оператора услуги.

На этапе тестирования может понадобиться установить тестовые сборки приложений eGov mobile, eGov business - напрямую для OS Android, 
либо с помощью [TestFlight](https://developer.apple.com/testflight/) по ссылке-приглашению оператора сервиса. Для тестирования кросс-ссылок, так же будет
необходимо указать корректные ссылки с идентификаторами тестовых приложений.

## Поддерживаемые версии

- PHP - 8.1, 8.2, 8.3;
- Laravel - 10.

## Установка

Данный пакет устанавливается с помощью composer в существующий или новый [Laravel](https://laravel.com/)-проект командой:

```shell
composer require mitwork/kalkan
```

## Настройка

Для возможности внесения изменений необходимо опубликовать конфигурацию в проект командой:

```shell
php artisan vendor:publish --tag kalkan-config
```

Параметры задаются и/или переопределяются в файле `config/kalkan.php`:

```php
<?php

return [
    'ncanode' => [
        'host' => env('NCANODE_HOST', 'http://localhost:14579'),
    ],
    'links' => [
        'prefix' => 'mobileSign:',
        'mobile' => 'https://mgovsign.page.link/?link=%s&isi=1476128386&ibi=kz.egov.mobile&apn=kz.mobile.mgov',
        'business' => 'https://egovbusiness.page.link/?link=%s&isi=1597880144&ibi=kz.mobile.mgov.business&apn=kz.mobile.mgov.business',
    ],
    'actions' => [
        'store-document' => 'store-document',
        'store-request' => 'store-request',
        'generate-qr-code' => 'generate-qr-code',
        'generate-cross-link' => 'generate-cross-link',
        'generate-service-link' => 'generate-service-link',
        'prepare-content' => 'prepare-content',
        'process-content' => 'process-content',
        'check-document' => 'check-document',
        'check-request' => 'check-request',
    ],
    'options' => [
        'description' => 'Текст для пользователя',
        'organisation' => [
            'nameRu' => 'АО ТЕСТ',
            'nameKz' => 'ТЕСТ АҚ',
            'nameEn' => 'JS TEST',
            'bin' => '123456789012',
        ],
        'auth' => [
            'type' => 'None', // Bearer
            'token' => '',
        ],
    ],
    'ttl' => 180,
];
```

где:

- `ncanode.host` - адрес и порт для подключения к NCANode;
- `links.prefix` - префикс для формирования ссылки в QR-code;
- `links.mobile` - шаблон для формирования кросс-ссылки при подписании в приложении _eGov mobile_;
- `links.business` - шаблон для формирования кросс-ссылки при подписании в приложении _eGov business_;
- `actions` - именованные маршруты для взаимодействия с приложениями при QR и кросс-подписании;
- `options.description` - название запроса или информационной системы;
- `options.organisation` - сведения об организации;
- `auth.type` - тип авторизации при формировании сервисных ссылок для QR-подписания, допустимые значения - `None`, `Bearer`;
- `auth.token` - токен авторизации, в случае если он не задан для запроса - будет сформирован уникальный одноразовый токен;
- `ttl` - время жизни одноразовых ссылок (в секундах).

## Использование

### Подписание и проверка XML данных

Данный пример подписания применим для подписания на бэкенде, когда ключ ЭЦП находится на сервере.
Для проверки подлинности данных, подписанных через NCALayer необходимо передавать подписанные данные через HTTP-запрос.

```php
<?php

namespace App\Controllers;

use \Mitwork\Kalkan\Services\KalkanSignatureService;
use \Mitwork\Kalkan\Services\KalkanValidationService;

class TestXmlController extends Controller
{
    function __construct(
        public KalkanSignatureService $signatureService,
        public KalkanValidationService $validationService,
    )
    {
        //
    }
    
    function testXmlSign(): string
    {
        $xml = '<?xml...';
        $key = 'base64...';
        $password = 'password';
        
        $result = $this->signatureService->signXml($xml, $key, $password);
        
        // dd($result);
        // <?xml...
        
        return $result;
    }
    
    function testXmlVerify(): bool
    {
        $signedXml = $this->testXmlSign();
        
        $result = $this->validationService->verifyXml($signedXml);
        
        // dd($result);
        // true
        
        return $result;
    }
}
```

### Подписание, проверка и извлечение CMS данных

Данный пример подписания применим для подписания на бэкенде, когда ключ ЭЦП находится на сервере.
Для проверки подлинности данных, подписанных через NCALayer необходимо передавать подписанные данные через HTTP-запрос.

```php
<?php

namespace App\Controllers;

use \Mitwork\Kalkan\Services\KalkanSignatureService;
use \Mitwork\Kalkan\Services\KalkanValidationService;
use \Mitwork\Kalkan\Services\KalkanExtractionService;

class TestCmsController extends Controller
{
    function __construct(
        public KalkanSignatureService $signatureService,
        public KalkanValidationService $validationService,
        public KalkanExtractionService $extractionService,
    )
    {
        //
    }
    
    function testCmsSign(): string
    {
        $data = 'base64...';
        $key = 'base64...';
        $password = 'password';
        
        $result = $this->signatureService->signCms($data, $key, $password);
        
        // dd($result);
        // base64...
        
        return $result;
    }
    
    function testCmsVerify(): bool
    {
        $cms = 'base64...';
        $data = 'base64...';
        
        $result = $this->validationService->verifyCms($cms, $data);
        
        // dd($result);
        // true
        
        return $result;
    }
    
    function testCmsExtract(): string
    {
        $cms = 'base64...';
        
        $result = $this->extractionService->extractCms($cms);
        
        // dd($result);
        // base64...
        
        return $result;
    }
}
```

### QR, кросс-подписания

Поскольку взаимодействие происходит по протоколу HTTP, данный пакет кроме сервисов содержит так же и готовые `Http\Actions` для выполнения всех требуемых шагов.

- `POST` `/api/documents` - загрузка документов;
- `POST` `/api/requests` - формирование запроса;
- `GET` `/api/requests/generate/{id}` - генерация сервисной ссылки;
- `GET` `/api/requests/qr-code/{id}` - формирование QR-кода;
- `GET` `/api/requests/links/{id}` - формирование кросс-ссылок;
- `GET` `/api/requests/{id}` - работа с данными - отдача;
- `PUT` `/api/requests/{id}` - работа с данными - обработка;
- `GET` `/check/document/{id}` - проверка статуса подписания документа;
- `GET` `/check/request/{id}` - проверка статуса заявки.

Для использования готовых методов, необходимо указать маршруты в `routes/api.php` описанные в [отдельном документе](docs/ROUTES_AND_ACTIONS.md) или [исходном коде](routes/api.php).

При необходимости можно переопределить любой из шагов, указав собственный обработчик. Подробнее о каждом из шагов написано в разделах ниже.

#### QR-подписание

Данный механизм позволяет подписывать данные с помощью смартфона с использованием приложений eGov mobile или eGov business, 
когда проект открыт в браузере компьютера или планшета.

**Основные шаги:**

1) [Загрузка документов](docs/STEP_10_STORE_DOCUMENT.md) - данный шаг опционален, документы можно отправить на шаге 2;
2) [Формирование запроса](docs/STEP_20_STORE_REQUEST.md) и [генерация сервисной ссылки](docs/STEP_21_GENERATE_SERVICE_LINK.md);
3) [Формирование QR-кода](docs/STEP_22_GENERATE_QR_CODE.md);
4) Считывание QR-кода мобильным приложением;
5) [Получение подписываемых данных](docs/STEP_30_PREPARE_CONTENT.md) мобильным приложением;
6) Подписание данных;
7) [Обработка подписанных данных](docs/STEP_40_PROCESS_CONTENT.md);
8) [Проверка статуса подписания документа](docs/STEP_50_CHECK_DOCUMENT.md).

#### Кросс-подписание

Данный механизм позволяет подписывать данные с помощью смартфона с использованием приложений eGov Mobile или eGov Business,
когда проект (сайт) открыт на самом смартфоне.

**Основные шаги:**

1) [Загрузка документов](docs/STEP_10_STORE_DOCUMENT.md) - данный шаг опционален, документы можно отправить на шаге 2;
2) [Формирование запроса](docs/STEP_20_STORE_REQUEST.md) и [генерация сервисной ссылки](docs/STEP_21_GENERATE_SERVICE_LINK.md);
3) [Формирование кросс-ссылок](docs/STEP_23_GENERATE_CROSS_LINKS.md);
4) Переход по кросс-ссылке в мобильное приложение;
5) [Получение подписываемых данных](docs/STEP_30_PREPARE_CONTENT.md) мобильным приложением;
6) Подписание данных;
7) [Обработка подписанных данных](docs/STEP_40_PROCESS_CONTENT.md);
8) [Проверка статуса подписания документа](docs/STEP_50_CHECK_DOCUMENT.md).

#### События

В пакете реализованы следующие события:

- `Mitwork\Kalkan\Events\AuthAccepted` - аутентификация при запросе данных мобильным приложением подтверждена;
- `Mitwork\Kalkan\Events\AuthRejected` - аутентификация при запросе данных мобильным приложением не подтверждена;
- `Mitwork\Kalkan\Events\DocumentRejected` - документ отклонен;
- `Mitwork\Kalkan\Events\DocumentRequested` - документ запрошен;
- `Mitwork\Kalkan\Events\DocumentSaved` - документ сохранен;
- `Mitwork\Kalkan\Events\DocumentSigned` - документ подписан;
- `Mitwork\Kalkan\Events\RequestProcessed` - запрос обработан;
- `Mitwork\Kalkan\Events\RequestRejected` - запрос отклонен;
- `Mitwork\Kalkan\Events\RequestRequested` - сервисные данные запрошены;
- `Mitwork\Kalkan\Events\RequestSaved` - сервисные данные сохранены.

Для подписки на любое из событий, в приложении можно реализовать собственный `EventListener`:

```php
<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Mitwork\Kalkan\Events\DocumentSigned;

class DocumentEventsListener
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    public function onSigned(DocumentSigned $event): void
    {
        Log::info('Документ подписан', [
            'id' => $event->id,
            'result' => $event->result,
        ]);
    }
}

```

## Ограничения

### Работа с несколькими файлами

При реализации подписания нескольких документов с использованием NCALayer, имеется возможность подписания только XML-документов.
API не позволяет подписывать несколько файлов (хэшей) за один раз.

Варианты решения:

1) Подписание на сервере через выбор ключа ЭЦП и запрос пароля - **небезопасно** и не применимо при работе с токенами (KazToken, JaCarta);
2) Формирование на сервере и подписание на клиенте архива подписываемых документов;
3) Подписание альтернативными способами - через мобильные приложения по QR-коду или кросс-ссылкам.

## Тестирование

Для запуска тестов необходимо выполнить команду:

```shell
./vendor/bin/phpunit tests
```

**Важно** - в тестах используются тестовые сертификаты из [SDK](https://pki.gov.kz/get-sdk/) НУЦ РК, для проверки необходимо запустить NCANode со следующими параметрами:

```shell
NCANODE_DEBUG=true NCANODE_CRL_URL="http://test.pki.gov.kz/crl/nca_rsa_test.crl http://test.pki.gov.kz/crl/nca_gost_test.crl http://test.pki.gov.kz/crl/nca_gost_test_2022.crl" NCANODE_CRL_DELTA_URL="http://test.pki.gov.kz/crl/nca_d_rsa_test.crl http://test.pki.gov.kz/crl/nca_d_gost_test.crl http://test.pki.gov.kz/crl/nca_d_gost_test_2022.crl" NCANODE_CA_URL="http://test.pki.gov.kz/cert/root_gost_test.cer http://test.pki.gov.kz/cert/root_rsa_test.cer http://test.pki.gov.kz/cert/root_test_gost_2022.cer http://test.pki.gov.kz/cert/nca_gost_test.cer http://test.pki.gov.kz/cert/nca_rsa_test.cer http://test.pki.gov.kz/cert/nca_gost2022_test.cer" NCANODE_OCSP_URL=http://test.pki.gov.kz/ocsp/ NCANODE_TSP_URL=http://test.pki.gov.kz/tsp/ java -jar NCANode-3.2.3.jar
```

Для работы с действительными сертификатами НУЦ РК, при тестировании приложение NCANode нужно запустить с параметрами по-умолчанию:

```shell
java -jar NCANode-3.2.3.jar
```

## Отказ от ответственности

Данный пакет предоставляется и распространяется "как есть", автор (авторы) не несут юридической ответственности, которая может возникнуть
при использовании данного пакета или его отдельных частей.

При реализации приложений, использующих данный пакет, необходимо придерживаться законов и нормативных актов, регламентирующих работу
с электронной цифровой подписью и электронным документом, в том числе:

1) [Закон Республики Казахстан от 7 января 2003 года N 370 "Об электронном документе и электронной цифровой подписи"](https://adilet.zan.kz/rus/docs/Z030000370_);
2) [Приказ Министра по инвестициям и развитию Республики Казахстан от 9 декабря 2015 года № 1187 Об утверждении Правил проверки подлинности электронной цифровой подписи](https://adilet.zan.kz/rus/docs/V1500012864).
