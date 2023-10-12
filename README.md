# MITWORK Kalkan Laravel Package

Данная библиотека реализует следующие возможности:

- Подписание XML данных;
- Подписание бинарных данных (CMS);
- Проверка подписанных данных;
- QR-подписание.

Внешние зависимости:

- [NCALayer](https://ncl.pki.gov.kz/) - подписание данных на стороне клиента в браузере;
- [NCANode](https://v3.ncanode.kz/) - проверка, валидация и извлечение подписанных данных.

## Установка

```shell
composer require mitwork/kalkan
```

## Настройка

Параметры `config/kalkan.php`:

```php
return [
    'ncanode' => [
        'host' => env('NCANODE_HOST', 'http://localhost:14579') // Хост для подключения к NCANode
    ],
    'links' => [
        'prefix' => 'mobileSign:%s',
        'person' => 'https://mgovsign.page.link/?link=%s&isi=1476128386&ibi=kz.egov.mobile&apn=kz.mobile.mgov',
        'legal' => 'https://egovbusiness.page.link/?link=%s&isi=1597880144&ibi=kz.mobile.mgov.business&apn=kz.mobile.mgov.business'
    ]
];
```

## Использование

### Подписание и проверка XML данных

```php
use \Mitwork\Kalkan\Services\KalkanSignatureService;
use \Mitwork\Kalkan\Services\KalkanValidationService;

class TestController extends Controller
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
        // <?xml ...
        
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

class TestController extends Controller
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
        $xml = 'base64...';
        $key = 'base64...';
        $password = 'password';
        
        $result = $this->signatureService->signCms($xml, $key, $password);
        
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

## Тестирование

Для запуска тестов необходимо выполнить команду:

```shell
./vendor/bin/phpunit tests
```

**Важно** - в тестах используются тестовые сертификаты из [SDK](https://pki.gov.kz/get-sdk/) НУЦ РК, для проверки необходимо запустить NCANode со следующими параметрами:

```shell
NCANODE_DEBUG=true NCANODE_CRL_URL=http://test.pki.gov.kz/crl/nca_gost2022_test.crl NCANODE_CA_URL="http://test.pki.gov.kz/cert/nca_gost2022_test.cer http://test.pki.gov.kz/cert/root_test_gost_2022.cer" NCANODE_OCSP_URL=http://test.pki.gov.kz/ocsp/ NCANODE_TSP_URL=http://test.pki.gov.kz/tsp/ java -jar NCANode.jar
```
