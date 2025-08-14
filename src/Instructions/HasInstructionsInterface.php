<?php

declare(strict_types=1);

namespace FKS\Instructions;

use FKS\Instructions\Collections\InstructionsCollection;
use FKS\Instructions\Enums\InstructionDelegateEnum;
use FKS\Instructions\ValueObjects\Instruction;

interface HasInstructionsInterface
{
    public function getInstructions(InstructionDelegateEnum $delegatedTo = null): InstructionsCollection;
    public function pushInstruction(Instruction ...$instruction): void;
    public function hasInstructionsToRunNow(): bool;
    public function completeInstruction(Instruction $instruction): void;
}
