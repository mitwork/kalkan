<?php

namespace Mitwork\Kalkan\Http\Actions;

use Illuminate\Http\JsonResponse;
use Mitwork\Kalkan\Enums\AuthType;
use Mitwork\Kalkan\Enums\DocumentStatus;
use Mitwork\Kalkan\Enums\RequestStatus;
use Mitwork\Kalkan\Events\AuthAccepted;
use Mitwork\Kalkan\Events\AuthRejected;
use Mitwork\Kalkan\Events\DocumentRejected;
use Mitwork\Kalkan\Events\DocumentSigned;
use Mitwork\Kalkan\Events\RequestProcessed;
use Mitwork\Kalkan\Events\RequestRejected;
use Mitwork\Kalkan\Http\Requests\ProcessDocumentRequest;
use Mitwork\Kalkan\Services\CacheDocumentService;
use Mitwork\Kalkan\Services\CacheRequestService;
use Mitwork\Kalkan\Services\KalkanValidationService;

class ProcessContent extends BaseAction
{
    public function __construct(
        public CacheDocumentService $documentService,
        public CacheRequestService $requestService,
        public KalkanValidationService $validationService,
    ) {
    }

    /**
     * Шаг 4 - получение и обработка подписанных данных
     */
    public function process(ProcessDocumentRequest $request): JsonResponse
    {
        $id = $request->input('id');

        $document = $this->requestService->get($id);

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

            $original = $this->documentService->get($signedDocument['id']);

            if (isset($signedDocument['documentXml'])) {
                $signature = $signedDocument['documentXml'];
                $result = $this->validationService->verifyXml($signature, raw: true);
            } else {
                $signature = $signedDocument['document']['file']['data'];
                $result = $this->validationService->verifyCms($signature, $original['data'], raw: true);
            }

            if (! isset($result['valid']) || $result['valid'] !== true) {

                $message = $this->validationService->getError();

                if (! $message) {
                    $message = __('kalkan::messages.unable_to_process_document');
                }

                if ($this->documentService->reject($signedDocument['id'], $message)) {
                    DocumentRejected::dispatch($signedDocument['id'], $message, $result, $id);
                }

                if ($this->requestService->reject($id, $message)) {
                    RequestRejected::dispatch($id, $message, $result, $signedDocument['id']);
                }

                return response()->json(['error' => $message, 'result' => $result], 500);
            }

            if (! $this->documentService->process($signedDocument['id'], $result)) {

                $message = __('kalkan::messages.unable_to_process_document');

                if ($this->documentService->reject($signedDocument['id'], $message)) {
                    DocumentRejected::dispatch($signedDocument['id'], $message, $result, $id);
                }

                if ($this->requestService->reject($id, $message)) {
                    RequestRejected::dispatch($id, $message, $result, $signedDocument['id']);
                }

                return response()->json(['error' => $message], 500);
            }

            $this->documentService->update($signedDocument['id'], DocumentStatus::SIGNED);

            DocumentSigned::dispatch($signedDocument['id'], $original['data'], $signature, $result, $id);
        }

        $this->requestService->update($id, RequestStatus::PROCESSED);

        RequestProcessed::dispatch($id, $request->all());

        return response()->json([]);
    }
}
