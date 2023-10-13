# MITWORK Kalkan Laravel Package

---

![License:MIT](https://img.shields.io/badge/license-MIT-green.svg)
![Downloads](https://img.shields.io/github/downloads/mitwork/kalkan/total.svg)
[![Build Tests](https://github.com/mitwork/kalkan/actions/workflows/tests.yml/badge.svg)](https://github.com/mitwork/kalkan/actions/workflows/tests.yml/badge.svg)
![GitHub release (latest SemVer)](https://img.shields.io/github/v/release/mitwork/kalkan)

---

Данная библиотека реализует следующие возможности:

- Подписание XML данных;
- Подписание бинарных данных (CMS);
- Проверка подписанных данных;
- QR, cross-подписание.

Внешние зависимости:

- [NCALayer](https://ncl.pki.gov.kz/) - подписание данных на стороне клиента в браузере;
- [NCANode](https://v3.ncanode.kz/) - проверка, валидация и извлечение подписанных данных.

Для возможности QR и cross-подписания ваша информационная система должна быть подключена к услуге [Cервис QR подписания посредством приложения Egov Mobile](https://sb.egov.kz/smart-bridge/services/passport/NITEC-S-5096),
и внешний адрес (адреса) должны быть добавлены на стороне оператора услуги.

На этапе тестирования может понадобиться установить тестовые сборки приложений egov Mobile, egov Business - напрямую для OS Android, 
либо с помощью [TestFlight](https://developer.apple.com/testflight/) по ссылке-приглашению оператора сервиса.

## Установка

Данный пакет устанавливается с помощью composer в существующий или новый [Laravel](https://laravel.com/)-проект командой:

```shell
composer require mitwork/kalkan
```

## Поддерживаемые версии

- PHP - 8.1, 8.2, 8.3;
- Laravel - 10.

## Настройка

Параметры задаются и/или переопределяются в файле `config/kalkan.php`:

```php
return [
    'ncanode' => [
        'host' => env('NCANODE_HOST', 'http://localhost:14579'),
    ],
    'links' => [
        'prefix' => 'mobileSign:',
        'mobile' => 'https://mgovsign.page.link/?link=%s&isi=1476128386&ibi=kz.egov.mobile&apn=kz.mobile.mgov',
        'business' => 'https://egovbusiness.page.link/?link=%s&isi=1597880144&ibi=kz.mobile.mgov.business&apn=kz.mobile.mgov.business',
    ],
    'options' => [
        'description' => 'Test',
        'organisation' => [
            'nameRu' => 'АО "ТЕСТ"',
            'nameKz' => '"ТЕСТ" ЖК',
            'nameEn' => 'OP "TEST"',
            'bin' => '123456789012',
        ],
        'ttl' => 180,
    ],
];
```

где:

- `ncanode.host` - адрес и порт для подключения к NCANode;
- `links.prefix` - префикс для формирования ссылки в QR-code;
- `links.mobile` - шаблон для формирования кросс-ссылки при подписании в приложении Egov Mobile;
- `links.business` - шаблон для формирования кросс-ссылки при подписании в приложении Egov Business;
- `options.description` - название информационной системы;
- `options.organisation` - сведения об организации;
- `options.ttl` - время жизни одноразовых ссылок (в секундах).

## Использование

### Подписание и проверка XML данных

```php
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

```php
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

### QR-подписание

Данный механизм позволяет подписывать данные с помощью смартфона с использованием приложений egov Mobile или egovBusiness, 
когда проект открыт в браузере компьютера или планшета.

Основные шаги:

1) Подготовка документа - документ может быть считан из файловой системы, облачного хранилища или базы данных. В случае работы с бинарными (CMS) данными, файл необходимо преобразовать в текст с помощью функции `base64_encode`;
2) Формирование QR-кода;
3) Считывание QR-кода, подписание и возврат подписанных данных;
4) Обработка подписанных данных.

Пример реализации и использования можно посмотреть в [файле](tests/ApplicationTestingTest.php) теста.

### Кросс-подписание

Данный механизм позволяет подписывать данные с помощью смартфона с использованием приложений egov Mobile или egovBusiness,
когда проект (сайт) открыт на самом смартфоне.

Основные шаги:

1) Подготовка документа - документ может быть считан из файловой системы, облачного хранилища или базы данных. В случае работы с бинарными (CMS) данными, файл необходимо преобразовать в текст с помощью функции `base64_encode`;
2) Формирование кросс-ссылок для клиента;
3) Переход по кросс-ссылки, подписание и возврат подписанных данных;
4) Обработка подписанных данных.

Пример реализации и использования можно посмотреть в [файле](tests/ApplicationTestingTest.php) теста.

## Тестирование

Для запуска тестов необходимо выполнить команду:

```shell
./vendor/bin/phpunit tests
```

**Важно** - в тестах используются тестовые сертификаты из [SDK](https://pki.gov.kz/get-sdk/) НУЦ РК, для проверки необходимо запустить NCANode со следующими параметрами:

```shell
NCANODE_DEBUG=true NCANODE_CRL_URL="http://test.pki.gov.kz/crl/nca_rsa_test.crl http://test.pki.gov.kz/crl/nca_gost_test.crl http://test.pki.gov.kz/crl/nca_gost_test_2022.crl" NCANODE_CRL_DELTA_URL="http://test.pki.gov.kz/crl/nca_d_rsa_test.crl http://test.pki.gov.kz/crl/nca_d_gost_test.crl http://test.pki.gov.kz/crl/nca_d_gost_test_2022.crl" NCANODE_CA_URL="http://test.pki.gov.kz/cert/root_gost_test.cer http://test.pki.gov.kz/cert/root_rsa_test.cer http://test.pki.gov.kz/cert/root_test_gost_2022.cer http://test.pki.gov.kz/cert/nca_gost_test.cer http://test.pki.gov.kz/cert/nca_rsa_test.cer http://test.pki.gov.kz/cert/nca_gost2022_test.cer" NCANODE_OCSP_URL=http://test.pki.gov.kz/ocsp/ NCANODE_TSP_URL=http://test.pki.gov.kz/tsp/ java -jar NCANode-3.2.3.jar
```
