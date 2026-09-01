<?php

namespace Mitwork\Kalkan\Enums;

enum TsaPolicy: string
{
    /** @deprecated  */
    case TSA_GOST_POLICY = 'TSA_GOST_POLICY';

    /** @deprecated  */
    case TSA_GOSTGT_POLICY = 'TSA_GOSTGT_POLICY';
    case TSA_GOST2015_POLICY = 'TSA_GOST2015_POLICY';
}
