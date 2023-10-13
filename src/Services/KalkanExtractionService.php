<?php

namespace Mitwork\Kalkan\Services;

use Mitwork\Kalkan\Contracts\ExtractionService;
use Mitwork\Kalkan\Traits\NcanodeHttpClient;

class KalkanExtractionService extends BaseService implements ExtractionService
{
    use NcanodeHttpClient;

    /**
     * {@inheritDoc}
     */
    public function extractCms(string $cms, bool $decode = false): string
    {
        if (str_contains($cms, PHP_EOL)) {
            $cms = str_replace(PHP_EOL, '', $cms);
        }

        $template = [
            'cms' => $cms,
        ];

        $message = json_encode($template);

        $response = $this->request('/cms/extract', $message);

        $data = $response['data'];

        $this->setResponse($response);

        if ($decode) {
            return base64_decode($data);
        }

        return $data;
    }
}
