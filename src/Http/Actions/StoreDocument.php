<?php

namespace Mitwork\Kalkan\Http\Actions;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Mitwork\Kalkan\CacheDocument;
use Mitwork\Kalkan\Events\DocumentSaved;
use Mitwork\Kalkan\Http\Requests\StoreDocumentRequest;
use Mitwork\Kalkan\Services\CacheDocumentService;
use Mitwork\Kalkan\Services\IntegrationService;
use Mitwork\Kalkan\Services\KalkanValidationService;
use Mitwork\Kalkan\Services\QrCodeGenerationService;

class StoreDocument extends BaseAction
{
    public function __construct(
        public IntegrationService $integrationService,
        public QrCodeGenerationService $qrCodeGenerationService,
        public KalkanValidationService $validationService,
        public CacheDocumentService $documentService,
    ) {

    }

    /**
     * Шаг 1 - отправка документа для последующей работы
     *
     * В данном примере содержимое документа сохраняется
     * только в кэш, в реально жизни это может быть база
     * данных или файловое/облачное хранилище.
     *
     * Последующие запросы используют этот идентификатор
     * для запроса и работы с данными.
     */
    public function store(StoreDocumentRequest $request): JsonResponse
    {
        $id = $request->input('id', Str::uuid());

        $document = new CacheDocument($request->validated());

        if (! $this->documentService->addDocument($id, $document->attributes())) {
            return response()->json([
                'message' => __('kalkan::messages.unable_to_save_document'),
            ], 500);
        }

        DocumentSaved::dispatch($id, $document->attributes());

        $link = $this->generateSignedLink('generate-link', ['id' => $id]);
        $result = $this->qrCodeGenerationService->generate($link);

        return response()->json([
            'id' => $id,
            'url' => $link,
            'links' => [
                'qr' => [
                    'uri' => $result->getDataUri(),
                    'raw' => base64_encode($result->getString()),
                ],
                'app' => [
                    'mobile' => sprintf(config('kalkan.links.mobile'), urlencode($link)),
                    'business' => sprintf(config('kalkan.links.business'), urlencode($link)),
                ],
            ],
        ]);
    }
}
