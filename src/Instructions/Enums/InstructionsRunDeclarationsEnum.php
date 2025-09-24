<?php

declare(strict_types=1);

namespace FKS\Instructions\Enums;

enum InstructionsRunDeclarationsEnum: string
{
    case IMMEDIATELY = 'immediately';
    case AFTER_SUCCESS = 'after_success';
    case IN_QUEUE = 'in_queue';
    case ONCE = 'once';
}
