<?php

namespace Mitwork\Kalkan\Http\Actions;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Mitwork\Kalkan\CacheDocument;
use Mitwork\Kalkan\CacheRequest;
use Mitwork\Kalkan\Enums\AuthType;
use Mitwork\Kalkan\Events\DocumentSaved;
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
     * Шаг 2 - формирование сервисного запроса
     *
     * В данном примере содержимое документа сохраняется
     * только в кэш, в реально жизни это может быть база
     * или шина данных.
     *
     * Последующие запросы используют этот идентификатор
     * для запроса и работы с данными.
     */
    public function store(PrepareServiceRequest $request): JsonResponse
    {
        $id = hrtime(true);
        $attributes = $request->validated();

        if (! isset($attributes['auth']) || ! isset($attributes['auth']['type'])) {

            if (config('kalkan.options.auth.type') === AuthType::BEARER->value) {

                if ($token = request()->bearerToken()) {
                    $attributes['auth'] = [
                        'type' => AuthType::BEARER->value,
                        'token' => $token,
                    ];
                } else {
                    $attributes['auth'] = [
                        'type' => AuthType::BEARER->value,
                        'token' => Str::random(32),
                    ];
                }

            } else {
                $attributes['auth'] = [
                    'type' => AuthType::NONE->value,
                    'token' => '',
                ];
            }

        } elseif ($attributes['auth']['type'] === AuthType::BEARER->value && empty($attributes['auth']['token'])) {
            $attributes['auth'] = [
                'type' => AuthType::BEARER->value,
                'token' => Str::random(32),
            ];
        } elseif ($attributes['auth']['type'] === AuthType::NONE->value) {
            $attributes['auth'] = [
                'type' => AuthType::NONE->value,
                'token' => '',
            ];
        }

        $serviceRequest = new CacheRequest(...$attributes);

        $files = $serviceRequest->files;

        foreach ($files as &$file) {

            if (! isset($file['id'])) {

                $file = new CacheDocument(...$file);

                $file['id'] = hrtime(true);

                if (! $this->documentService->add($file['id'], $file->toArray())) {
                    return response()->json([
                        'message' => __('kalkan::messages.unable_to_save_document'),
                    ], 500);
                }

                DocumentSaved::dispatch($file['id'], $file->toArray());
            }
        }

        if (! $this->requestService->add($id, $serviceRequest->toArray())) {

            return response()->json([
                'message' => __('kalkan::messages.unable_to_save_request'),
            ], 500);
        }

        RequestSaved::dispatch($id, $serviceRequest->toArray());

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
