<?php

namespace Mitwork\Kalkan\Contracts;

use Mitwork\Kalkan\Exceptions\IncorrectXmlException;
use Mitwork\Kalkan\Exceptions\NcanodeUnavailableException;

interface SignatureService
{
    /**
     * Sign XML data
     *
     * @param string $xml Данные для подписания
     * @param string $key Ключ в формате base64
     * @param string $password Пароль ключа
     * @param string|null $alias Псевдоним (алиас) (необязательно)
     * @param bool $clearSignatures Очистка подписи
     * @param bool $trimXml Очистка XML
     * @param bool $raw
     * @return string|array
     * @throws IncorrectXmlException
     * @throws NcanodeUnavailableException
     */
    public function signXml(string $xml, string $key, string $password, string $alias = null, bool $clearSignatures = false, bool $trimXml = false, bool $raw = false): string|array;

    /**
     * Sign CMS data
     *
     * @param string $data Данные для подписания
     * @param string $key Ключ в формате base64
     * @param string $password Пароль ключа
     * @param string|null $alias Псевдоним (алиас) (необязательно)
     * @param bool $withTsp Метка времени
     * @param string $tsaPolicy Политика TSP-запроса
     * @param bool $detached Открепленная подпись
     * @param bool $raw
     * @return string|array
     * @throws NcanodeUnavailableException
     */
    public function signCms(string $data, string $key, string $password, string $alias = null, bool $withTsp = true, string $tsaPolicy = 'TSA_GOST_POLICY', bool $detached = false, bool $raw = false): string|array;

}
