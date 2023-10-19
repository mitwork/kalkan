<?php

namespace Mitwork\Kalkan\Http\Actions;

use Illuminate\Http\JsonResponse;
use Mitwork\Kalkan\Http\Requests\FetchRequestRequest;

class GenerateCrossLink extends BaseAction
{
    /**
     * Шаг 1.2 - генерация кросс-ссылок
     *
     * Данные ссылки генерируются для возможности
     * работы с подписанием через мобильное приложение
     * eGov Mobile или eGov business.
     */
    public function generate(FetchRequestRequest $request): JsonResponse
    {
        $id = $request->input('id');
        $link = $this->generateTemporaryLink(config('kalkan.actions.generate-service-link'), ['id' => $id]);

        return response()->json([
            'mobile' => sprintf(config('kalkan.links.mobile'), $link),
            'business' => sprintf(config('kalkan.links.business'), $link),
        ]
        );
    }
}
