<?php

declare(strict_types=1);

namespace FKS\Instructions;

use FKS\Instructions\Contracts\ServerInstructionRunnerInterface;
use FKS\Instructions\Enums\InstructionDelegateEnum;
use FKS\Instructions\HasInstructionsInterface;
use FKS\Instructions\ValueObjects\Instruction;

class ServerInstructionsHandler
{
    public function handle(HasInstructionsInterface $object): void
    {
        foreach ($object->getInstructions(InstructionDelegateEnum::SERVER) as $item) {
            if ($item->)
            $this->runner($object, $item)->run($object, $item);
        }
    }

    public function runner(HasInstructionsInterface $object, Instruction $instruction): ServerInstructionRunnerInterface
    {
        $customRunners = config('instructions')[$object::class][$instruction->action->value] ?? null;

        if ($customRunners === null) {
            throw new \Exception('Instruction runner not found');
        }

        return $customRunners;
    }
}
