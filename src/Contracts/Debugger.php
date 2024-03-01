<?php

declare(strict_types=1);

namespace FKS\Contracts;

use Stringable;

interface Debugger
{
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
}
