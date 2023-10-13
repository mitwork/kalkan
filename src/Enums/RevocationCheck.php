<?php

namespace Mitwork\Kalkan\Enums;

enum RevocationCheck: string
{
    case OCSP = 'OCSP';
    case CRL = 'CRL';
}
