<?php

declare(strict_types=1);

namespace FKS\Facades;

use DateTimeInterface;
use Illuminate\Support\Facades\Facade;
use FKS\ValueObjects\Id;
use Throwable;

/**
 * @method static Id getCurrentDataOwnerId();
 * @method static Id getCurrentUserId();
 * @method static Id getCurrentDayId();
 * @method static Id getSpecifiedDayId(DateTimeInterface $specifiedDate);
 * @method static array getCreatingContext();
 * @method static array getUpdatingContext();
 * @method static array getSpecifiedContext(string $name);
 * @method static Id getNewId();
 * @method static void logException(Throwable $exception, string $logHeader = '');
 */
class FKS extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'FKS';
    }
}
