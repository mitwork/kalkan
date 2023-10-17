<?php

namespace Mitwork\Kalkan\Enums;

enum RequestStatus: string
{
    case CREATED = 'created';
    case PROGRESS = 'progress';
    case PROCESSED = 'processed';
    case REJECTED = 'rejected';
}
