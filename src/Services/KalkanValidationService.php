<?php

namespace Mitwork\Kalkan\Services;

use Mitwork\Kalkan\Contracts\BaseService;
use Mitwork\Kalkan\Contracts\ValidationService;
use Mitwork\Kalkan\Traits\NcanodeHttpClient;

class KalkanValidationService extends BaseService implements ValidationService
{
    use NcanodeHttpClient;

    /**
     * {@inheritDoc}
     */
    public function verifyXml(string $xml, bool $verifyCrl = true, bool $verifyOcsp = true, bool $raw = false): bool|array
    {
        $template = [
            'revocationCheck' => [],
            'xml' => $xml,
        ];

        if ($verifyOcsp) {
            $template['revocationCheck'][] = 'OCSP';
        }

        if ($verifyCrl) {
            $template['revocationCheck'][] = 'CRL';
        }

        if (count($template['revocationCheck']) === 0) {
            unset($template['revocationCheck']);
        }

        $message = json_encode($template);

        $response = $this->request('/xml/verify', $message);

        $this->setResponse($response);

        if ($raw) {
            return $response;
        }

        return isset($response['valid']) && $response['valid'] === true;

    }

    /**
     * {@inheritDoc}
     */
    public function verifyCms(string $cms, string $data, bool $verifyCrl = true, bool $verifyOcsp = true, bool $raw = false): bool|array
    {
        if (str_contains($cms, PHP_EOL)) {
            $cms = str_replace(PHP_EOL, '', $cms);
        }

        $template = [
            'revocationCheck' => [],
            'cms' => $cms,
            'data' => $data,
        ];

        if ($verifyOcsp) {
            $template['revocationCheck'][] = 'OCSP';
        }

        if ($verifyCrl) {
            $template['revocationCheck'][] = 'CRL';
        }

        if (count($template['revocationCheck']) === 0) {
            unset($template['revocationCheck']);
        }

        $message = json_encode($template);

        $response = $this->request('/cms/verify', $message);

        $this->setResponse($response);

        if ($raw) {
            return $response;
        }

        return isset($response['valid']) && $response['valid'] === true;
    }
}
