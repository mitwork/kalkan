<?php

namespace Mitwork\Kalkan\Enums;

enum DocumentStatus: string
{
    case CREATED = 'created';
    case REQUESTED = 'requested';
    case REJECTED = 'rejected';
    case SIGNED = 'signed';
    case PROCESSED = 'processed';
}
