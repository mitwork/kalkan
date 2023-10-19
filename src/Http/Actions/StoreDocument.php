<?php

namespace Mitwork\Kalkan\Http\Actions;

use Illuminate\Http\JsonResponse;
use Mitwork\Kalkan\CacheDocument;
use Mitwork\Kalkan\Events\DocumentSaved;
use Mitwork\Kalkan\Http\Requests\StoreDocumentRequest;
use Mitwork\Kalkan\Services\CacheDocumentService;

class StoreDocument extends BaseAction
{
    public function __construct(
        public CacheDocumentService $documentService,
    ) {

    }

    /**
     * Шаг 1 - отправка файла для последующей работы
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
        $id = $request->input('id', hrtime(true));

        $attributes = $request->validated();

        $document = new CacheDocument(...$attributes);

        if (! $this->documentService->add($id, $document->toArray())) {

            return response()->json([
                'message' => __('kalkan::messages.unable_to_save_document'),
            ], 500);
        }

        DocumentSaved::dispatch($id, $document->toArray());

        return response()->json([
            'id' => $id,
        ]);
    }
}
