<?php

declare(strict_types=1);

namespace FKS\Logging\Formatters;

use Monolog\Formatter\JsonFormatter;
use Monolog\LogRecord;

class LogJsonFormatter extends JsonFormatter
{
    public function __construct(
        int $batchMode = self::BATCH_MODE_JSON,
        bool $appendNewline = true,
        bool $ignoreEmptyContextAndExtra = false,
        bool $includeStacktraces = false,
        private readonly bool $prependNewline = true,
    ) {
        parent::__construct($batchMode, $appendNewline, $ignoreEmptyContextAndExtra, $includeStacktraces);
    }

    public function format(array|LogRecord $record): string
    {
        return ($this->prependNewline ? "\n" : '') . parent::format($record);
    }
}
