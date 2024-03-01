<?php

declare(strict_types=1);

namespace FKS\Exceptions\PdfGenerator;

use FKS\Contracts\FKSExceptionInterface;

class NavigatePdfUrlException extends \Exception implements FKSExceptionInterface
{
    public static function becausePageIsNotLoaded(string $url): void
    {
        throw new self("Page is not loaded: $url");
    }
}
