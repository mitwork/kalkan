<?php

namespace Mitwork\Kalkan\Http\Actions;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Mitwork\Kalkan\Enums\AuthType;
use Mitwork\Kalkan\Events\RequestSaved;
use Mitwork\Kalkan\Http\Requests\PrepareServiceRequest;
use Mitwork\Kalkan\Services\CacheDocumentService;
use Mitwork\Kalkan\Services\CacheRequestService;
use Mitwork\Kalkan\Services\IntegrationService;
use Mitwork\Kalkan\Services\KalkanValidationService;
use Mitwork\Kalkan\Services\QrCodeGenerationService;

class StoreRequest extends BaseAction
{
    public function __construct(
        public IntegrationService $integrationService,
        public QrCodeGenerationService $qrCodeGenerationService,
        public KalkanValidationService $validationService,
        public CacheDocumentService $documentService,
        public CacheRequestService $requestService,
    ) {

    }

    /**
     * Шаг 1 - отправка документов для последующей работы
     *
     * В данном примере содержимое документа сохраняется
     * только в кэш, в реально жизни это может быть база
     * данных или файловое/облачное хранилище.
     *
     * Последующие запросы используют этот идентификатор
     * для запроса и работы с данными.
     */
    public function store(PrepareServiceRequest $request): JsonResponse
    {
        $id = Str::uuid()->toString();
        $attributes = $request->validated();

        $files = $attributes['files'];

        foreach ($files as &$file) {

            if (! isset($file['id'])) {

                $file['id'] = hrtime();

                if (! $this->documentService->add($file['id'], $file)) {
                    return response()->json([
                        'message' => __('kalkan::messages.unable_to_save_document'),
                    ], 500);
                }
            }
        }

        if (! isset($attributes['auth']) || ! isset($attributes['auth']['type'])) {

            $attributes['auth'] = [
                'type' => AuthType::NONE->value,
                'token' => '',
            ];
        }

        if (! $this->requestService->add($id, $attributes)) {
            return response()->json([
                'message' => __('kalkan::messages.unable_to_save_request'),
            ], 500);
        }

        RequestSaved::dispatch($id, $attributes);

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
            'expire' => time() + config('kalkan.ttl'),
        ]);
    }
}
