<?php

namespace FKS\Web\OutputModifiers;

interface OutputModifier
{
    public function modify(string $output = ''): string;
}
