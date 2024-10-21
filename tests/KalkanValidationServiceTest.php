<?php

declare(strict_types=1);

namespace Mitwork\Kalkan\Tests;

use Illuminate\Support\Str;
use Mitwork\Kalkan\Exceptions\KalkanValidationException;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(\Mitwork\Kalkan\Services\KalkanValidationService::class)]
final class KalkanValidationServiceTest extends BaseTestCase
{
    public function testXmlValidation(): void
    {
        $service = new \Mitwork\Kalkan\Services\KalkanValidationService;

        $data = <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="no"?><xml>test<ds:Signature xmlns:ds="http://www.w3.org/2000/09/xmldsig#">
<ds:SignedInfo>
<ds:CanonicalizationMethod Algorithm="http://www.w3.org/TR/2001/REC-xml-c14n-20010315"/>
<ds:SignatureMethod Algorithm="urn:ietf:params:xml:ns:pkigovkz:xmlsec:algorithms:gostr34102015-gostr34112015-512"/>
<ds:Reference URI="">
<ds:Transforms>
<ds:Transform Algorithm="http://www.w3.org/2000/09/xmldsig#enveloped-signature"/>
<ds:Transform Algorithm="http://www.w3.org/TR/2001/REC-xml-c14n-20010315#WithComments"/>
</ds:Transforms>
<ds:DigestMethod Algorithm="urn:ietf:params:xml:ns:pkigovkz:xmlsec:algorithms:gostr34112015-512"/>
<ds:DigestValue>FFVWHD09L/oIjDWgJhj5cHYg5PiWhO7CgNVsGu7zOKnJuY+NUOAh710l/L9gg/Gh5bAPRQzfmEE8
CrROZfEjsA==</ds:DigestValue>
</ds:Reference>
</ds:SignedInfo>
<ds:SignatureValue>
ea8GGutdBw7ZDxmgnyAxt66Vu3DhLmdB4QM2HvPX/z9wBHOs/5mDcasD01c5qjh5PZIpSwG97Hl+
y2htV0XHMpMF3MwslTCpmcyqG2jwXp38BfrLjQIyUskKTxcK+LUr13mWOIAtlohq62Hu7d24723F
O3DSeV4JsjdVOKEVylE=
</ds:SignatureValue>
<ds:KeyInfo>
<ds:X509Data>
<ds:X509Certificate>
MIIENjCCA56gAwIBAgIUEUcodYLfm7RxDkYYBKzUm4i/RcQwDgYKKoMOAwoBAQIDAgUAMF0xTjBM
BgNVBAMMRdKw0JvQotCi0KvSmiDQmtCj05jQm9CQ0J3QlNCr0KDQo9Co0Ksg0J7QoNCi0JDQm9Cr
0pogKEdPU1QpIFRFU1QgMjAyMjELMAkGA1UEBhMCS1owHhcNMjMxMTA5MTAxODQwWhcNMjQxMTA4
MTAxODQwWjB5MR4wHAYDVQQDDBXQotCV0KHQotCe0JIg0KLQldCh0KIxFTATBgNVBAQMDNCi0JXQ
odCi0J7QkjEYMBYGA1UEBRMPSUlOMTIzNDU2Nzg5MDExMQswCQYDVQQGEwJLWjEZMBcGA1UEKgwQ
0KLQldCh0KLQntCS0JjQpzCBrDAjBgkqgw4DCgEBAgIwFgYKKoMOAwoBAQICAQYIKoMOAwoBAwMD
gYQABIGA2vzSnxC/K74V0b9QzJ/r2mM9GxhbOosYKI0SBCnAo2IbeA5HKzzhlt5PhEeGEdTN4wr9
bW+xR4vxEBVqDniAYRR6unCfzOmRy7nMo5yA4JPwu8724OZlwe2CEBsmBytAyReCth6RZMyeAzTD
/S6ayoBmKfVAlCFB9BX5cEJVV2qjggHGMIIBwjA4BgNVHSAEMTAvMC0GBiqDDgMDAjAjMCEGCCsG
AQUFBwIBFhVodHRwOi8vcGtpLmdvdi5rei9jcHMwdwYIKwYBBQUHAQEEazBpMCgGCCsGAQUFBzAB
hhxodHRwOi8vdGVzdC5wa2kuZ292Lmt6L29jc3AvMD0GCCsGAQUFBzAChjFodHRwOi8vdGVzdC5w
a2kuZ292Lmt6L2NlcnQvbmNhX2dvc3QyMDIyX3Rlc3QuY2VyMEEGA1UdHwQ6MDgwNqA0oDKGMGh0
dHA6Ly90ZXN0LnBraS5nb3Yua3ovY3JsL25jYV9nb3N0MjAyMl90ZXN0LmNybDBDBgNVHS4EPDA6
MDigNqA0hjJodHRwOi8vdGVzdC5wa2kuZ292Lmt6L2NybC9uY2FfZ29zdDIwMjJfZF90ZXN0LmNy
bDAdBgNVHSUEFjAUBggrBgEFBQcDBAYIKoMOAwMEAQEwDgYDVR0PAQH/BAQDAgPIMB0GA1UdDgQW
BBSBRyh1gt+btHEORhgErNSbiL9FxDAfBgNVHSMEGDAWgBT60ksbo6DJYf4cqFA+aqK7RQ24ozAW
BgYqgw4DAwUEDDAKBggqgw4DAwUBATAOBgoqgw4DCgEBAgMCBQADgYEAac/t5KbxJSmX7Mh1Wzlq
m83UuY6iGn2TrnDTTN0n6oFuNBT9lFyvX6HwwXF7K2cV0C/D/bd2vvTrLVTdild6yqX+0rf4mHtL
fZAo2WYt+9LeybWppE2ZGkaxXCS2di+bZwyPoV2NiA0QJRgQAtuCDy7LY+pySBNUtvBdUXgYcxM=
</ds:X509Certificate>
</ds:X509Data>
</ds:KeyInfo>
</ds:Signature></xml>
XML;

        $result = $service->verifyXml($data);
        $response = $service->getResponse();

        $this->assertIsArray($response, 'Получен некорректный ответ сервиса');
        $this->assertArrayHasKey('status', $response, 'Ответ не содержит статус');
        $this->assertEquals(200, $response['status'], 'Получен некорректный ответ сервиса');

        $this->assertTrue($result, 'Проверка подлинности XML не работает');
    }

