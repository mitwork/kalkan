<?php

namespace Mitwork\Kalkan\Http\Actions;

use Illuminate\Http\JsonResponse;
use Mitwork\Kalkan\Http\Requests\FetchDocumentRequest;
use Mitwork\Kalkan\Services\QrCodeGenerationService;

class GenerateQrCode extends BaseAction
{
    public function __construct(
        public QrCodeGenerationService $qrCodeGenerationService
    ) {
    }

    /**
     * Шаг 1.1 - Генерация QR-кода
     *
     * После получения ID документа из прошлого шага
     * необходимо получить QR-код и ссылку для того
     * чтобы можно было отобразить ее в интерфейсе.
     */
    public function generate(FetchDocumentRequest $request): JsonResponse
    {
        $id = $request->input('id');
        $link = $this->generateSignedLink(config('kalkan.actions.generate-service-link'), ['id' => $id]);
        $result = $this->qrCodeGenerationService->generate($link);

        return response()->json([
            'uri' => $result->getDataUri(),
            'raw' => base64_encode($result->getString()),
        ]);
    }
}
