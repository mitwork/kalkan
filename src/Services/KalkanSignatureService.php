<?php

namespace Mitwork\Kalkan\Services;

use Mitwork\Kalkan\Contracts\BaseService;
use Mitwork\Kalkan\Contracts\SignatureService;
use Mitwork\Kalkan\Enums\TsaPolicy;
use Mitwork\Kalkan\Traits\NcanodeHttpClient;

class KalkanSignatureService extends BaseService implements SignatureService
{
    use NcanodeHttpClient;

    /**
     * {@inheritDoc}
     */
    public function signXml(string $xml, string $key, string $password, string $alias = null, bool $clearSignatures = false, bool $trimXml = false, bool $raw = false): string|array
    {
        if (str_contains($key, PHP_EOL)) {
            $key = str_replace(PHP_EOL, '', $key);
        }

        $template = [
            'xml' => $xml,
            'signers' => [
                [
                    'key' => $key,
                    'password' => $password,
                    'keyAlias' => $alias,
                ],
            ],
            'clearSignatures' => $clearSignatures,
            'trimXml' => $trimXml,
        ];

        $message = json_encode($template);
        $response = $this->request('/xml/sign', $message);

        $this->setResponse($response);

        if ($raw) {
            return $response;
        }

        return $response['xml'];
    }

    /**
     * {@inheritDoc}
     */
    public function signCms(string $data, string $key, string $password, string $alias = null, bool $withTsp = true, TsaPolicy $tsaPolicy = TsaPolicy::TSA_GOST_POLICY, bool $detached = false, string $cms = null, bool $raw = false): string|array
    {
        if (str_contains($key, PHP_EOL)) {
            $key = str_replace(PHP_EOL, '', $key);
        }

        $template = [
            'data' => $data,
            'signers' => [
                [
                    'key' => $key,
                    'password' => $password,
                    'keyAlias' => $alias,
                ],
            ],
            'withTsp' => $withTsp,
            'tsaPolicy' => $tsaPolicy,
            'detached' => $detached,
        ];

        if ($cms) {
            $template['cms'] = $cms;
        }

        $message = json_encode($template);

        if ($cms) {
            $response = $this->request('/cms/sign/add', $message);
        } else {
            $response = $this->request('/cms/sign', $message);
        }

        $this->setResponse($response);

        if ($raw) {
            return $response;
        }

        return $response['cms'];
    }
}
