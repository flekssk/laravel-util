<?php

declare(strict_types=1);

namespace FKS\Instructions\ValueObjects;

use FKS\Instructions\Enums\InstructionDelegateEnum;
use FKS\Instructions\Enums\InstructionsActionEnum;
use FKS\Instructions\Enums\InstructionsRunDeclarationsEnum;

class Instruction
{
    /**
     * @param InstructionsActionEnum $action
     * @param InstructionDelegateEnum $delegatedTo
     * @param InstructionsRunDeclarationsEnum[] $runDeclarations
     * @param array $data
     */
    public function __construct(
        public readonly string $name,
        public readonly InstructionsActionEnum $action,
        public readonly InstructionDelegateEnum $delegatedTo,
        public readonly array $runDeclarations = [],
        public readonly array $data = [],
    ) {
    }
}
