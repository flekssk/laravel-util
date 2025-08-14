<?php

declare(strict_types=1);

namespace FKS\Actions;

use App\Services\DataStructure\Facades\RuleParserFacade;
use App\Services\DataStructure\Support\RuleParser;
use Illuminate\Foundation\Bus\PendingDispatch;
use Illuminate\Validation\ValidationRuleParser;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\ValidateActions;
use Lorisleiva\Actions\Concerns\WithAttributes;
use FKS\Instructions\Collections\InstructionsCollection;
use FKS\Instructions\ValueObjects\Instruction;
use FKS\Instructions\ValueObjects\ProvideDataInstruction;
use FKS\Serializer\SerializerFacade;

class Action
{
    use AsAction;
    use ValidateActions;
    use WithAttributes;

    public static function dispatch(...$arguments): PendingDispatch
    {
        self::dispatch(...$arguments);
    }

    public function runInstractions(): InstructionsCollection
    {
        $instructions = new InstructionsCollection([
            new ProvideDataInstruction(
                RuleParserFacade::parse($this->rules())
            )
        ]);

        return $instructions;
    }

    public static function run(mixed ...$arguments): mixed
    {
        $action = static::make();

        if ($action->actionDTO() !== null) {
            return $action->handle(SerializerFacade::deserializeFromArray($arguments, $action->actionDTO()));
        }

        return $action->handle(...$arguments);
    }

    public function actionDTO(): ?string
    {
        return null;
    }
}
