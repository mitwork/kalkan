<?php

namespace Mitwork\Kalkan\Contracts;

use Mitwork\Kalkan\Exceptions\KalkanValidationException;
use Mitwork\Kalkan\Exceptions\NcanodeStatusException;
use Mitwork\Kalkan\Exceptions\NcanodeUnavailableException;

interface ValidationService
{
    /**
     * Проверка подписанной XML-строки
     *
     * @param  string  $xml  Подписанный XML
     * @param  bool  $verifyCrl  Проверка по CRL
     * @param  bool  $verifyOcsp  Проверка OCSP
     * @param  bool  $raw  Возврат результата, либо ответа
     * @param  bool  $throw  Возврат исключения в случае ошибки
     * @return bool|array Результат, либо ответ
     *
     * @throws NcanodeUnavailableException
     * @throws NcanodeStatusException
     * @throws KalkanValidationException
     */
    public function verifyXml(string $xml, bool $verifyCrl = true, bool $verifyOcsp = true, bool $raw = false, bool $throw = false): bool|array;

    /**
     * Проверка подписанных CMS-данных
     *
     * @param  string  $cms  Подписанные данные
     * @param  string  $data  Исходные данные
     * @param  bool  $verifyCrl  Проверка по CRL
     * @param  bool  $verifyOcsp  Проверка OCSP
     * @param  bool  $raw  Возврат результата, либо ответа
     * @param  bool  $throw  Возврат исключения в случае ошибки
     * @return bool|array Результат, либо ответ
     *
     * @throws NcanodeUnavailableException
     * @throws NcanodeStatusException
     * @throws KalkanValidationException
     */
    public function verifyCms(string $cms, string $data, bool $verifyCrl = true, bool $verifyOcsp = true, bool $raw = false, bool $throw = false): bool|array;
}
