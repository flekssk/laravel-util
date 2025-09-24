<?php

declare(strict_types=1);

namespace FKS\Instructions;

use FKS\Instructions\Collections\InstructionsCollection;
use FKS\Instructions\Enums\InstructionDelegateEnum;
use FKS\Instructions\ValueObjects\Instruction;

trait HasInstructions
{
    private InstructionsCollection $instructions;

    public function getInstructions(InstructionDelegateEnum $delegatedTo = null): InstructionsCollection
    {
        if (!isset($this->instructions)) {
            $this->setInstructions(new InstructionsCollection());
        }

        return $this->instructions->when($delegatedTo, fn ($collection) => $collection->where('delegatedTo', $delegatedTo));
    }

    public function pushInstruction(Instruction ...$instruction): void
    {
        $this->instructions->push($instruction);
    }

    public function hasInstructionsToRunNow(): bool
    {
        return $this->instructions->isNotEmpty();
    }

    public function completeInstruction(Instruction $instruction): void
    {
        if (!$this->instructions->contains($instruction)) {
            throw new \Exception('Can`t complete instruction. Instruction not found');
        }

        $this->instructions->reject(function ($item) use ($instruction) {
            return $item === $instruction;
        });
    }

    public function setInstructions(InstructionsCollection $instructions): void
    {
        $this->instructions = $instructions;
    }
}
