<?php

namespace Mitwork\Kalkan\Http\Actions;

use Illuminate\Http\JsonResponse;
use Mitwork\Kalkan\Services\CacheRequestService;

class CheckRequest extends BaseAction
{
    public function __construct(
        public CacheRequestService $requestService
    ) {}

    /**
     * Шаг 5 - Проверка статуса обработанной заявки
     *
     * @param  int|string  $id  Идентификатор
     */
    public function check(int|string $id): JsonResponse
    {
        $status = $this->requestService->check($id);

        if (is_null($status)) {
            return response()->json([
                'error' => __('kalkan::messages.request_not_found'),
                'status' => $status,
            ], 404);
        }

        if ($status === false) {
            return response()->json(['status' => false]);
        }

        return response()->json($status);
    }
}
