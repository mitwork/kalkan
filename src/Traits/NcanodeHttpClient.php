<?php

namespace Mitwork\Kalkan\Traits;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Mitwork\Kalkan\Exceptions\NcanodeStatusException;
use Mitwork\Kalkan\Exceptions\NcanodeUnavailableException;

trait NcanodeHttpClient
{
    /**
     * @var Client|null
     */
    protected ?Client $client = null;

    /**
     * @var array|string[]
     */
    protected array $headers = [
        'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_3) Chrome/77.0.3865.35 Safari/537.36',
        'Accept' => 'application/json',
        'Accept-Language' => 'ru-RU,ru;q=0.9,en-US;q=0.8,en;q=0.7,bg;q=0.6',
        'Content-Type' => 'application/json'
    ];

    /**
     * Send HTTP request
     *
     * @param string $url
     * @param string|array|null $body
     * @param array $options
     * @return array
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

            if (!$message || !isset($message['status']) || $message['status'] !== 200) {
                throw NcanodeStatusException::create(500, $response);
            }

            return $message;
        }

        throw NcanodeStatusException::create($request->getStatusCode(), $request->getBody()->getContents());
    }

    /**
     * @param array $options
     * @param string $host
     * @return void
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
     * Send POST request
     *
     * @param string $url
     * @param string|array $body
     * @return \Psr\Http\Message\ResponseInterface
     * @throws GuzzleException
     */
    private function post(string $url, string|array $body): \Psr\Http\Message\ResponseInterface
    {
        return $this->client->post($url, ['headers' => $this->headers, 'body' => $body]);
    }

    /**
     * Send GET request
     *
     * @param string $url
     * @return \Psr\Http\Message\ResponseInterface
     * @throws GuzzleException
     */
    private function get(string $url): \Psr\Http\Message\ResponseInterface
    {
        return $this->client->get($url, ['headers' => $this->headers]);
    }
}
