<?php

namespace FKS\Logging;

trait Traceable
{
    protected ?string $traceId = null;

    public function setTraceId(string $traceId): void
    {
        $this->traceId = $traceId;
    }

    public function getTraceId(string $traceId): ?string
    {
        return $this->traceId;
    }
}
