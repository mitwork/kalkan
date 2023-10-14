<?php

namespace Mitwork\Kalkan\Http\Actions;

use Illuminate\Http\JsonResponse;
use Mitwork\Kalkan\Http\Requests\FetchDocumentRequest;

class GenerateCrossLink extends BaseAction
{
    /**
     * Шаг 1.2 - генерация кросс-ссылок
     *
     * Данные ссылки генерируются для возможности
     * работы с подписанием через мобильное приложение
     * eGov Mobile или eGov business.
     */
    public function generate(FetchDocumentRequest $request): JsonResponse
    {
        $link = $this->generateSignedLink('prepare-content', ['id' => $request->get('id')]);

        return response()->json([
            'person' => sprintf(config('kalkan.links.mobile'), urlencode($link)),
            'legal' => sprintf(config('kalkan.links.business'), urlencode($link))]
        );
    }
}
