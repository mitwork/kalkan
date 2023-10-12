<?php

namespace Mitwork\Kalkan\Contracts;

interface ValidationService
{
    /**
     * Проверка подписанной XML-строки
     *
     * @param  string  $xml Подписанный XML
     * @param  bool  $verifyCrl Проверка по CRL
     * @param  bool  $verifyOcsp Проверка OCSP
     * @param  bool  $raw Возврат результата, либо ответа
     * @return bool|array Результат, либо ответ
     */
    public function verifyXml(string $xml, bool $verifyCrl = true, bool $verifyOcsp = true, bool $raw = false): bool|array;

    /**
     * Проверка подписанных CMS-данных
     *
     * @param  string  $cms Подписанные данные
     * @param  string  $data Исходные данные
     * @param  bool  $verifyCrl Проверка по CRL
     * @param  bool  $verifyOcsp Проверка OCSP
     * @param  bool  $raw Возврат результата, либо ответа
     * @return bool|array Результат, либо ответ
     */
    public function verifyCms(string $cms, string $data, bool $verifyCrl = true, bool $verifyOcsp = true, bool $raw = false): bool|array;
}
