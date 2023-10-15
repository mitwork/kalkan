<?php

namespace Mitwork\Kalkan\Http\Actions;

use Illuminate\Http\JsonResponse;
use Mitwork\Kalkan\Enums\AuthType;
use Mitwork\Kalkan\Enums\ContentType;
use Mitwork\Kalkan\Events\AuthRejected;
use Mitwork\Kalkan\Http\Requests\FetchDocumentRequest;
use Mitwork\Kalkan\Services\CacheDocumentService;
use Mitwork\Kalkan\Services\IntegrationService;

class PrepareContent extends BaseAction
{
    public function __construct(
        public CacheDocumentService $documentService,
        public IntegrationService $integrationService,
    ) {
    }

    /**
     * Шаг 3 - работа с контентом документа
     *
     * Возврат содержимого документов
     */
    public function prepare(FetchDocumentRequest $request): JsonResponse
    {
        $id = $request->input('id');

        $document = $this->documentService->getDocument($id);

        if (! $document) {
            return response()->json([
                'message' => __('kalkan::messages.unable_to_get_document'),
            ], 500);
        }

        if (isset($document['auth']['type']) && $document['auth']['type'] === AuthType::BEARER->value) {

            $token = request()->bearerToken();

            if (! $token) {

                AuthRejected::dispatch($id);

                return response()->json([
                    'message' => __('kalkan::messages.empty_bearer_token'),
                ], 401);
            }

            if ($token !== $document['auth']['token']) {

                AuthRejected::dispatch($id);

                return response()->json([
                    'message' => __('kalkan::messages.wrong_bearer_token'),
                ], 403);
            }
        }

        if (! isset($document['meta'])) {
            $document['meta'] = [];
        }

        if ($document['type'] === ContentType::XML->value) {
            $this->integrationService->addXmlDocument($id, $document['name'], $document['content'], $document['meta']);
            $response = $this->integrationService->getXmlDocuments($id);

        } else {
            $this->integrationService->addCmsDocument($id, $document['name'], $document['content'], $document['meta']);
            $response = $this->integrationService->getCmsDocuments($id);

        }

        return response()->json($response);
    }
}
