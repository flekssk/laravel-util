<?php

namespace FKS\Logging\Processors;

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Monolog\Handler\WebRequestRecognizerTrait;
use Monolog\LogRecord as BaseLogRecord;
use Monolog\Processor\ProcessorInterface;
use FKS\Contracts\FKSLogger;
use FKS\Logging\LogRecord;

/**
 * @see https://cloud.google.com/logging/docs/structured-logging
 * @see https://cloud.google.com/logging/docs/reference/v2/rest/v2/LogEntry#HttpRequest
 */
class GkeGeneralInfoProcessor implements ProcessorInterface
{
    use WebRequestRecognizerTrait;

    public function __construct(protected FKSLogger $FKSLogger) {}

    public function getLabels(array|BaseLogRecord $record): array
    {
        $labels = [];
        foreach ($record['context']['labels'] ?? [] as $name => $value) {
            $labels[$name] = $value;
        }

        if ($this->isWebRequest()) {
            $labels['route_name'] = Route::getCurrentRoute()?->getName();
        } else {
            $labels['command'] = data_get(request()?->server(), 'argv.1');
            $labels['absolute_command'] = implode(' ', request()?->server('argv', []) ?? []);
        }

        return $labels;
    }

    public function __invoke(array|BaseLogRecord $record): LogRecord|array
    {
        if (version_compare(Application::VERSION, '10.0', '>')) {
            $context = $record->context;
            unset($context['labels']);

            return new LogRecord(
                $record->datetime,
                $record->channel,
                $record->level,
                $record->message,
                $context,
                $record->extra,
                $record->formatted,
                $this->getLabels($record),
                $this->FKSLogger->getTraceId()
            );
        }

        $record['logging.googleapis.com/labels'] = $this->getLabels($record);
        $record['logging.googleapis.com/trace'] = $this->FKSLogger->getTraceId();

        $record['time'] = $record['datetime'];
        $record['severity'] = $record['level_name'];

        unset($record['context']['labels'], $record['datetime'], $record['level_name'], $record['channel']);

        return $record;
    }
}
