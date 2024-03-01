<?php

declare(strict_types=1);

namespace FKS\Logging;

use Illuminate\Support\Arr;
use Illuminate\Support\Env;
use Illuminate\Support\Facades\Facade;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Stringable;
use FKS\Contracts\FKSLogger;

class FKSLoggerService implements FKSLogger
{
    public function __construct(private LoggerInterface $logger) {}

    private array $FKS_profile_tokens = [];

    public function emergency(Stringable|string $message, array $context = [], array $labels = []): void
    {
        $this->logger->emergency($message, $this->getMergedContext($context, $labels));
    }

    public function alert(Stringable|string $message, array $context = [], array $labels = []): void
    {
        $this->logger->alert($message, $this->getMergedContext($context, $labels));
    }

    public function critical(Stringable|string $message, array $context = [], array $labels = []): void
    {
        $this->logger->critical($message, $this->getMergedContext($context, $labels));
    }

    public function error(Stringable|string $message, array $context = [], array $labels = []): void
    {
        $this->logger->error($message, $this->getMergedContext($context, $labels));
    }

    public function warning(Stringable|string $message, array $context = [], array $labels = []): void
    {
        $this->logger->warning($message, $this->getMergedContext($context, $labels));
    }

    public function notice(Stringable|string $message, array $context = [], array $labels = []): void
    {
        $this->logger->notice($message, $this->getMergedContext($context, $labels));
    }

    public function info(Stringable|string $message, array $context = [], array $labels = []): void
    {
        $this->logger->info($message, $this->getMergedContext($context, $labels));
    }

    public function debug(Stringable|string $message, array $context = [], array $labels = []): void
    {
        $this->logger->debug($message, $this->getMergedContext($context, $labels));
    }

    public function isManualStartInConsole(): bool
    {
        return str_starts_with((string)Env::get('TERM'), 'xterm');
    }

    public function beginProfile(string|Stringable $token, array $context = [], array $labels = []): void
    {
        if ($this->isManualStartInConsole()) {
            $this->logger->debug(
                "BEGIN PROFILE: $token",
                $this->getMergedContext($context, $labels)
            );
        }
        $this->FKS_profile_tokens[$token] = [
            'microtime' => microtime(true),
            'context' => $context,
        ];
    }

    public function endProfile(string|Stringable $token, array $context = [], array $labels = []): void
    {
        $info = $this->FKS_profile_tokens[$token] ?? null;
        if (is_null($info)) {
            return;
        }
        $diff = round(microtime(true) - $info['microtime'], 3);
        $message = $this->isManualStartInConsole()
            ? sprintf("END PROFILE: %s (TIME: %.3f sec)", $token, $diff)
            : sprintf("PROFILING: %s (TIME: %.3f sec)", $token, $diff);

        $this->logger->debug($message, $this->getMergedContext($context, $labels + ['time' => $diff]));
    }

    public function getCaller(int $deepOffset): ?string
    {
        $items = array_filter(debug_backtrace(limit: 5), static function($item) {
            return $item['class'] === Facade::class;
        });
        if (count($items) !== 0) {
            $deepOffset = array_keys($items)[0];
        }

        $deepLimit = 2 + $deepOffset;
        $recordKey = 1 + $deepOffset;

        $data = debug_backtrace(limit: $deepLimit);
        $data = $data[$recordKey];
        $class = $data['class'] ?? null;
        $function = $data['function'] ?? null;
        $caller = null;
        if ($class) {
            $caller .= $class;
        }
        if ($function) {
            if ($caller !== null) {
                $caller .= '::';
            }
            $caller .= $function;
        }
        return $caller;
    }

    public function getMergedContext(array $context, array $labels): array
    {
        foreach (self::$labels as $labelKey => $labelValue) {
            if (is_integer($labelKey)) {
                $labelKey = $labelValue;
            }
            Arr::set($context, "labels.$labelKey", $labelValue);
        }

        Arr::set($context, 'labels.caller', $this->getCaller(2));
        $labels['FKS_app_name'] = FKSLogger::FKS_APP_NAME;

        foreach ($labels as $labelKey => $labelValue) {
            if (is_integer($labelKey)) {
                $labelKey = $labelValue;
            }
            Arr::set($context, "labels.$labelKey", $labelValue);
        }
        return $context;
    }

    static protected ?string $traceId = null;

    public function getTraceId(): string
    {
        if (self::$traceId) {
            return self::$traceId;
        }
        if ($traceId = $_SERVER['HTTP_X_REQUEST_ID'] ?? null) {
            self::$traceId = $traceId;
            return $traceId;
        }
        self::$traceId = Uuid::uuid4()->toString();
        return self::$traceId;
    }

    public function setTraceId(string $traceId): void
    {
        self::$traceId = $traceId;
    }

    static protected array $labels = [];

    public function addLabel(string $key, $value): void
    {
        self::$labels[$key] = $value;
    }

    public function setLabels(array $labels): void
    {
        self::$labels = $labels;
    }

    public function addLabels(array $labels): void
    {
        self::$labels += $labels;
    }

    public function getLabels(): array
    {
        return self::$labels;
    }

    public function removeLabel(string $key): void
    {
        unset(self::$labels[$key]);
    }

    public function clearLabels(): void
    {
        self::$labels = [];
    }
}
