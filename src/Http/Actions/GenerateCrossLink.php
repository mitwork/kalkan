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
        $id = $request->input('id');
        $link = $this->generateSignedLink(config('kalkan.actions.prepare-content'), ['id' => $id]);

        return response()->json([
            'mobile' => sprintf(config('kalkan.links.mobile'), urlencode($link)),
            'business' => sprintf(config('kalkan.links.business'), urlencode($link))]
        );
    }
}
