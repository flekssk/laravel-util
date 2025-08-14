<?php

declare(strict_types=1);

namespace FKS\Instructions\Collections;

use Illuminate\Support\Collection;
use FKS\Instructions\ValueObjects\Instruction;

/**
 * @template Instruction
 * @extends Collection<Instruction>
 */
class InstructionsCollection extends Collection
{
}
