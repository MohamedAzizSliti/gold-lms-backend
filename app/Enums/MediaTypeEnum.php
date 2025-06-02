<?php

namespace App\Enums;

enum MediaTypeEnum: string
{
    case IMAGE = 'image';
    case AUDIO = 'audio';
    case VIDEO = 'video';
    case DOCUMENT = 'document';
}
