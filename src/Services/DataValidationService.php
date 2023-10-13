<?php

namespace Mitwork\Kalkan\Services;

use DOMDocument;
use Mitwork\Kalkan\Exceptions\IncorrectBase64DataException;
use Mitwork\Kalkan\Exceptions\IncorrectJsonDataException;
use Mitwork\Kalkan\Exceptions\IncorrectXmlDataException;

class DataValidationService extends BaseService
{
    /**
     * Проверка XML-строки
     *
     * @param  string  $input Входная строка
     * @param  string  $version Версия
     * @param  string  $encoding Кодировка
     * @return bool Результат
     */
    public function validateXml(string $input, string $version = '1.0', string $encoding = 'utf-8', bool $throw = false): bool
    {
        if (trim($input) == '') {
            return false;
        }

        libxml_use_internal_errors(true);

        $doc = new DOMDocument($version, $encoding);
        $doc->loadXML($input);

        $errors = libxml_get_errors();
        libxml_clear_errors();

        if (count($errors) > 0) {

            $this->setError($errors[0]->message);

            if ($throw) {
                throw IncorrectXmlDataException::create($this->error);
            }
        }

        return empty($errors);
    }

    /**
     * Проверка декодирования JSON-строки
     *
     * @param  string  $input Входная строка
     * @param  bool  $associative Ассоциативные массив
     * @return bool Результат
     */
    public function validateJson(string $input, bool $associative = true, bool $throw = false): bool
    {
        if (! json_decode($input, $associative)) {

            if ($throw) {
                throw IncorrectJsonDataException::create();
            }

            return false;
        }

        return true;
    }

    /**
     * Проверка декодирования Base64-строки
     *
     * @param  string  $input Входная строка
     * @return bool Результат
     */
    public function validateBase64(string $input, bool $throw = false): bool
    {
        if (! preg_match('/^[a-zA-Z0-9\/\r\n+]*={0,2}$/', $input) || ! base64_decode($input)) {

            if ($throw) {
                throw IncorrectBase64DataException::create();
            }

            return false;
        }

        return true;
    }
}
