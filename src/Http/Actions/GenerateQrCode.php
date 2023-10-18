<?php

namespace Mitwork\Kalkan\Http\Actions;

use Illuminate\Http\JsonResponse;
use Mitwork\Kalkan\Http\Requests\FetchRequestRequest;
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
    public function generate(FetchRequestRequest $request): JsonResponse
    {
        $id = $request->input('id');

        $link = $this->generateTemporaryLink(config('kalkan.actions.prepare-content'), ['id' => $id]);

        $data = $this->qrCodeGenerationService->generate($link);

        return response()->json([
            'uri' => $data->getDataUri(),
            'raw' => base64_encode($data->getString()),
        ]);
    }
}
