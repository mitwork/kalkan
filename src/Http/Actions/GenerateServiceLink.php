<?php

namespace Mitwork\Kalkan\Http\Actions;

use Illuminate\Http\JsonResponse;
use Mitwork\Kalkan\Http\Requests\FetchRequestRequest;
use Mitwork\Kalkan\Services\CacheRequestService;
use Mitwork\Kalkan\Services\IntegrationService;

class GenerateServiceLink extends BaseAction
{
    public function __construct(
        public IntegrationService $integrationService,
        public CacheRequestService $requestService,
    ) {
    }

    /**
     * Шаг 2 - генерация сервисных данных
     */
    public function generate(FetchRequestRequest $request): JsonResponse
    {
        $id = $request->input('id');
        $document = $this->requestService->get($id);

        $link = $this->generateTemporaryLink(config('kalkan.actions.prepare-content'), ['id' => $id]);

        $data = $this->integrationService->prepareServiceData($link, $document);

        return response()->json($data);
    }
}
