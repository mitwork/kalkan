<?php

namespace Mitwork\Kalkan\Services;

use Mitwork\Kalkan\Contracts\ValidationService;
use Mitwork\Kalkan\Enums\RevocationCheck;
use Mitwork\Kalkan\Exceptions\KalkanValidationException;
use Mitwork\Kalkan\Traits\NcanodeHttpClient;

class KalkanValidationService extends BaseService implements ValidationService
{
    use NcanodeHttpClient;

    /**
     * {@inheritDoc}
     */
    public function verifyXml(string $xml, bool $verifyCrl = true, bool $verifyOcsp = true, bool $raw = false, bool $throw = false): bool|array
    {
        $template = [
            'revocationCheck' => [],
            'xml' => $xml,
        ];

        if ($verifyOcsp) {
            $template['revocationCheck'][] = RevocationCheck::OCSP;
        }

        if ($verifyCrl) {
            $template['revocationCheck'][] = RevocationCheck::CRL;
        }

        if (count($template['revocationCheck']) === 0) {
            unset($template['revocationCheck']);
        }

        $message = json_encode($template);

        $response = $this->request('/xml/verify', $message, throw: ! $throw);

        $this->setResponse($response);

        if ($raw) {
            return $response;
        }

        $result = isset($response['valid']) && $response['valid'] === true;

        if ($result === false && $throw) {
            throw KalkanValidationException::create($response);
        }

        return $result;

    }

    /**
     * {@inheritDoc}
     */
    public function verifyCms(string $cms, string $data, bool $verifyCrl = true, bool $verifyOcsp = true, bool $raw = false, bool $throw = false): bool|array
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
            $template['revocationCheck'][] = RevocationCheck::OCSP;
        }

        if ($verifyCrl) {
            $template['revocationCheck'][] = RevocationCheck::CRL;
        }

        if (count($template['revocationCheck']) === 0) {
            unset($template['revocationCheck']);
        }

        $message = json_encode($template);

        $response = $this->request('/cms/verify', $message, throw: ! $throw);

        $this->setResponse($response);

        if ($raw) {
            return $response;
        }

        $result = isset($response['valid']) && $response['valid'] === true;

        if ($result === false && $throw) {
            throw KalkanValidationException::create($response);
        }

        return $result;
    }
}