    public function testCmsValidation(): void
    {
        $service = new \Mitwork\Kalkan\Services\KalkanValidationService;

        $data = <<<'DATA'
MIILtgYJKoZIhvcNAQcCoIILpzCCC6MCAQExDjAMBggqgw4DCgEDAwUAMBIGCSqGSIb3DQEHAaAFBAO16y2gggR6MIIEdjCCA96gAwIBAgIUUroyvCizfdta
1ODief3jFpXBDB0wDgYKKoMOAwoBAQIDAgUAMF0xTjBMBgNVBAMMRdKw0JvQotCi0KvSmiDQmtCj05jQm9CQ0J3QlNCr0KDQo9Co0Ksg0J7QoNCi0JDQm9Cr
0pogKEdPU1QpIFRFU1QgMjAyMjELMAkGA1UEBhMCS1owHhcNMjMxMTA5MTA1NTQ0WhcNMjQxMTA4MTA1NTQ0WjCBrTEeMBwGA1UEAwwV0KLQldCh0KLQntCS
INCi0JXQodCiMRUwEwYDVQQEDAzQotCV0KHQotCe0JIxGDAWBgNVBAUTD0lJTjEyMzQ1Njc4OTAxMTELMAkGA1UEBhMCS1oxGDAWBgNVBAoMD9CQ0J4gItCi
0JXQodCiIjEYMBYGA1UECwwPQklOMTIzNDU2Nzg5MDIxMRkwFwYDVQQqDBDQotCV0KHQotCe0JLQmNCnMIGsMCMGCSqDDgMKAQECAjAWBgoqgw4DCgEBAgIB
Bggqgw4DCgEDAwOBhAAEgYCHBo0n89NYZhLwKNvTD8+VMm1fwJLmTfC8lBKJoqaidlI8s8MGKbrnz1tnlMrguo4J7kCJdxgU1Zf70q9RC/8DKB4eeW57WmWI
ZPeWnLwbuUDWLS0qo9jtSiT0hXpa0BQ5pCO14ybvxJ9P+D7+hL9x1jslUc2kBt3jRnwDwQWvP6OCAdEwggHNMCgGA1UdJQQhMB8GCCsGAQUFBwMEBggqgw4D
AwQBAgYJKoMOAwMEAQIBMDgGA1UdIAQxMC8wLQYGKoMOAwMCMCMwIQYIKwYBBQUHAgEWFWh0dHA6Ly9wa2kuZ292Lmt6L2NwczB3BggrBgEFBQcBAQRrMGkw
KAYIKwYBBQUHMAGGHGh0dHA6Ly90ZXN0LnBraS5nb3Yua3ovb2NzcC8wPQYIKwYBBQUHMAKGMWh0dHA6Ly90ZXN0LnBraS5nb3Yua3ovY2VydC9uY2FfZ29z
dDIwMjJfdGVzdC5jZXIwQQYDVR0fBDowODA2oDSgMoYwaHR0cDovL3Rlc3QucGtpLmdvdi5rei9jcmwvbmNhX2dvc3QyMDIyX3Rlc3QuY3JsMEMGA1UdLgQ8
MDowOKA2oDSGMmh0dHA6Ly90ZXN0LnBraS5nb3Yua3ovY3JsL25jYV9nb3N0MjAyMl9kX3Rlc3QuY3JsMA4GA1UdDwEB/wQEAwIDyDAdBgNVHQ4EFgQU0roy
vCizfdta1ODief3jFpXBDB0wHwYDVR0jBBgwFoAU+tJLG6OgyWH+HKhQPmqiu0UNuKMwFgYGKoMOAwMFBAwwCgYIKoMOAwMFAQEwDgYKKoMOAwoBAQIDAgUA
A4GBAL3FB7lFUvFNzKsQVaeb+nVZv1CPv2XXbT5ph+xr+Mz2c1jQMvgVX35QFPVS9Eu+SLjnQf4RxuDiAbGh6Wz3023ZlqnTJ0XeDmGSRECzJoLwsvI8Cr6W
9ec7fo5muI84T81MtQ3+y928CV7zRnwvhfWtOL5JgO7vOK8B3jFtIuNBMYIG+jCCBvYCAQEwdTBdMU4wTAYDVQQDDEXSsNCb0KLQotCr0pog0JrQo9OY0JvQ
kNCd0JTQq9Cg0KPQqNCrINCe0KDQotCQ0JvQq9KaIChHT1NUKSBURVNUIDIwMjIxCzAJBgNVBAYTAktaAhRSujK8KLN921rU4OJ5/eMWlcEMHTAMBggqgw4D
CgEDAwUAoIGJMBgGCSqGSIb3DQEJAzELBgkqhkiG9w0BBwEwHAYJKoZIhvcNAQkFMQ8XDTI0MDcyMjEyMzI1MFowTwYJKoZIhvcNAQkEMUIEQI6vANnr3hkI
NHIeiEwuKiJh+foWwquQHsT0UuY7xeyvtC2ESvwRQ+LxG9EQvXKYz62MMrec8ZQJHoT3Du7JeZMwDgYKKoMOAwoBAQIDAgUABIGA7XFVnp2HFYvv89BTUfsJ
UFHa+mE2yr5ldhrpbpMZcgDfsV2ydfyQkScHoTqkUlzMpGIHx+3irG23ltg7NoXQiyilUlIx5dl0y54LdlahV8+TzUTkncoCzFidMYGEBXr5RuiPgyU3Swbe
ccIsxIdmKus/tg2ktbW6eAiH3PJExTehggVLMIIFRwYLKoZIhvcNAQkQAg4xggU2MIIFMgYJKoZIhvcNAQcCoIIFIzCCBR8CAQMxDjAMBggqgw4DCgEDAQUA
MIGBBgsqhkiG9w0BCRABBKByBHAwbgIBAQYIKoMOAwMCBgEwMDAMBggqgw4DCgEDAQUABCDO+gcO1oOvwAboAKhr/+Yt6rWfeb2zWgWxFn8rJu5XxgIUSf+Y
c2lKHRB89T0GZu1GfJH5tkAYDzIwMjQwNzIyMTIzMjUxWgIGAZDabikLoIIDNzCCAzMwggLdoAMCAQICFGMc57hpEmqNHDnl6CPWLseEvdrOMA0GCSqDDgMK
AQEBAgUAMC4xHzAdBgNVBAMMFtKw0prQniAzLjAgKEdPU1QgVEVTVCkxCzAJBgNVBAYTAktaMB4XDTIzMTExNzA5MjUwNVoXDTI2MTExNjA5MjUwNVowgYYx
LzAtBgNVBAMMJtCh0JXQoNCS0JjQoSDQnNCV0KLQmtCYINCS0KDQldCc0JXQndCYMQswCQYDVQQGEwJLWjEVMBMGA1UEBwwM0JDRgdGC0LDQvdCwMRUwEwYD
VQQIDAzQkNGB0YLQsNC90LAxGDAWBgNVBAoMD9CQ0J4gItCi0JXQodCiIjBsMCUGCSqDDgMKAQEBATAYBgoqgw4DCgEBAQEBBgoqgw4DCgEDAQEAA0MABEDj
JR8TW2D34+OVHeiFPHFFhbXSe2xdHcgY94gQMCoRls/buXWDYU9oTgBRxpUoqtitGWX/yxu+bIo7tZDKHlcno4IBaDCCAWQwFgYDVR0lAQH/BAwwCgYIKwYB
BQUHAwgwPQYDVR0fBDYwNDAyoDCgLoYsaHR0cDovL3Rlc3QucGtpLmdvdi5rei9jcmwvbmNhX2dvc3RfdGVzdC5jcmwwPwYDVR0uBDgwNjA0oDKgMIYuaHR0
cDovL3Rlc3QucGtpLmdvdi5rei9jcmwvbmNhX2RfZ29zdF90ZXN0LmNybDByBggrBgEFBQcBAQRmMGQwOQYIKwYBBQUHMAKGLWh0dHA6Ly90ZXN0LnBraS5n
b3Yua3ovY2VydC9uY2FfZ29zdF90ZXN0LmNlcjAnBggrBgEFBQcwAYYbaHR0cDovL3Rlc3QucGtpLmdvdi5rei9vY3NwMB0GA1UdDgQWBBTjHOe4aRJqjRw5
5egj1i7HhL3azjAfBgNVHSMEGDAWgBT+0Ha8xWK3oMZT44P1mwz/mETSHDAWBgYqgw4DAwUEDDAKBggqgw4DAwUBATANBgkqgw4DCgEBAQIFAANBAOs9NNTS
P5MNfAR2EWrJfo7HyAWbOMgJyPuS9ljN7w1YS1RLr6j62ntgt5O0OXiLJQL+N1ZNssyS+I1hIDFe888xggFJMIIBRQIBATBGMC4xHzAdBgNVBAMMFtKw0prQ
niAzLjAgKEdPU1QgVEVTVCkxCzAJBgNVBAYTAktaAhRjHOe4aRJqjRw55egj1i7HhL3azjAMBggqgw4DCgEDAQUAoIGYMBoGCSqGSIb3DQEJAzENBgsqhkiG
9w0BCRABBDAcBgkqhkiG9w0BCQUxDxcNMjQwNzIyMTIzMjUxWjArBgsqhkiG9w0BCRACDDEcMBowGDAWBBRL0U6vu9kkgJdzpqebGraRFek7ODAvBgkqhkiG
9w0BCQQxIgQg1vFsFAfNI8YJPc24puCxt6UjboRabuOzUIRLJriEavYwDQYJKoMOAwoBAQECBQAEQGluYERHf63WRI/J3AS4vB0JfReax8aWCNlf0qnfdU8l
cwoiiijEkppuSTYdvcsPvUx1u9vulZDUvMztpvvietY=
DATA;

        $result = $service->verifyCms($data, 'test');
        $response = $service->getResponse();

        $this->assertIsArray($response, 'Получен некорректный ответ сервиса');
        $this->assertArrayHasKey('status', $response, 'Ответ не содержит статус');
        $this->assertEquals(200, $response['status'], 'Получен некорректный ответ сервиса');

        $this->assertTrue($result, 'Проверка подлинности CMS не работает');
    }

    public function testValidationExceptions(): void
    {
        $validationService = new \Mitwork\Kalkan\Services\KalkanValidationService;

        $data = Str::random(64);

        $this->assertThrows(
            fn () => $validationService->verifyCms($data, 'hello', throw: true),
            KalkanValidationException::class
        );
    }
}
