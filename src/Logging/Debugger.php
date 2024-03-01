<?php

declare(strict_types=1);

namespace FKS\Logging;

use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Facade;
use Psr\Log\LoggerInterface;
use Stringable;
use FKS\Contracts\FKSLogger;

trait Debugger
{
    private array $FKS_profile_tokens = [];

    public function emergency(string|Stringable $message, array $context = [], array $labels = []): void
    {
        /** @var $logger LoggerInterface */
        $logger = app(LoggerInterface::class);
        $logger->emergency($message, $this->getMergedContext($context, $labels));
    }

    public function alert(string|Stringable $message, array $context = [], array $labels = []): void
    {
        /** @var $logger LoggerInterface */
        $logger = app(LoggerInterface::class);
        $logger->alert($message, $this->getMergedContext($context, $labels));
    }

    public function critical(string|Stringable $message, array $context = [], array $labels = []): void
    {
        /** @var $logger LoggerInterface */
        $logger = app(LoggerInterface::class);
        $logger->critical($message, $this->getMergedContext($context, $labels));
    }

    public function error(string|Stringable $message, array $context = [], array $labels = []): void
    {
        /** @var $logger LoggerInterface */
        $logger = app(LoggerInterface::class);
        $logger->error($message, $this->getMergedContext($context, $labels));
    }

    public function warning(string|Stringable $message, array $context = [], array $labels = []): void
    {
        /** @var $logger LoggerInterface */
        $logger = app(LoggerInterface::class);
        $logger->warning($message, $this->getMergedContext($context, $labels));
    }

    public function notice(string|Stringable $message, array $context = [], array $labels = []): void
    {
        /** @var $logger LoggerInterface */
        $logger = app(LoggerInterface::class);
        $logger->notice($message, $this->getMergedContext($context, $labels));
    }

    public function info(string|Stringable $message, array $context = [], array $labels = []): void
    {
        /** @var $logger LoggerInterface */
        $logger = app(LoggerInterface::class);
        $logger->info($message, $this->getMergedContext($context, $labels));
    }

    public function debug(string|Stringable $message, array $context = [], array $labels = []): void
    {
        /** @var $logger LoggerInterface */
        $logger = app(LoggerInterface::class);
        $logger->debug($message, $this->getMergedContext($context, $labels));
    }

    public function beginProfile(string|Stringable $token, array $context = [], array $labels = []): void
    {
        /** @var $logger LoggerInterface */
        $logger = app(LoggerInterface::class);
        $logger->debug(
            "BEGIN PROFILE: $token",
            $this->getMergedContext($context, $labels)
        );
        $this->FKS_profile_tokens[$token] = [
            'microtime' => microtime(true),
            'context' => $context,
        ];
    }

    public function endProfile(string|Stringable $token, array $context = [], array $labels = []): void
    {
        $info = $this->FKS_profile_tokens[$token] ?? null;
        if ($info) {
            /** @var $logger LoggerInterface */
            $logger = app(LoggerInterface::class);
            $diff = microtime(true) - $info['microtime'];
            $logger->debug(
                sprintf("END PROFILE: %s (TIME: %.3f sec)", $token, $diff),
                $this->getMergedContext($context, $labels + ['time' => round($diff, 3)])
            );
        }
    }

    public function getCaller(int $deepOffset): ?string
    {
        $items = array_filter(debug_backtrace(limit: 5), function($item) {
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
        Arr::set($context, 'labels.caller', $this->getCaller(2));
        $labels['FKS_app_name'] = FKSLogger::FKS_APP_NAME;
        $context['labels'] = array_merge(
            Arr::get($context, 'labels', []),
            $labels
        );
        return $context;
    }
}
