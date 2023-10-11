## MITWORK Kalkan Laravel Package

Данная библиотека реализует следующие возможности:

- Подписание XML данных;
- Подписание бинарных данных (CMS);
- Проверка подписанных данных;
- QR-подписание.

Внешние зависимости:

- [NCALayer](https://ncl.pki.gov.kz/) - подписание данных на стороне клиента в браузере;
- [NCANode](https://v3.ncanode.kz/) - проверка, валидация и извлечение подписанных данных.

### Установка

```shell
composer require mitwork/kalkan
```

### Настройка

Параметры `config/kalkan.php`:

```php
return [
    'ncanode' => [
        'host' => env('NCANODE_HOST', 'http://localhost:3333') // Хост для подключения к NCANode
    ],
    'links' => [
        'prefix' => 'mobileSign:%s',
        'person' => 'https://mgovsign.page.link/?link=%s&isi=1476128386&ibi=kz.egov.mobile&apn=kz.mobile.mgov',
        'legal' => 'https://egovbusiness.page.link/?link=%s&isi=1597880144&ibi=kz.mobile.mgov.business&apn=kz.mobile.mgov.business'
    ]
];
```

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
