<?php

namespace Mitwork\Kalkan\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\URL;
use Mitwork\Kalkan\Services\DocumentService;
use Mitwork\Kalkan\Services\QrCodeGenerationService;

class TestController extends \Illuminate\Routing\Controller
{
    public function __construct(
        public DocumentService $documentService,
        public QrCodeGenerationService $qrCodeGenerationService,
    ) {
        //
    }

    /**
     * Шаг 1 - отправка документа, для последующей работы
     */
    public function prepareDocument(Request $request): JsonResponse
    {
        $request->validate([
            'name' => ['required'],
            'content' => ['required'],
            'type' => ['required'],
        ]);

        $id = $request->get('id');

        if (! $request->has('id')) {
            $id = time();
        }

        Cache::add($id, [
            'name' => $request->get('name'),
            'content' => $request->get('content'),
            'type' => $request->get('type'),
        ],
            config('kalkan.options.ttl')
        );

        return response()->json([
            'id' => $id,
        ]);

    }

    /**
     * Шаг 2 - Генерация QR-кода
     */
    public function generateQrCode(Request $request): JsonResponse
    {
        $request->validate([
            'id' => ['required'],
        ]);

        $link = URL::temporarySignedRoute(
            'generate-link',
            config('kalkan.options.ttl'),
            [
                'id' => $request->get('id'),
            ]
        );

        $result = $this->qrCodeGenerationService->generate($link);

        return response()->json([
            'image' => $result->getDataUri(),
            'link' => $link,
        ]);
    }

    /**
     * Шаг 3 - генерация ссылки
     */
    public function generateLink(Request $request): JsonResponse
    {
        $request->validate([
            'id' => ['required'],
        ]);

        $link = URL::temporarySignedRoute(
            'prepare-content',
            config('kalkan.options.ttl'),
            [
                'id' => $request->get('id'),
            ]
        );

        $data = $this->documentService->prepareServiceData($link);

        return response()->json($data);
    }

    /**
     * Шаг 4 - генерация кросс-ссылок
     */
    public function generateCrossLink(Request $request): JsonResponse
    {
        $request->validate([
            'id' => ['required'],
        ]);

        $link = URL::temporarySignedRoute(
            'prepare-content',
            config('kalkan.options.ttl'),
            [
                'id' => $request->get('id'),
            ]
        );

        return response()->json([
            'person' => sprintf(config('kalkan.links.person'), urlencode($link)),
            'legal' => sprintf(config('kalkan.links.legal'), urlencode($link))]
        );
    }

    /**
     * Шаг 5 - работа с контентом документа
     */
    public function prepareContent(Request $request): JsonResponse
    {
        $request->validate([
            'id' => ['required'],
        ]);

        $data = Cache::get($request->get('id'));

        // Отправка исходных данных

        if ($request->isMethod('GET')) {

            if ($data['type'] === 'xml') {
                $this->documentService->addXmlDocument($request->get('id'), $data['name'], $data['content']);

                return response()->json($this->documentService->getXmlDocuments());
            } else {
                $this->documentService->addCmsDocument($request->get('id'), $data['name'], $data['content']);

                return response()->json($this->documentService->getCmsDocuments());
            }

        }

        // Обработка подписанных данных

        if (request()->isMethod('PUT')) {

        }

        return response()->json([]);

    }
}
