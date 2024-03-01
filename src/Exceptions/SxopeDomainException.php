<?php

declare(strict_types=1);

namespace FKS\Exceptions;

use DomainException;
use FKS\Contracts\FKSExceptionInterface;
use Throwable;

class FKSDomainException extends DomainException implements FKSExceptionInterface
{
    public function __construct(string $message = '', int $code = 422, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
