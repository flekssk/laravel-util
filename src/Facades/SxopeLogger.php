<?php

namespace FKS\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static void emergency(string|\Stringable $message, array $context = [], array $labels = [])
 * @method static void alert(string|\Stringable $message, array $context = [], array $labels = [])
 * @method static void critical(string|\Stringable $message, array $context = [], array $labels = [])
 * @method static void error(string|\Stringable $message, array $context = [], array $labels = [])
 * @method static void warning(string|\Stringable $message, array $context = [], array $labels = [])
 * @method static void notice(string|\Stringable $message, array $context = [], array $labels = [])
 * @method static void info(string|\Stringable $message, array $context = [], array $labels = [])
 * @method static void debug(string|\Stringable $message, array $context = [], array $labels = [])
 * @method static void beginProfile(string|\Stringable $token, array $context = [], array $labels = [])
 * @method static void endProfile(string|\Stringable $token, array $context = [], array $labels = [])
 *
 * @method static string getTraceId()
 * @method static void setTraceId(string $traceId)
 * @method static void addLabel(string $key, $value)
 * @method static void setLabels(array $labels)
 * @method static void addLabels(array $labels)
 * @method static array getLabels()
 * @method static void removeLabel(string $key)
 * @method static void clearLabels()
 */
class FKSLogger extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \FKS\Contracts\FKSLogger::class;
    }
}
