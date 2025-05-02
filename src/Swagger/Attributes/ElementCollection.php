<?php

declare(strict_types=1);

namespace FKS\Swagger\Attributes;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;

abstract class ElementCollection implements Arrayable
{
    /**
     * @param string $scope
     * @param class-string<Request>|null $requestClass
     */
    public function __construct(private readonly string $scope, protected readonly ?string $requestClass = null)
    {
    }

    public function toArray(): array
    {
        return $this->{$this->scope . 'Scope'}();
    }
}
