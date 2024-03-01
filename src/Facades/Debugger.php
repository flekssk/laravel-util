<?php

namespace FKS\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static emergency(string|\Stringable $message, array $context = [], array $labels = []);
 * @method static alert(string|\Stringable $message, array $context = [], array $labels = []);
 * @method static critical(string|\Stringable $message, array $context = [], array $labels = []);
 * @method static error(string|\Stringable $message, array $context = [], array $labels = []);
 * @method static warning(string|\Stringable $message, array $context = [], array $labels = []);
 * @method static notice(string|\Stringable $message, array $context = [], array $labels = []);
 * @method static info(string|\Stringable $message, array $context = [], array $labels = []);
 * @method static debug(string|\Stringable $message, array $context = [], array $labels = []);
 * @method static beginProfile(string|\Stringable $token, array $context = [], array $labels = []);
 * @method static endProfile(string|\Stringable $token, array $context = [], array $labels = []);
 */
class Debugger extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \FKS\Contracts\Debugger::class;
    }
}
