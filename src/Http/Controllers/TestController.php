<?php

namespace Mitwork\Kalkan\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\URL;
use Mitwork\Kalkan\Services\DocumentService;
use Mitwork\Kalkan\Services\KalkanValidationService;
use Mitwork\Kalkan\Services\QrCodeGenerationService;

class TestController extends \Illuminate\Routing\Controller
{
    public function __construct(
        public DocumentService $documentService,
        public QrCodeGenerationService $qrCodeGenerationService,
        public KalkanValidationService $validationService,
    ) {
        //
    }

    /**
     * Шаг 1 - отправка документа для последующей работы
     *
     * В данном примере содержимое документа сохраняется
     * только в кэш, в реально жизни это может быть база
     * данных или файлоовое хранилище.
     *
     * Последующие запросы используют этот идентификатор
     * для запроса и работы с данными.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => ['required'],
            'content' => ['required'],
            'type' => ['required'],
        ]);

        $id = $request->get('id');

        if (! $request->has('id')) {
            $id = hrtime(true);
        }

        Cache::add($id, [
            'name' => $request->get('name'),
            'content' => $request->get('content'),
            'type' => $request->get('type'),
        ],
            config('kalkan.options.ttl')
        );

        $link = $this->generateSignedLink('generate-link', ['id' => $id]);
        $result = $this->qrCodeGenerationService->generate($link);

        return response()->json([
            'id' => $id,
            'url' => $link,
            'links' => [
                'qr' => [
                    'uri' => $result->getDataUri(),
                    //'raw' => $generateResult->getString(),
                ],
                'app' => [
                    'mobile' => sprintf(config('kalkan.links.mobile'), urlencode($link)),
                    'business' => sprintf(config('kalkan.links.business'), urlencode($link)),
                ],
            ],
        ]);

    }

    /**
     * Шаг 1.1 - Генерация QR-кода
     *
     * После получения ID документа из прошлого шага
     * необходимо получить QR-код и ссылку для того
     * чтобы можно было отобразить ее в интерфейсе.
     */
    public function generateQrCode(Request $request): JsonResponse
    {
        $request->validate([
            'id' => ['required'],
        ]);

        $link = $this->generateSignedLink('generate-link', ['id' => $request->get('id')]);
        $result = $this->qrCodeGenerationService->generate($link);

        return response()->json([
            'image' => $result->getDataUri(),
            'link' => $link,
        ]);
    }

    /**
     * Шаг 1.2 - генерация кросс-ссылок
     *
     * Данные ссылки генерируются для возможности
     * работы с подписанием через мобильное приложение
     * eGov Mobile или eGov business.
     */
    public function generateCrossLink(Request $request): JsonResponse
    {
        $request->validate([
            'id' => ['required'],
        ]);

        $link = $this->generateSignedLink('prepare-content', ['id' => $request->get('id')]);

        return response()->json([
            'person' => sprintf(config('kalkan.links.mobile'), urlencode($link)),
            'legal' => sprintf(config('kalkan.links.business'), urlencode($link))]
        );
    }

    /**
     * Шаг 2 - генерация сервисных данных
     */
    public function generateLink(Request $request): JsonResponse
    {
        $request->validate([
            'id' => ['required'],
        ]);

        $link = $this->generateSignedLink('prepare-content', ['id' => $request->get('id')]);
        $data = $this->documentService->prepareServiceData($link);

        return response()->json($data);
    }

    /**
     * Шаг 3 - работа с контентом документа
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

            $documents = $request->get('documentsToSign');
            $type = $data['type'];

            foreach ($documents as $document) {
                if ($type === 'xml') {
                    $result = $this->validationService->verifyXml($document['documentXml']);
                } else {
                    $result = $this->validationService->verifyCms($document['documentCms'], $data['content']);
                }

                if ($result !== true) {
                    return response()->json([], 422);
                }
            }
        }

        return response()->json([]);

    }

    /**
     * Генерация временных ссылок
     *
     * @param  string  $route Роут
     * @param  array  $params Параметры
     * @param  int  $ttl Время жизни
     * @return string Ссылка
     */
    private function generateSignedLink(string $route, array $params = [], int $ttl = 30): string
    {
        return URL::temporarySignedRoute($route, $ttl ?: config('kalkan.options.ttl'), $params);
    }
}
