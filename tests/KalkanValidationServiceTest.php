<?php

declare(strict_types=1);

namespace Mitwork\Kalkan\Tests;

use Illuminate\Support\Str;
use Mitwork\Kalkan\Exceptions\KalkanValidationException;
use Mitwork\Kalkan\Services\KalkanValidationService;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(KalkanValidationService::class)]
final class KalkanValidationServiceTest extends BaseTestCase
{
    public function test_xml_validation(): void
    {
        $service = new KalkanValidationService;

        $data = <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="no"?><test>
  <message>MvhKWBRy1eJSGQcY</message>
<ds:Signature xmlns:ds="http://www.w3.org/2000/09/xmldsig#">
<ds:SignedInfo>
<ds:CanonicalizationMethod Algorithm="http://www.w3.org/TR/2001/REC-xml-c14n-20010315"/>
<ds:SignatureMethod Algorithm="urn:ietf:params:xml:ns:pkigovkz:xmlsec:algorithms:gostr34102015-gostr34112015-512"/>
<ds:Reference URI="">
<ds:Transforms>
<ds:Transform Algorithm="http://www.w3.org/2000/09/xmldsig#enveloped-signature"/>
<ds:Transform Algorithm="http://www.w3.org/TR/2001/REC-xml-c14n-20010315#WithComments"/>
</ds:Transforms>
<ds:DigestMethod Algorithm="urn:ietf:params:xml:ns:pkigovkz:xmlsec:algorithms:gostr34112015-512"/>
<ds:DigestValue>Oi60uBbAUIYKCcJUFbTlBXdTx9kUBnHN9rUk65Y/T/8xEJC/5O/IQMwchrdemVrgISlv8LGhBZy9&#13;
/7Je4c8R1g==</ds:DigestValue>
</ds:Reference>
</ds:SignedInfo>
<ds:SignatureValue>
e76k2wiJhOHhgZRR3M/si2fjnbgCtHteGXoq7ldbDppr50f3xnAyVjKthbS18iZH+nZIs2LOA6Cd&#13;
EFEtXSLG0JtQhhhhsftMxnrvOVPpqFqp3nOl5M5uFQvbFWJRtTreE60rgaHKyp100Yd+YGNVi64n&#13;
w43PDse1pYwUOx/eEt0=
</ds:SignatureValue>
<ds:KeyInfo>
<ds:X509Data>
<ds:X509Certificate>
MIIEozCCBAugAwIBAgIUMD7r3xeWnz7e3pvZgo+xNVqrvk4wDgYKKoMOAwoBAQIDAgUAMF0xTjBM&#13;
BgNVBAMMRdKw0JvQotCi0KvSmiDQmtCj05jQm9CQ0J3QlNCr0KDQo9Co0Ksg0J7QoNCi0JDQm9Cr&#13;
0pogKEdPU1QpIFRFU1QgMjAyMjELMAkGA1UEBhMCS1owHhcNMjYwNTA4MDcxMDE1WhcNMjcwNTA4&#13;
MDcxMDE1WjCBrTEeMBwGA1UEAwwV0KLQldCh0KLQntCSINCi0JXQodCiMRUwEwYDVQQEDAzQotCV&#13;
0KHQotCe0JIxGDAWBgNVBAUTD0lJTjEyMzQ1Njc4OTAxMTELMAkGA1UEBhMCS1oxGDAWBgNVBAoM&#13;
D9CQ0J4gItCi0JXQodCiIjEYMBYGA1UECwwPQklOMTIzNDU2Nzg5MDIxMRkwFwYDVQQqDBDQotCV&#13;
0KHQotCe0JLQmNCnMIGsMCMGCSqDDgMKAQECAjAWBgoqgw4DCgEBAgIBBggqgw4DCgEDAwOBhAAE&#13;
gYBOxeMGyBEhfYGUdQEutSg5nHBkmTwhH3nuerbW8b03YgRTBVh67rIKiQmrwNZ4W5IutgjTbbCQ&#13;
nZHRY3c4mblX12DvApDMVD2VSUAX+Co81azdCk9nXaK5unFTv8W1ysLZAyiR/PfNMdw9jdrgwlog&#13;
KANPum9S577odQqYu4jbF6OCAf4wggH6MCgGA1UdJQQhMB8GCCsGAQUFBwMEBggqgw4DAwQBAgYJ&#13;
KoMOAwMEAQIBMDgGA1UdIAQxMC8wLQYGKoMOAwMCMCMwIQYIKwYBBQUHAgEWFWh0dHA6Ly9wa2ku&#13;
Z292Lmt6L2NwczBBBgNVHR8EOjA4MDagNKAyhjBodHRwOi8vdGVzdC5wa2kuZ292Lmt6L2NybC9u&#13;
Y2FfZ29zdDIwMjJfdGVzdC5jcmwwQwYDVR0uBDwwOjA4oDagNIYyaHR0cDovL3Rlc3QucGtpLmdv&#13;
di5rei9jcmwvbmNhX2dvc3QyMDIyX2RfdGVzdC5jcmwwDgYDVR0PAQH/BAQDAgPIMIGjBggrBgEF&#13;
BQcBAQSBljCBkzA9BggrBgEFBQcwAoYxaHR0cDovL3Rlc3QucGtpLmdvdi5rei9jZXJ0L25jYV9n&#13;
b3N0MjAyMl90ZXN0LmNlcjAoBggrBgEFBQcwAYYcaHR0cDovL3Rlc3QucGtpLmdvdi5rei9vY3Nw&#13;
LzAoBggrBgEFBQcwAYYcaHR0cDovL3Rlc3QucGtpLmdvdi5rei9vY3NwLzAdBgNVHQ4EFgQUMD7r&#13;
3xeWnz7e3pvZgo+xNVqrvk4wHwYDVR0jBBgwFoAU+tJLG6OgyWH+HKhQPmqiu0UNuKMwFgYGKoMO&#13;
AwMFBAwwCgYIKoMOAwMFAQEwDgYKKoMOAwoBAQIDAgUAA4GBADLrU6Q8osjTQbz6jFhMHv5f6Z/P&#13;
X+XS3FwWmQLX1BC+tsNKqbicljIkXiMLvkFy6eluDPmqzEG1gVBYWDQG7okFawjyu+lVnz2AJDJK&#13;
Pf+Dt85Mm/FJkd6bdsM/Hb0c/WfG3OsdYfNYvGSZrPBvYVVpaNTDtgrSolLh31YVoKjp
</ds:X509Certificate>
</ds:X509Data>
</ds:KeyInfo>
</ds:Signature></test>
XML;

        $result = $service->verifyXml($data);
        $response = $service->getResponse();

        $this->assertIsArray($response, 'Получен некорректный ответ сервиса');
        $this->assertArrayHasKey('status', $response, 'Ответ не содержит статус');
        $this->assertEquals(200, $response['status'], 'Получен некорректный ответ сервиса');

        $this->assertTrue($result, 'Проверка подлинности XML не работает');
    }

    public function test_cms_validation(): void
    {
        $service = new KalkanValidationService;

        $data = <<<'DATA'
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
DATA;

        $result = $service->verifyCms($data, 'test');
        $response = $service->getResponse();

        $this->assertIsArray($response, 'Получен некорректный ответ сервиса');
        $this->assertArrayHasKey('status', $response, 'Ответ не содержит статус');
        $this->assertEquals(200, $response['status'], 'Получен некорректный ответ сервиса');

        $this->assertTrue($result, 'Проверка подлинности CMS не работает');
    }

    public function test_validation_exceptions(): void
    {
        $validationService = new KalkanValidationService;

        $data = Str::random(64);

        $this->assertThrows(
            fn () => $validationService->verifyCms($data, 'hello', throw: true),
            KalkanValidationException::class
        );
    }
}
