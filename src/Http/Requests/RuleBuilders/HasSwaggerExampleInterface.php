<?php

namespace FKS\Http\Requests\RuleBuilders;

interface HasSwaggerExampleInterface
{
    public function getExample(): ?string;
}
