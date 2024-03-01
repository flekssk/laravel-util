<?php


namespace FKS\Logging;

use Illuminate\Support\Str;

class LogContext
{
    private ?string $traceId = null;
    private array $labels = [];

    public function getTraceId(): string
    {
        if (!$this->traceId) {
            $this->traceId = Str::uuid()->toString();
        }
        return $this->traceId;
    }

    public function setTraceId(string $traceId): self
    {
        $this->traceId = $traceId;
        return $this;
    }

    public function addLabel(string $key, string $value): void
    {
        $this->labels[$key] = $value;
    }

    public function removeLabel(string $key): void
    {
        unset($this->labels[$key]);
    }

    public function getLabels(): array
    {
        return $this->labels;
    }
}
