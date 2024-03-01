<?php

declare(strict_types=1);

namespace FKS\Contracts;

use Stringable;

interface FKSLogger
{

    const FKS_APP_NAME = 'FKS';
    
    public function emergency(string|Stringable $message, array $context = [], array $labels = []);
    public function alert(string|Stringable $message, array $context = [], array $labels = []);
    public function critical(string|Stringable $message, array $context = [], array $labels = []);
    public function error(string|Stringable $message, array $context = [], array $labels = []);
    public function warning(string|Stringable $message, array $context = [], array $labels = []);
    public function notice(string|Stringable $message, array $context = [], array $labels = []);
    public function info(string|Stringable $message, array $context = [], array $labels = []);
    public function debug(string|Stringable $message, array $context = [], array $labels = []);
    public function beginProfile(string|Stringable $token, array $context = [], array $labels = []);
    public function endProfile(string|Stringable $token, array $context = [], array $labels = []);

    public function getTraceId(): string;
    public function setTraceId(string $traceId): void;
    public function addLabel(string $key, $value): void;
    public function addLabels(array $labels): void;
    public function setLabels(array $labels): void;
    public function getLabels(): array;
    public function removeLabel(string $key): void;
    public function clearLabels(): void;
}
