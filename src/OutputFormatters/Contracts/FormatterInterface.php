<?php

declare(strict_types=1);

namespace FKS\OutputFormatters\Contracts;


use Illuminate\Http\Response;

interface FormatterInterface
{
    public function toResponse(mixed $content, array $errors = []): Response;
}
