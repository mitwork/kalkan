<?php

namespace Mitwork\Kalkan\Services;

use Mitwork\Kalkan\Contracts\ValidationService;
use Mitwork\Kalkan\Traits\NcanodeHttpClient;

class KalkanValidationService implements ValidationService
{
    use NcanodeHttpClient;

    /**
     * @param string $xml
     * @param bool $verifyCrl
     * @param bool $verifyOcsp
     * @param bool $raw
     * @return bool|array
     */
    public function verifyXml(string $xml, bool $verifyCrl = true, bool $verifyOcsp = true, bool $raw = false): bool|array
    {
        $template = [
            'revocationCheck' => [],
            'xml' => $xml
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

        if ($raw) {
            return $response;
        }

        return isset($response['valid']) && $response['valid'] === true;

    }

    /**
     * @param string $cms
     * @param string $data
     * @param bool $verifyCrl
     * @param bool $verifyOcsp
     * @param bool $raw
     * @return bool|array
     */
    public function verifyCms(string $cms, string $data, bool $verifyCrl = true, bool $verifyOcsp = true, bool $raw = false): bool|array
    {
        $template = [
            'revocationCheck' => [],
            'cms' => $cms,
            'data' => $data
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

        if ($raw) {
            return $response;
        }

        return isset($response['valid']) && $response['valid'] === true;
    }
}
