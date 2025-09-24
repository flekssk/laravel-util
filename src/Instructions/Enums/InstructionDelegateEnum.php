<?php

declare(strict_types=1);

namespace FKS\Instructions\Enums;

enum InstructionDelegateEnum: string
{
    case USER = 'user';
    case SERVER = 'server';
    case ADMIN = 'admin';
}
