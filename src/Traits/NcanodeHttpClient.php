<?php

namespace Mitwork\Kalkan\Traits;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Mitwork\Kalkan\Exceptions\NcanodeStatusException;
use Mitwork\Kalkan\Exceptions\NcanodeUnavailableException;

trait NcanodeHttpClient
{
    protected ?Client $client = null;

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
     * @param  string  $url Ссылка
     * @param  string|array|null  $body Тело запроса
     * @param  array  $options Параметры
     * @return array Результат
     */
    public function request(string $url, string|array $body = null, array $options = []): array
    {

        $this->init($options);

        try {

            if ($body) {
                $request = $this->post($url, $body);
            } else {
                $request = $this->get($url);
            }

        } catch (GuzzleException $ex) {
            throw NcanodeUnavailableException::create($ex->getMessage());
        }

        if ($request->getStatusCode() === 200) {

            $response = $request->getBody()->getContents();
            $message = json_decode($response, true);

            if (! $message || ! isset($message['status']) || $message['status'] !== 200) {
                throw NcanodeStatusException::create($request->getStatusCode(), $response);
            }

            return $message;
        }

        throw NcanodeStatusException::create($request->getStatusCode(), $request->getBody()->getContents());
    }

    /**
     * Инициализация
     *
     * @param  array  $options Параметры
     * @param  string  $host Хост по-умолчанию
     */
    private function init(array $options = [], string $host = 'http://localhost:14579'): void
    {
        $host = function_exists('config') ? config('kalkan.ncanode.host') : $host;

        $defaults = [
            'base_uri' => $host,
            'timeout' => 120,
            'http_errors' => false,
        ];

        $this->headers['Host'] = str_replace(['http://', 'https://'], '', $host);

        $this->client = new Client($defaults + $options);
    }

    /**
     * Отправка POST-запроса
     *
     * @param  string  $url Ссылка
     * @param  string|array  $body Тело запроса
     * @return \Psr\Http\Message\ResponseInterface Результат
     *
     * @throws GuzzleException
     */
    private function post(string $url, string|array $body): \Psr\Http\Message\ResponseInterface
    {
        return $this->client->post($url, ['headers' => $this->headers, 'body' => $body]);
    }

    /**
     * Отправка GET-запроса
     *
     * @param  string  $url Ссылка
     * @return \Psr\Http\Message\ResponseInterface Результат
     *
     * @throws GuzzleException
     */
    private function get(string $url): \Psr\Http\Message\ResponseInterface
    {
        return $this->client->get($url, ['headers' => $this->headers]);
    }
}
