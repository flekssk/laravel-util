<?php

declare(strict_types=1);

namespace FKS\Logging\Processors;

use Illuminate\Foundation\Application;
use Monolog\Handler\WebRequestRecognizerTrait;
use Monolog\LogRecord as BaseLogRecord;
use Monolog\Processor\ProcessorInterface;
use FKS\Logging\LogRecord;

/**
 * @see https://cloud.google.com/logging/docs/structured-logging
 * @see https://cloud.google.com/logging/docs/reference/v2/rest/v2/LogEntry#HttpRequest
 */
class GkeWebProcessor implements ProcessorInterface
{
    use WebRequestRecognizerTrait;

    public function __invoke(array|BaseLogRecord|LogRecord $record): LogRecord|array
    {
        $httpRequest = [];

        if (!$this->isWebRequest()) {
            return $record;
        }

        if ($method = $_SERVER['REQUEST_METHOD'] ?? null) {
            $httpRequest['requestMethod'] = $method;
        }
        if ($uri = $_SERVER['REQUEST_URI'] ?? null) {
            $httpRequest['requestUrl'] = $uri;
        }

        if (version_compare(Application::VERSION, '10.0', '>')) {
            return new LogRecord(
                $record->datetime,
                $record->channel,
                $record->level,
                $record->message,
                $record->context,
                $record->extra,
                $record->formatted,
                $record->labels,
                $record->trace,
                $httpRequest
            );
        }

        $record['httpRequest'] = $httpRequest;

        return $record;
    }
}
