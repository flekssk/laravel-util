<?php

namespace FKS\Instructions\Contracts;

use FKS\Instructions\HasInstructionsInterface;
use FKS\Instructions\ValueObjects\Instruction;

interface ServerInstructionRunnerInterface
{
    public function run(HasInstructionsInterface $entity, Instruction $instruction);
}
