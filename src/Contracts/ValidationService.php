<?php

namespace Mitwork\Kalkan\Contracts;

interface ValidationService
{
    /**
     * @param string $xml
     * @param bool $verifyCrl
     * @param bool $verifyOcsp
     * @param bool $raw
     * @return bool|array
     */
    public function verifyXml(string $xml, bool $verifyCrl = true, bool $verifyOcsp = true, bool $raw = false): bool|array;

    /**
     * @param string $cms
     * @param string $data
     * @param bool $verifyCrl
     * @param bool $verifyOcsp
     * @param bool $raw
     * @return bool|array
     */
    public function verifyCms(string $cms, string $data, bool $verifyCrl = true, bool $verifyOcsp = true, bool $raw = false): bool|array;
}
