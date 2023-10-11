<?php

namespace Mitwork\Kalkan\Services;

use Mitwork\Kalkan\Contracts\ExtractionService;
use Mitwork\Kalkan\Traits\NcanodeHttpClient;

class KalkanExtractionService implements ExtractionService
{
    use NcanodeHttpClient;

    /**
     * Extract CMS data
     *
     * @param string $cms
     * @param bool $decode
     * @return string
     */
    public function extractCms(string $cms, bool $decode = false): string
    {
        $template = [
            'cms' => $cms
        ];

        $message = json_encode($template);

        $response = $this->request('/cms/extract', $message);

        $data = $response['data'];

        if ($decode) {
            return base64_decode($data);
        }

        return $data;
    }
}
