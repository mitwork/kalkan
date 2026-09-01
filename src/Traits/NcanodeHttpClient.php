<?php

namespace Mitwork\Kalkan\Traits;

use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Client\HttpClientException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Mitwork\Kalkan\Exceptions\NcanodeStatusException;
use Mitwork\Kalkan\Exceptions\NcanodeUnavailableException;

trait NcanodeHttpClient
{
    protected ?PendingRequest $client = null;

    /**
     * @var array|string[]
     */
    protected array $headers = [
        'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_3) Chrome/77.0.3865.35 Safari/537.36',
        'Accept' => 'application/json',
        'Accept-Language' => 'ru-RU,ru;q=0.9,en-US;q=0.8,en;q=0.7,bg;q=0.6',
        'Content-Type' => 'application/json',
    ];

    /**
     * Отправка запроса
     *
     * @param  string  $url  Ссылка
     * @param  string|array<string, mixed>|null  $body  Тело запроса
     * @param  array<string, mixed>  $options  Параметры
     * @return array<string, mixed> Результат
     */
    public function request(string $url, string|array|null $body = null, array $options = [], bool $throw = true): array
    {

        $this->init($options);

        try {

            if ($body) {
                $request = $this->post($url, $body);
            } else {
                $request = $this->get($url);
            }

        } catch (HttpClientException|GuzzleException $ex) {
            throw NcanodeUnavailableException::create($ex->getMessage());
        }

        $response = $request->body();
        $message = json_decode($response, true);

        if ($request->status() === 200) {

            if ($throw) {
                if (! $message || ! isset($message['status']) || $message['status'] !== 200) {
                    throw NcanodeStatusException::create($request->status(), $response);
                }
            }

            return $message;
        }

        if ($throw) {
            throw NcanodeStatusException::create($request->status(), $response);
        }

        return $message;

    }

    /**
     * Инициализация
     *
     * @param  array<string, mixed>  $overrides  Параметры
     * @param  string  $host  Хост по-умолчанию
     */
    private function init(array $overrides = [], string $host = 'http://localhost:14579'): void
    {
        $host = config('kalkan.ncanode.host', $host);

        $defaults = [
            'timeout' => 120,
            'http_errors' => false,
        ];

        $components = parse_url($host);

        $this->headers['Host'] = $components['host'] ?? $host;

        if (isset($components['port'])) {
            $this->headers['Host'] .= ':'.$components['port'];
        }

        $options = $defaults + $overrides;

        $this->client = Http::baseUrl($host)->withOptions($options)->withHeaders($this->headers);
    }

    /**
     * Отправка POST-запроса
     *
     * @param  string  $url  Ссылка
     * @param  string|array<string, mixed>  $body  Тело запроса
     * @return Response Результат
     *
     * @throws HttpClientException
     * @throws GuzzleException
     */
    private function post(string $url, string|array $body): Response
    {
        if (is_string($body)) {
            return $this->client->withBody($body, $this->headers['Content-Type'])->post($url);
        }

        return $this->client->post($url, $body);
    }

    /**
     * Отправка GET-запроса
     *
     * @param  string  $url  Ссылка
     * @return Response Результат
     *
     * @throws HttpClientException
     * @throws GuzzleException
     */
    private function get(string $url): Response
    {
        return $this->client->get($url);
    }
}
