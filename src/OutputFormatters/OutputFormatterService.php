<?php

declare(strict_types=1);

namespace FKS\OutputFormatters;

use Exception;
use Illuminate\Http\Response;
use FKS\OutputFormatters\Contracts\FormatterInterface;
use FKS\OutputFormatters\Enums\OutputFormatEnum;
use FKS\OutputFormatters\Formatters\DownloadFileFormatter;
use FKS\OutputFormatters\Formatters\HtmlFormatter;

class OutputFormatterService
{
    public function toResponse(mixed $content, array $errors, OutputFormatEnum $format): Response
    {
        return $this->formatter($format)->toResponse($content, $errors);
    }

    private function formatter(OutputFormatEnum $format): FormatterInterface
    {
        return match ($format) {
            OutputFormatEnum::HTML => app(HtmlFormatter::class),
            OutputFormatEnum::DOWNLOAD_FILE => app(DownloadFileFormatter::class),
            OutputFormatEnum::JSON => throw new Exception('To be implemented'),
        };
    }
}
