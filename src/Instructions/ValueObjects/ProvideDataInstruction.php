<?php

declare(strict_types=1);

namespace FKS\Instructions\ValueObjects;

use FKS\Instructions\Enums\InstructionDelegateEnum;
use FKS\Instructions\Enums\InstructionsActionEnum;

class ProvideDataInstruction extends Instruction
{
    public function __construct(array $fields)
    {
        parent::__construct(
            "provide_data",
            InstructionsActionEnum::PROVIDE_DATA,
            InstructionDelegateEnum::USER,
            data: [
                'fields' => $fields,
            ]
        );
    }
}
