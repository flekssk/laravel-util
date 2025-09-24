<?php

declare(strict_types=1);

namespace FKS\OutputFormatters\Formatters;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;
use FKS\OutputFormatters\Contracts\FormatterInterface;

class DownloadFileFormatter implements FormatterInterface
{
    public function toResponse(mixed $content, array $errors = []): Response
    {
        $filename = basename(parse_url($content, PHP_URL_PATH));

        $response = Http::get($content);

        if ($response->successful()) {
            return response($response->body())
                ->header('Content-Type', $response->header('Content-Type') ?? 'application/octet-stream')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
        }

        abort(404, 'File not found');
    }
}
