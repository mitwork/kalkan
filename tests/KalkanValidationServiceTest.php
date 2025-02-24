<?php

declare(strict_types=1);

namespace Mitwork\Kalkan\Tests;

use Illuminate\Support\Str;
use Mitwork\Kalkan\Exceptions\KalkanValidationException;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(\Mitwork\Kalkan\Services\KalkanValidationService::class)]
final class KalkanValidationServiceTest extends BaseTestCase
{
    public function test_xml_validation(): void
    {
        $service = new \Mitwork\Kalkan\Services\KalkanValidationService;

        $data = <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="no"?><test><message>QCxn00hfuH9zCv58</message><ds:Signature xmlns:ds="http://www.w3.org/2000/09/xmldsig#">
<ds:SignedInfo>
<ds:CanonicalizationMethod Algorithm="http://www.w3.org/TR/2001/REC-xml-c14n-20010315"/>
<ds:SignatureMethod Algorithm="urn:ietf:params:xml:ns:pkigovkz:xmlsec:algorithms:gostr34102015-gostr34112015-512"/>
<ds:Reference URI="">
<ds:Transforms>
<ds:Transform Algorithm="http://www.w3.org/2000/09/xmldsig#enveloped-signature"/>
<ds:Transform Algorithm="http://www.w3.org/TR/2001/REC-xml-c14n-20010315#WithComments"/>
</ds:Transforms>
<ds:DigestMethod Algorithm="urn:ietf:params:xml:ns:pkigovkz:xmlsec:algorithms:gostr34112015-512"/>
<ds:DigestValue>mezmV3CZ6EbcmVZwktwxsHMts8QlY0KBWz9wqFq7tXlOBNE2czNlm9Ia+yV7Y8BeY7FFbi+dVeB3&#13;
E0Gnoz8h7Q==</ds:DigestValue>
</ds:Reference>
</ds:SignedInfo>
<ds:SignatureValue>
/FAR50IPi3UzFiNukbo25fBUVxKBcxD+ibw2tqWCGzN6CnFtNeDgkv67Od4Z8tY2uBYg0VnH4G0y&#13;
eld6ZgVz96qCoP4P96u3nwfSeOcibppzGN/c/DitsIWqvh6CKws0FvrIvunp51Kyrji4pRTYevD4&#13;
RTwz0Syhuoi5hAyRZzU=
</ds:SignatureValue>
<ds:KeyInfo>
<ds:X509Data>
<ds:X509Certificate>
MIIEdjCCA96gAwIBAgIUEPUSX7nlL4TELLrY5FHGjxCl6GkwDgYKKoMOAwoBAQIDAgUAMF0xTjBM&#13;
BgNVBAMMRdKw0JvQotCi0KvSmiDQmtCj05jQm9CQ0J3QlNCr0KDQo9Co0Ksg0J7QoNCi0JDQm9Cr&#13;
0pogKEdPU1QpIFRFU1QgMjAyMjELMAkGA1UEBhMCS1owHhcNMjQxMDI5MTE0MTI0WhcNMjUxMDI5&#13;
MTE0MTI0WjCBrTEeMBwGA1UEAwwV0KLQldCh0KLQntCSINCi0JXQodCiMRUwEwYDVQQEDAzQotCV&#13;
0KHQotCe0JIxGDAWBgNVBAUTD0lJTjEyMzQ1Njc4OTAxMTELMAkGA1UEBhMCS1oxGDAWBgNVBAoM&#13;
D9CQ0J4gItCi0JXQodCiIjEYMBYGA1UECwwPQklOMTIzNDU2Nzg5MDIxMRkwFwYDVQQqDBDQotCV&#13;
0KHQotCe0JLQmNCnMIGsMCMGCSqDDgMKAQECAjAWBgoqgw4DCgEBAgIBBggqgw4DCgEDAwOBhAAE&#13;
gYA0Oba3jTsj3Ty6a9qRnYONQFiLcRa46li2/2nL8r0jnm014ojzFEO1lDHoOHUUogex0BtujEXY&#13;
ly/fK2skjuWb7lJPl/TnX3SZbxKl5LNk62aK9GTWWVl1QhhdVZfIHfuuAoGgMe6N7QaUL08Bqkz6&#13;
Xt4esg2kgCColB0sCipwIKOCAdEwggHNMCgGA1UdJQQhMB8GCCsGAQUFBwMEBggqgw4DAwQBAgYJ&#13;
KoMOAwMEAQIBMDgGA1UdIAQxMC8wLQYGKoMOAwMCMCMwIQYIKwYBBQUHAgEWFWh0dHA6Ly9wa2ku&#13;
Z292Lmt6L2NwczB3BggrBgEFBQcBAQRrMGkwKAYIKwYBBQUHMAGGHGh0dHA6Ly90ZXN0LnBraS5n&#13;
b3Yua3ovb2NzcC8wPQYIKwYBBQUHMAKGMWh0dHA6Ly90ZXN0LnBraS5nb3Yua3ovY2VydC9uY2Ff&#13;
Z29zdDIwMjJfdGVzdC5jZXIwQQYDVR0fBDowODA2oDSgMoYwaHR0cDovL3Rlc3QucGtpLmdvdi5r&#13;
ei9jcmwvbmNhX2dvc3QyMDIyX3Rlc3QuY3JsMEMGA1UdLgQ8MDowOKA2oDSGMmh0dHA6Ly90ZXN0&#13;
LnBraS5nb3Yua3ovY3JsL25jYV9nb3N0MjAyMl9kX3Rlc3QuY3JsMA4GA1UdDwEB/wQEAwIDyDAd&#13;
BgNVHQ4EFgQUEPUSX7nlL4TELLrY5FHGjxCl6GkwHwYDVR0jBBgwFoAU+tJLG6OgyWH+HKhQPmqi&#13;
u0UNuKMwFgYGKoMOAwMFBAwwCgYIKoMOAwMFAQEwDgYKKoMOAwoBAQIDAgUAA4GBAC9TQwkcVziV&#13;
K6ogZ++MBswBULAVzhJ30ystp/JuBNunhbufhbvaf7qkN6Xl9erwVtX343E+SiEojFXMQtzjJpHX&#13;
kDaTM1n8lQbf3HvumhP26X1TYxbFlYmYYnH9TV3CMOqabBmp7UmDok+ueein+kVojcyirFSFh7ZT&#13;
912nPPCJ
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
        $service = new \Mitwork\Kalkan\Services\KalkanValidationService;

        $data = <<<'DATA'
MIILtgYJKoZIhvcNAQcCoIILpzCCC6MCAQExDjAMBggqgw4DCgEDAwUAMBIGCSqGSIb3DQEHAaAFBAO16y2gggR6MIIEdjCCA96gAwIBAgIUEPUSX7nlL4TE
LLrY5FHGjxCl6GkwDgYKKoMOAwoBAQIDAgUAMF0xTjBMBgNVBAMMRdKw0JvQotCi0KvSmiDQmtCj05jQm9CQ0J3QlNCr0KDQo9Co0Ksg0J7QoNCi0JDQm9Cr
0pogKEdPU1QpIFRFU1QgMjAyMjELMAkGA1UEBhMCS1owHhcNMjQxMDI5MTE0MTI0WhcNMjUxMDI5MTE0MTI0WjCBrTEeMBwGA1UEAwwV0KLQldCh0KLQntCS
INCi0JXQodCiMRUwEwYDVQQEDAzQotCV0KHQotCe0JIxGDAWBgNVBAUTD0lJTjEyMzQ1Njc4OTAxMTELMAkGA1UEBhMCS1oxGDAWBgNVBAoMD9CQ0J4gItCi
0JXQodCiIjEYMBYGA1UECwwPQklOMTIzNDU2Nzg5MDIxMRkwFwYDVQQqDBDQotCV0KHQotCe0JLQmNCnMIGsMCMGCSqDDgMKAQECAjAWBgoqgw4DCgEBAgIB
Bggqgw4DCgEDAwOBhAAEgYA0Oba3jTsj3Ty6a9qRnYONQFiLcRa46li2/2nL8r0jnm014ojzFEO1lDHoOHUUogex0BtujEXYly/fK2skjuWb7lJPl/TnX3SZ
bxKl5LNk62aK9GTWWVl1QhhdVZfIHfuuAoGgMe6N7QaUL08Bqkz6Xt4esg2kgCColB0sCipwIKOCAdEwggHNMCgGA1UdJQQhMB8GCCsGAQUFBwMEBggqgw4D
AwQBAgYJKoMOAwMEAQIBMDgGA1UdIAQxMC8wLQYGKoMOAwMCMCMwIQYIKwYBBQUHAgEWFWh0dHA6Ly9wa2kuZ292Lmt6L2NwczB3BggrBgEFBQcBAQRrMGkw
KAYIKwYBBQUHMAGGHGh0dHA6Ly90ZXN0LnBraS5nb3Yua3ovb2NzcC8wPQYIKwYBBQUHMAKGMWh0dHA6Ly90ZXN0LnBraS5nb3Yua3ovY2VydC9uY2FfZ29z
dDIwMjJfdGVzdC5jZXIwQQYDVR0fBDowODA2oDSgMoYwaHR0cDovL3Rlc3QucGtpLmdvdi5rei9jcmwvbmNhX2dvc3QyMDIyX3Rlc3QuY3JsMEMGA1UdLgQ8
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
        $validationService = new \Mitwork\Kalkan\Services\KalkanValidationService;

        $data = Str::random(64);

        $this->assertThrows(
            fn () => $validationService->verifyCms($data, 'hello', throw: true),
            KalkanValidationException::class
        );
    }
}
