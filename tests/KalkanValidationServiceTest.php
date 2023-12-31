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
        $service = new \Mitwork\Kalkan\Services\KalkanValidationService();

        $data = <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="no"?><test>
  <message>Hello</message>
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
<ds:DigestValue>UX1fM667qTi5ZJqbz6PT7D0e4ZYwKsdKYjM5Qba/NcFKrdgp47RWI8e8eIGSE43PH+2vzEKeM+8J&#13;
sgxnD10EOA==</ds:DigestValue>
</ds:Reference>
</ds:SignedInfo>
<ds:SignatureValue>
ffmEea/KPaqJtg7P00tqTGSv488Qu4O9ivbtsm47uRa/nDPFom9AHpWHwxB6EyyeLg6tTTYymBHi&#13;
/b7crZFBlmFxHdthoT1cMFo8mhDLRY83Zp2CAKToogMwR8yybWfXguv8q/K0dM501letlo9PVtBC&#13;
B0hZhr+ZTE/q4i+eufM=
</ds:SignatureValue>
<ds:KeyInfo>
<ds:X509Data>
<ds:X509Certificate>
MIIEdjCCA96gAwIBAgIUIgikXaIEvr7SPCaCQmqXB0mg0lcwDgYKKoMOAwoBAQIDAgUAMF0xTjBM&#13;
BgNVBAMMRdKw0JvQotCi0KvSmiDQmtCj05jQm9CQ0J3QlNCr0KDQo9Co0Ksg0J7QoNCi0JDQm9Cr&#13;
0pogKEdPU1QpIFRFU1QgMjAyMjELMAkGA1UEBhMCS1owHhcNMjMwMzI3MDM1NTI4WhcNMjQwMzI2&#13;
MDM1NTI4WjCBrTEeMBwGA1UEAwwV0KLQldCh0KLQntCSINCi0JXQodCiMRUwEwYDVQQEDAzQotCV&#13;
0KHQotCe0JIxGDAWBgNVBAUTD0lJTjEyMzQ1Njc4OTAxMTELMAkGA1UEBhMCS1oxGDAWBgNVBAoM&#13;
D9CQ0J4gItCi0JXQodCiIjEYMBYGA1UECwwPQklOMTIzNDU2Nzg5MDIxMRkwFwYDVQQqDBDQotCV&#13;
0KHQotCe0JLQmNCnMIGsMCMGCSqDDgMKAQECAjAWBgoqgw4DCgEBAgIBBggqgw4DCgEDAwOBhAAE&#13;
gYDRF2HvtHCcgLVhaCi4Ge1weXWWtkN1KGVczGOxRwlHTbcCU7rD/yPFp4bJM9MHfOSN8W9a7tWG&#13;
p9bnQHouvVQbeiwXFChwTnwbiCgHbUpVCjNFt6RzX1iR5sVsUJJnQdV6UeMRx7OvCDQN2XhW3C6o&#13;
g7J9IBmFS+H8XR+EeRoVSqOCAdEwggHNMCgGA1UdJQQhMB8GCCsGAQUFBwMEBggqgw4DAwQBAgYJ&#13;
KoMOAwMEAQIBMDgGA1UdIAQxMC8wLQYGKoMOAwMCMCMwIQYIKwYBBQUHAgEWFWh0dHA6Ly9wa2ku&#13;
Z292Lmt6L2NwczB3BggrBgEFBQcBAQRrMGkwKAYIKwYBBQUHMAGGHGh0dHA6Ly90ZXN0LnBraS5n&#13;
b3Yua3ovb2NzcC8wPQYIKwYBBQUHMAKGMWh0dHA6Ly90ZXN0LnBraS5nb3Yua3ovY2VydC9uY2Ff&#13;
Z29zdDIwMjJfdGVzdC5jZXIwQQYDVR0fBDowODA2oDSgMoYwaHR0cDovL3Rlc3QucGtpLmdvdi5r&#13;
ei9jcmwvbmNhX2dvc3QyMDIyX3Rlc3QuY3JsMEMGA1UdLgQ8MDowOKA2oDSGMmh0dHA6Ly90ZXN0&#13;
LnBraS5nb3Yua3ovY3JsL25jYV9nb3N0MjAyMl9kX3Rlc3QuY3JsMA4GA1UdDwEB/wQEAwIDyDAd&#13;
BgNVHQ4EFgQUogikXaIEvr7SPCaCQmqXB0mg0lcwHwYDVR0jBBgwFoAU+tJLG6OgyWH+HKhQPmqi&#13;
u0UNuKMwFgYGKoMOAwMFBAwwCgYIKoMOAwMFAQEwDgYKKoMOAwoBAQIDAgUAA4GBADX10/8QEFKk&#13;
b/Zfni0rmIwvbhc09IVAMc03AnkZziQxHGR82HH+a2XTpYPmP3YLXKjtBbGbR9yd7lt5lEmp2U7b&#13;
dHYHYc/mZBFZ16G8ALz4XupSgmwTGw4f5OkaCcfYu6M3L+ntvQxQna9bfdOqAmzUqeDEHrMYQzcU&#13;
1M+apBCR
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

    public function testCmsValidation(): void
    {
        $service = new \Mitwork\Kalkan\Services\KalkanValidationService();

        $data = <<<'DATA'
MIIMgAYJKoZIhvcNAQcCoIIMcTCCDG0CAQExDjAMBggqgw4DCgEDAwUAMBIGCSqGSIb3DQEHAaAFBAO16y2gggR6MIIEdjCCA96gAwIBAgIUIgikXaIEvr7S
PCaCQmqXB0mg0lcwDgYKKoMOAwoBAQIDAgUAMF0xTjBMBgNVBAMMRdKw0JvQotCi0KvSmiDQmtCj05jQm9CQ0J3QlNCr0KDQo9Co0Ksg0J7QoNCi0JDQm9Cr
0pogKEdPU1QpIFRFU1QgMjAyMjELMAkGA1UEBhMCS1owHhcNMjMwMzI3MDM1NTI4WhcNMjQwMzI2MDM1NTI4WjCBrTEeMBwGA1UEAwwV0KLQldCh0KLQntCS
INCi0JXQodCiMRUwEwYDVQQEDAzQotCV0KHQotCe0JIxGDAWBgNVBAUTD0lJTjEyMzQ1Njc4OTAxMTELMAkGA1UEBhMCS1oxGDAWBgNVBAoMD9CQ0J4gItCi
0JXQodCiIjEYMBYGA1UECwwPQklOMTIzNDU2Nzg5MDIxMRkwFwYDVQQqDBDQotCV0KHQotCe0JLQmNCnMIGsMCMGCSqDDgMKAQECAjAWBgoqgw4DCgEBAgIB
Bggqgw4DCgEDAwOBhAAEgYDRF2HvtHCcgLVhaCi4Ge1weXWWtkN1KGVczGOxRwlHTbcCU7rD/yPFp4bJM9MHfOSN8W9a7tWGp9bnQHouvVQbeiwXFChwTnwb
iCgHbUpVCjNFt6RzX1iR5sVsUJJnQdV6UeMRx7OvCDQN2XhW3C6og7J9IBmFS+H8XR+EeRoVSqOCAdEwggHNMCgGA1UdJQQhMB8GCCsGAQUFBwMEBggqgw4D
AwQBAgYJKoMOAwMEAQIBMDgGA1UdIAQxMC8wLQYGKoMOAwMCMCMwIQYIKwYBBQUHAgEWFWh0dHA6Ly9wa2kuZ292Lmt6L2NwczB3BggrBgEFBQcBAQRrMGkw
KAYIKwYBBQUHMAGGHGh0dHA6Ly90ZXN0LnBraS5nb3Yua3ovb2NzcC8wPQYIKwYBBQUHMAKGMWh0dHA6Ly90ZXN0LnBraS5nb3Yua3ovY2VydC9uY2FfZ29z
dDIwMjJfdGVzdC5jZXIwQQYDVR0fBDowODA2oDSgMoYwaHR0cDovL3Rlc3QucGtpLmdvdi5rei9jcmwvbmNhX2dvc3QyMDIyX3Rlc3QuY3JsMEMGA1UdLgQ8
MDowOKA2oDSGMmh0dHA6Ly90ZXN0LnBraS5nb3Yua3ovY3JsL25jYV9nb3N0MjAyMl9kX3Rlc3QuY3JsMA4GA1UdDwEB/wQEAwIDyDAdBgNVHQ4EFgQUogik
XaIEvr7SPCaCQmqXB0mg0lcwHwYDVR0jBBgwFoAU+tJLG6OgyWH+HKhQPmqiu0UNuKMwFgYGKoMOAwMFBAwwCgYIKoMOAwMFAQEwDgYKKoMOAwoBAQIDAgUA
A4GBADX10/8QEFKkb/Zfni0rmIwvbhc09IVAMc03AnkZziQxHGR82HH+a2XTpYPmP3YLXKjtBbGbR9yd7lt5lEmp2U7bdHYHYc/mZBFZ16G8ALz4XupSgmwT
Gw4f5OkaCcfYu6M3L+ntvQxQna9bfdOqAmzUqeDEHrMYQzcU1M+apBCRMYIHxDCCB8ACAQEwdTBdMU4wTAYDVQQDDEXSsNCb0KLQotCr0pog0JrQo9OY0JvQ
kNCd0JTQq9Cg0KPQqNCrINCe0KDQotCQ0JvQq9KaIChHT1NUKSBURVNUIDIwMjIxCzAJBgNVBAYTAktaAhQiCKRdogS+vtI8JoJCapcHSaDSVzAMBggqgw4D
CgEDAwUAoIGJMBgGCSqGSIb3DQEJAzELBgkqhkiG9w0BBwEwHAYJKoZIhvcNAQkFMQ8XDTIzMTAxMTA5MzExNVowTwYJKoZIhvcNAQkEMUIEQI6vANnr3hkI
NHIeiEwuKiJh+foWwquQHsT0UuY7xeyvtC2ESvwRQ+LxG9EQvXKYz62MMrec8ZQJHoT3Du7JeZMwDgYKKoMOAwoBAQIDAgUABIGAl4c4Obo4YT8PB8BO1b5o
C6CNGs0xCpzW0x0A1bxFdaZMiaI5hhtyCDVT6Mn8kBBvr9HlQuhEkNkL68QUcfKo9VH+AHJr2PHSvbOeQ2dWL8yY9t/tK/H0Osto05m8IJbzSwanwG4pXmWj
DD2I1MYdDHSN6rFnTYDWuMcPab/sEt6hggYVMIIGEQYLKoZIhvcNAQkQAg4xggYAMIIF/AYJKoZIhvcNAQcCoIIF7TCCBekCAQMxDjAMBggqgw4DCgEDAQUA
MIGBBgsqhkiG9w0BCRABBKByBHAwbgIBAQYIKoMOAwMCBgEwMDAMBggqgw4DCgEDAQUABCAxoW8oTQyR4hjwlX0+ZMNFdWa9GeXqOd587P7ncOx48QIU33Ux
9AIf0l12Osb1NTldXYPcODIYDzIwMjMxMDExMDkzMTE2WgIGAYseE369oIID3DCCA9gwggOCoAMCAQICFCIc8SBfYAQyFO48quEvxoVIaIqqMA0GCSqDDgMK
AQEBAgUAMFMxCzAJBgNVBAYTAktaMUQwQgYDVQQDDDvSsNCb0KLQotCr0pog0JrQo9OY0JvQkNCd0JTQq9Cg0KPQqNCrINCe0KDQotCQ0JvQq9KaIChHT1NU
KTAeFw0yMjEwMTgwNTQxMjhaFw0yNTA2MDEwNTQxMjhaMIIBBDEUMBIGA1UEAwwLVFNBIFNFUlZJQ0UxGDAWBgNVBAUTD0lJTjc2MTIzMTMwMDMxMzELMAkG
A1UEBhMCS1oxFTATBgNVBAcMDNCQ0KHQotCQ0J3QkDEVMBMGA1UECAwM0JDQodCi0JDQndCQMRgwFgYDVQQLDA9CSU4wMDA3NDAwMDA3MjgxfTB7BgNVBAoM
dNCQ0JrQptCY0J7QndCV0KDQndCe0JUg0J7QkdCp0JXQodCi0JLQniAi0J3QkNCm0JjQntCd0JDQm9Cs0J3Qq9CVINCY0J3QpNCe0KDQnNCQ0KbQmNCe0J3Q
ndCr0JUg0KLQldCl0J3QntCb0J7Qk9CY0JgiMGwwJQYJKoMOAwoBAQEBMBgGCiqDDgMKAQEBAQEGCiqDDgMKAQMBAQADQwAEQJfUyOKv1RC6FUNVwwssJn5l
AtE8wCRY2LJu/KHjpYbHKJ6aX2Q6ETVUhkI/NC1C0uPaZR+cNBCLHVFaVN/NGJGjggFpMIIBZTAWBgNVHSUBAf8EDDAKBggrBgEFBQcDCDAPBgNVHSMECDAG
gARbanPpMB0GA1UdDgQWBBQLYf8Me7L4/rJMkRZaz/vsEH3TrTBYBgNVHR8EUTBPME2gS6BJhiJodHRwOi8vY3JsLnBraS5nb3Yua3ovbmNhX2dvc3QuY3Js
hiNodHRwOi8vY3JsMS5wa2kuZ292Lmt6L25jYV9nb3N0LmNybDBcBgNVHS4EVTBTMFGgT6BNhiRodHRwOi8vY3JsLnBraS5nb3Yua3ovbmNhX2RfZ29zdC5j
cmyGJWh0dHA6Ly9jcmwxLnBraS5nb3Yua3ovbmNhX2RfZ29zdC5jcmwwYwYIKwYBBQUHAQEEVzBVMC8GCCsGAQUFBzAChiNodHRwOi8vcGtpLmdvdi5rei9j
ZXJ0L25jYV9nb3N0LmNlcjAiBggrBgEFBQcwAYYWaHR0cDovL29jc3AucGtpLmdvdi5rejANBgkqgw4DCgEBAQIFAANBAO/CJUPBc5wdNaizlYc9mQUPXowZ
r0EB2CA7a0mAXBKDfNnN+DicK2U72Zy1TUY3C1UI1z2ZbY6G+IAFF66NrncxggFuMIIBagIBATBrMFMxCzAJBgNVBAYTAktaMUQwQgYDVQQDDDvSsNCb0KLQ
otCr0pog0JrQo9OY0JvQkNCd0JTQq9Cg0KPQqNCrINCe0KDQotCQ0JvQq9KaIChHT1NUKQIUIhzxIF9gBDIU7jyq4S/GhUhoiqowDAYIKoMOAwoBAwEFAKCB
mDAaBgkqhkiG9w0BCQMxDQYLKoZIhvcNAQkQAQQwHAYJKoZIhvcNAQkFMQ8XDTIzMTAxMTA5MzExNlowKwYLKoZIhvcNAQkQAgwxHDAaMBgwFgQUHdTZ1OWT
y5BEMewHh0zc5N/1OpowLwYJKoZIhvcNAQkEMSIEIIkplMw1PyEYPNPZRgBfhHTyNgEGCJZVM4rqeZOhX7XoMA0GCSqDDgMKAQEBAgUABEC89ZAPvduXugWk
P+JQeOCC+d0Br3ZRUs9tTuwVfd1DoW+X77YzSOVEk0+pP4LE/nMhsnF6EFsxnwkPQNPmsHDS
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
        $validationService = new \Mitwork\Kalkan\Services\KalkanValidationService();

        $data = Str::random(64);

        $this->assertThrows(
            fn () => $validationService->verifyCms($data, 'hello', throw: true),
            KalkanValidationException::class
        );
    }
}
