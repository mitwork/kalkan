<?php

namespace Mitwork\Kalkan\Http\Actions;

use Illuminate\Http\JsonResponse;
use Mitwork\Kalkan\Http\Requests\FetchDocumentRequest;
use Mitwork\Kalkan\Services\IntegrationService;

class GenerateServiceLink extends BaseAction
{
    public function __construct(
        public IntegrationService $integrationService
    ) {
    }

    /**
     * Шаг 2 - генерация сервисных данных
     */
    public function generate(FetchDocumentRequest $request): JsonResponse
    {
        $link = $this->generateSignedLink('prepare-content', ['id' => $request->input('id')]);
        $data = $this->integrationService->prepareServiceData($link);

        return response()->json($data);
    }
}
