<?php

namespace Mitwork\Kalkan\Http\Actions;

use Illuminate\Http\JsonResponse;
use Mitwork\Kalkan\Services\CacheDocumentService;

class CheckDocument extends BaseAction
{
    public function __construct(
        public CacheDocumentService $documentService
    ) {}

    /**
     * Шаг 5 - Проверка статуса подписания документа
     *
     * @param  int|string  $id  Идентификатор
     */
    public function check(int|string $id): JsonResponse
    {
        $status = $this->documentService->check($id);

        if (is_null($status)) {
            return response()->json([
                'error' => __('kalkan::messages.document_not_found'),
                'status' => $status,
            ], 404);
        }

        if ($status === false) {
            return response()->json(['status' => false]);
        }

        return response()->json($status);
    }
}
