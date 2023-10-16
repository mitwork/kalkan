<?php

namespace Mitwork\Kalkan\Http\Actions;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Mitwork\Kalkan\CacheDocument;
use Mitwork\Kalkan\Enums\AuthType;
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
        $uniqueId = null;

        if (config('kalkan.uid') === 'uuid') {
            $uniqueId = Str::uuid()->toString();
        } elseif (config('kalkan.uid') === 'hrtime') {
            $uniqueId = hrtime(true);
        }

        $id = $request->input('id', $uniqueId);

        $attributes = $request->validated();

        if (! isset($attributes['auth'])) {

            if (config('kalkan.options.auth.type') === AuthType::BEARER->value) {

                $token = config('kalkan.options.auth.token');

                if ($token === '') {
                    $token = Str::random(32);
                }

                $attributes['auth'] = [
                    'type' => AuthType::BEARER->value,
                    'token' => $token,
                ];
            } else {
                $attributes['auth'] = [
                    'type' => AuthType::NONE->value,
                    'token' => '',
                ];
            }
        }

        $document = new CacheDocument($attributes);

        if (! $this->documentService->addDocument($id, $document->attributes())) {
            return response()->json([
                'message' => __('kalkan::messages.unable_to_save_document'),
            ], 500);
        }

        DocumentSaved::dispatch($id, $document->attributes());

        $link = $this->generateSignedLink(config('kalkan.actions.generate-service-link'), ['id' => $id]);
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
