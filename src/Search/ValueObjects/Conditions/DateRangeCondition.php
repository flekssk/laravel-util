<?php

declare(strict_types=1);

namespace FKS\Search\ValueObjects\Conditions;

use Carbon\Carbon;
use DomainException;

class DateRangeCondition extends Condition
{
    public const TYPE_DATE = 'date';
    public const TYPE_TIMESTAMP = 'timestamp';

    private Carbon $from;
    private Carbon $to;

    public static function allowedTypes(): array
    {
        return [self::TYPE_DATE, self::TYPE_TIMESTAMP];
    }

    public function __construct(string $filterParam, Carbon $from, Carbon $to, string $type)
    {
        if (!in_array($type, self::allowedTypes(), true)) {
            throw new DomainException("Bad type $type. Allowed types: " . implode(', ', self::allowedTypes()));
        }
        $this->filterParam = $filterParam;
        $this->from = $from;
        $this->to = $to;
        $this->type = $type;
    }

    public function getFrom(): Carbon
    {
        return $this->from;
    }

    public function getTo(): Carbon
    {
        return $this->to;
    }

    public function isDate(): bool
    {
        return $this->type === self::TYPE_DATE;
    }

    public function isTimestamp(): bool
    {
        return $this->type === self::TYPE_TIMESTAMP;
    }
}
