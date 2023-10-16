<?php

namespace Mitwork\Kalkan\Enums;

enum SignatureType: string
{
    case XML = 'XML';
    case CMS_WITH_DATA = 'CMS_WITH_DATA';

    case CMS_SIGN_ONLY = 'CMS_SIGN_ONLY';

    public function getSignatureMethod(): string
    {
        return match($this)
        {
            self::XML => 'XML',
            self::CMS_WITH_DATA => 'CMS_WITH_DATA',
            self::CMS_SIGN_ONLY => 'CMS_SIGN_ONLY',
        };
    }
}
