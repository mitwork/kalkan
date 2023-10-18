<?php

namespace Mitwork\Kalkan\Http\Actions;

use Illuminate\Http\JsonResponse;
use Mitwork\Kalkan\Enums\AuthType;
use Mitwork\Kalkan\Enums\ContentType;
use Mitwork\Kalkan\Enums\DocumentStatus;
use Mitwork\Kalkan\Events\AuthAccepted;
use Mitwork\Kalkan\Events\AuthRejected;
use Mitwork\Kalkan\Events\DocumentRequested;
use Mitwork\Kalkan\Events\RequestRequested;
use Mitwork\Kalkan\Http\Requests\FetchDocumentRequest;
use Mitwork\Kalkan\Services\CacheDocumentService;
use Mitwork\Kalkan\Services\CacheRequestService;
use Mitwork\Kalkan\Services\IntegrationService;

class PrepareContent extends BaseAction
{
    public function __construct(
        public CacheDocumentService $documentService,
        public IntegrationService $integrationService,
        public CacheRequestService $requestService
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

        $document = $this->requestService->get($id);

        if (! $document) {
            return response()->json([
                'message' => __('kalkan::messages.unable_to_get_request'),
            ], 500);
        }

        if (isset($document['auth']['type']) && $document['auth']['type'] === AuthType::BEARER->value) {

            $token = request()->bearerToken();

            if (! $token) {

                $message = __('kalkan::messages.empty_bearer_token');

                AuthRejected::dispatch($id, $message);

                return response()->json([
                    'message' => $message,
                ], 401);
            }

            if ($token !== $document['auth']['token']) {

                $message = __('kalkan::messages.wrong_bearer_token');

                AuthRejected::dispatch($id, $message);

                return response()->json([
                    'message' => $message,
                ], 403);
            }

            AuthAccepted::dispatch($id, $token);
        }

        $files = $document['files'];

        $response = [
            'signMethod' => '',
            'documentsToSign' => [],
        ];

        $type = ContentType::CMS;
        $mimes = collect($files)->groupBy('mime')->keys();

        if (count($mimes) === 1 && $mimes[0] === ContentType::TEXT_XML->value) {
            $type = ContentType::XML;
        }

        foreach ($files as $file) {

            if ($type === ContentType::XML) {
                $this->integrationService->addXmlDocument($id, $file['id'], $file['name'], $file['data'], $file['meta']);
                $document = $this->integrationService->getXmlDocuments($id);

            } else {
                $this->integrationService->addCmsDocument($id, $file['id'], $file['name'], $file['data'], $file['meta'], $file['mime']);
                $document = $this->integrationService->getCmsDocuments($id);

            }

            $response['signMethod'] = $document['signMethod'];
            $response['documentsToSign'] = array_merge($response['documentsToSign'], $document['documentsToSign']);

            $this->documentService->update($file['id'], DocumentStatus::REQUESTED);

            DocumentRequested::dispatch($file['id'], $request->all(), $file);
        }

        RequestRequested::dispatch($id, $request->all(), $response);

        return response()->json($response);
    }
}
