<?php

namespace Mitwork\Kalkan\Services;

use Mitwork\Kalkan\Contracts\SignatureService;
use Mitwork\Kalkan\Traits\NcanodeHttpClient;

class KalkanSignatureService implements SignatureService
{
    use NcanodeHttpClient;

    /**
     * @param string $xml
     * @param string $key
     * @param string $password
     * @param string|null $alias
     * @param bool $clearSignatures
     * @param bool $trimXml
     * @param bool $raw
     * @return string|array
     */
    public function signXml(string $xml, string $key, string $password, string $alias = null, bool $clearSignatures = false, bool $trimXml = false, bool $raw = false): string|array
    {
        $template = [
            'xml' => $xml,
            'signers' => [
                [
                    'key' => $key,
                    'password' => $password,
                    'keyAlias' => $alias
                ]
            ],
            'clearSignatures' => $clearSignatures,
            'trimXml' => $trimXml,
        ];

        $message = json_encode($template);
        $response = $this->request('/xml/sign', $message);

        if ($raw) {
            return $response;
        }

        return $response['xml'];
    }

    /**
     * @param string $data
     * @param string $key
     * @param string $password
     * @param string|null $alias
     * @param bool $withTsp
     * @param string $tsaPolicy
     * @param bool $detached
     * @param bool $raw
     * @return string|array
     */
    public function signCms(string $data, string $key, string $password, string $alias = null, bool $withTsp = true, string $tsaPolicy = 'TSA_GOST_POLICY', bool $detached = false, bool $raw = false): string|array
    {
        $template = [
            'data' => $data,
            'signers' => [
                [
                    'key' => $key,
                    'password' => $password,
                    'keyAlias' => $alias
                ]
            ],
            'withTsp' => $withTsp,
            'tsaPolicy' => $tsaPolicy,
            'detached' => $detached,
        ];

        $message = json_encode($template);
        $response = $this->request('/cms/sign', $message);

        if ($raw) {
            return $response;
        }

        return $response['cms'];
    }
}
