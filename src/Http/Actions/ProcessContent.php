<?php

namespace Mitwork\Kalkan\Http\Actions;

use Illuminate\Http\JsonResponse;
use Mitwork\Kalkan\Enums\AuthType;
use Mitwork\Kalkan\Enums\ContentType;
use Mitwork\Kalkan\Events\AuthRejected;
use Mitwork\Kalkan\Events\DocumentRejected;
use Mitwork\Kalkan\Events\DocumentSigned;
use Mitwork\Kalkan\Http\Requests\ProcessDocumentRequest;
use Mitwork\Kalkan\Services\CacheDocumentService;
use Mitwork\Kalkan\Services\KalkanValidationService;

class ProcessContent extends BaseAction
{
    public function __construct(
        public CacheDocumentService $documentService,
        public KalkanValidationService $validationService,
    ) {
    }

    /**
     * Шаг 4 - получение и обработка подписанных данных
     */
    public function process(ProcessDocumentRequest $request): JsonResponse
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

        $documents = $request->input('documentsToSign');

        foreach ($documents as $signedDocument) {

            if ($document['type'] === ContentType::XML->value) {
                $signature = $signedDocument['documentXml'];
                $result = $this->validationService->verifyXml($signature);
            } else {
                $signature = $signedDocument['documentCms'];
                $result = $this->validationService->verifyCms($signature, $document['content']);
            }

            if ($result !== true) {
                DocumentRejected::dispatch($id, $this->validationService->getError());

                return response()->json(['error' => $this->validationService->getError()], 422);
            }

            if (! $this->documentService->processDocument($id)) {
                DocumentRejected::dispatch($id, __('kalkan::messages.unable_to_process_document'));

                return response()->json(['error' => __('kalkan::messages.unable_to_process_document')], 500);
            }

            DocumentSigned::dispatch($id, $document['content'], $signature);
        }

        return response()->json([]);
    }
}
