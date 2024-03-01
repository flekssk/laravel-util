<?php

declare(strict_types=1);

namespace FKS\Logging;

use Monolog\Level;

class LogRecord extends \Monolog\LogRecord
{
    public function __construct(
        \DateTimeImmutable $datetime,
        string $channel,
        Level $level,
        string $message,
        array $context = [],
        array $extra = [],
        mixed $formatted = null,
        public readonly array $labels = [],
        public readonly string $trace = '',
        public readonly ?array $httpRequest = null,
    ) {
        parent::__construct(
            $datetime,
            $channel,
            $level,
            $message,
            $context,
            $extra,
            $formatted
        );
    }

    public function toArray(): array
    {
        $data = [
            'message' => $this->message,
            'context' => $this->context,
            'level' => $this->level->value,
            'extra' => $this->extra,
            'time' => $this->datetime,
            'severity' => $this->level->getName(),
            'logging.googleapis.com/labels' => $this->labels,
            'logging.googleapis.com/trace' => $this->trace,
        ];

        if (is_array($this->httpRequest)) {
            $data['httpRequest'] = $this->httpRequest;
        }

        return $data;
    }
}
