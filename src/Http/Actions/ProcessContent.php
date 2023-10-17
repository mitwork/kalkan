<?php

namespace Mitwork\Kalkan\Http\Actions;

use Illuminate\Http\JsonResponse;
use Mitwork\Kalkan\Enums\AuthType;
use Mitwork\Kalkan\Enums\ContentType;
use Mitwork\Kalkan\Enums\DocumentStatus;
use Mitwork\Kalkan\Events\AuthAccepted;
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
                    'message' => __('kalkan::messages.wrong_bearer_token'),
                ], 403);
            }

            AuthAccepted::dispatch($id, $token);
        }

        $documents = $request->input('documentsToSign');

        foreach ($documents as $signedDocument) {

            if ($document['type'] === ContentType::XML->value) {
                $signature = $signedDocument['documentXml'];
                $result = $this->validationService->verifyXml($signature, raw: true);
            } else {
                $signature = $signedDocument['document']['file']['data'];
                $result = $this->validationService->verifyCms($signature, $document['content'], raw: true);
            }

            if (! isset($result['valid']) || $result['valid'] !== true) {

                $message = $this->validationService->getError();

                if (! $message) {
                    $message = __('kalkan::messages.unable_to_process_document');
                }

                DocumentRejected::dispatch($id, $message, $result);

                if ($this->documentService->rejectDocument($id, $message)) {
                    return response()->json(['error' => $message, 'result' => $result], 422);
                }

                return response()->json(['error' => $message, 'result' => $result], 500);
            }

            if (! $this->documentService->processDocument($id, $result)) {

                $message = __('kalkan::messages.unable_to_process_document');

                $this->documentService->rejectDocument($id, $message);

                DocumentRejected::dispatch($id, $message, $result);

                return response()->json(['error' => $message], 500);
            }

            $this->documentService->changeStatus($id, DocumentStatus::SIGNED);

            DocumentSigned::dispatch($id, $document['content'], $signature, $result);
        }

        return response()->json([]);
    }
}
