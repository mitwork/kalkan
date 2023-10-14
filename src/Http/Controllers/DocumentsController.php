<?php

namespace Mitwork\Kalkan\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\URL;
use Mitwork\Kalkan\Enums\ContentType;
use Mitwork\Kalkan\Events\DocumentRejected;
use Mitwork\Kalkan\Events\DocumentSaved;
use Mitwork\Kalkan\Events\DocumentSigned;
use Mitwork\Kalkan\Http\Requests\FetchDocumentRequest;
use Mitwork\Kalkan\Http\Requests\ProcessDocumentRequest;
use Mitwork\Kalkan\Http\Requests\StoreDocumentRequest;
use Mitwork\Kalkan\Services\CacheDocumentService;
use Mitwork\Kalkan\Services\IntegrationService;
use Mitwork\Kalkan\Services\KalkanValidationService;
use Mitwork\Kalkan\Services\QrCodeGenerationService;

class DocumentsController extends \Illuminate\Routing\Controller
{
    public function __construct(
        public IntegrationService $integrationService,
        public QrCodeGenerationService $qrCodeGenerationService,
        public KalkanValidationService $validationService,
        public CacheDocumentService $documentService,
    ) {
        //
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
        $id = $request->input('id', hrtime(true));

        if (! $this->documentService->addDocument($id, $request->validated())) {
            return response()->json([
                'message' => __('kalkan::messages.unable_to_save_document'),
            ], 500);
        }

        DocumentSaved::dispatch($id);

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
    public function generateQrCode(FetchDocumentRequest $request): JsonResponse
    {
        $link = $this->generateSignedLink('generate-link', ['id' => $request->input('id')]);
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
    public function generateCrossLink(FetchDocumentRequest $request): JsonResponse
    {
        $link = $this->generateSignedLink('prepare-content', ['id' => $request->get('id')]);

        return response()->json([
            'person' => sprintf(config('kalkan.links.mobile'), urlencode($link)),
            'legal' => sprintf(config('kalkan.links.business'), urlencode($link))]
        );
    }

    /**
     * Шаг 2 - генерация сервисных данных
     */
    public function generateLink(FetchDocumentRequest $request): JsonResponse
    {
        $link = $this->generateSignedLink('prepare-content', ['id' => $request->input('id')]);
        $data = $this->integrationService->prepareServiceData($link);

        return response()->json($data);
    }

    /**
     * Шаг 3 - работа с контентом документа
     *
     * Возврат содержимого документов
     */
    public function prepareContent(FetchDocumentRequest $request): JsonResponse
    {
        $id = $request->input('id');

        $document = $this->documentService->getDocument($id);

        if (! $document) {
            return response()->json([
                'message' => __('kalkan::messages.unable_to_get_document'),
            ], 500);
        }

        if (! isset($document['meta'])) {
            $document['meta'] = [];
        }

        if ($document['type'] === ContentType::XML->value) {
            $this->integrationService->addXmlDocument($id, $document['name'], $document['content'], $document['meta']);
            $response = $this->integrationService->getXmlDocuments($id);

        } else {
            $this->integrationService->addCmsDocument($id, $document['name'], $document['content'], $document['meta']);
            $response = $this->integrationService->getCmsDocuments($id);

        }

        return response()->json($response);
    }

    /**
     * Шаг 4 - получение и обработка подписанных данных
     */
    public function processContent(ProcessDocumentRequest $request): JsonResponse
    {
        $id = $request->input('id');

        $document = $this->documentService->getDocument($id);
        $documents = $request->input('documentsToSign');

        foreach ($documents as $signedDocument) {

            if ($document['type'] === ContentType::XML->value) {
                $signature = $signedDocument['documentXml'];
                $result = $this->validationService->verifyXml($signature);
            } else {
                $signature = $signedDocument['documentCms'];
                $result = $this->validationService->verifyCms($signature, $document['content']);
            }

            if ($result !== true) {
                DocumentRejected::dispatch($id, $this->validationService->getError());

                return response()->json(['error' => $this->validationService->getError()], 422);
            }

            if (! $this->documentService->processDocument($id)) {
                DocumentRejected::dispatch($id, __('kalkan::messages.unable_to_process_document'));

                return response()->json(['error' => __('kalkan::messages.unable_to_process_document')], 500);
            }

            DocumentSigned::dispatch($id, $document['content'], $signature);
        }

        return response()->json([]);
    }

    /**
     * Шаг 5 - Проверка статуса подписания документа
     *
     * @param  int|string  $id Идентификатор
     */
    public function check(int|string $id): JsonResponse
    {
        $status = $this->documentService->checkDocument($id);

        if (is_null($status)) {
            return response()->json(['error' => __('kalkan::messages.document_not_found'), 'status' => $status], 404);
        }

        return response()->json(['status' => $status]);
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
