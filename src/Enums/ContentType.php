<?php

namespace Mitwork\Kalkan\Enums;

enum ContentType: string
{
    case XML = 'xml';
    case CMS = 'cms';
    case TEXT_XML = 'text/xml';
    case PDF = 'application/pdf';
}
