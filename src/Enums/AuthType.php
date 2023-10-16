<?php

namespace Mitwork\Kalkan\Enums;

enum AuthType: string
{
    case NONE = 'None';
    case BEARER = 'Bearer';

    case EDS = 'Eds';
}
