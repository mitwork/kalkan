<?php

namespace Mitwork\Kalkan\Http\Actions;

use Illuminate\Http\JsonResponse;
use Mitwork\Kalkan\Http\Requests\FetchDocumentRequest;
use Mitwork\Kalkan\Services\CacheDocumentService;
use Mitwork\Kalkan\Services\IntegrationService;

class GenerateServiceLink extends BaseAction
{
    public function __construct(
        public IntegrationService $integrationService,
        public CacheDocumentService $documentService,
    ) {
    }

    /**
     * Шаг 2 - генерация сервисных данных
     */
    public function generate(FetchDocumentRequest $request): JsonResponse
    {
        $id = $request->input('id');
        $document = $this->documentService->getDocument($id);

        $auth = $document['auth'];

        $link = $this->generateSignedLink(config('kalkan.actions.prepare-content'), ['id' => $id]);
        $data = $this->integrationService->prepareServiceData($link, $auth['type'], $auth['token']);

        return response()->json($data);
    }
}