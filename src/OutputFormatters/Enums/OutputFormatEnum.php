<?php

declare(strict_types=1);

namespace FKS\OutputFormatters\Enums;

enum OutputFormatEnum: string
{
    case JSON = 'json';
    case HTML = 'html';
    case DOWNLOAD_FILE = 'pdf_url';
}
