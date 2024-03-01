<?php

declare(strict_types=1);

namespace FKS\Contracts;

use DateTimeInterface;
use FKS\ValueObjects\Id;
use Throwable;

interface FKS
{
    public function getCurrentDataOwnerId(): Id;
    public function getCurrentUserId(): Id;
    public function getCurrentDayId(): Id;
    public function getSpecifiedDayId(DateTimeInterface $specifiedDateTime): Id;
    public function getCreatingContext(): array;
    public function getUpdatingContext(): array;
    public function getSpecifiedContext(string $name): array;
    public function getNewId(): Id;
    public function logException(Throwable $exception, string $logHeader = ''): void;
}
