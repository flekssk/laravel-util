<?php

declare(strict_types=1);

namespace FKS\OutputFormatters\Formatters;

use Exception;
use Illuminate\Http\Response;
use FKS\OutputFormatters\Contracts\FormatterInterface;
use FKS\OutputFormatters\Enums\OutputFormatEnum;

class HtmlFormatter implements FormatterInterface
{
    public function toResponse(mixed $content, array $errors = []): Response
    {
        if (is_string($content)) {
            $response = response($content);

            $response->header('Errors', json_encode($errors));
            $response->header('Content-Format', OutputFormatEnum::HTML->value);

            return $response;
        }

        throw new Exception('Invalid content type');
    }
}
