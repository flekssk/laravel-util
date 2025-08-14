<?php

namespace FKS\Actions;

use App\Services\DataStructure\Facades\RuleParserFacade;
use Illuminate\Validation\Rule;
use FKS\Instructions\Collections\InstructionsCollection;
use FKS\Instructions\ValueObjects\ProvideDataInstruction;

abstract class StrategyAction extends Action
{
    abstract public function getStrategyName(): string;

    /**
     * @return array|class-string<\BackedEnum>
     */
    abstract public function getStrategyActions(): array|string;

    /**
     * @return array<string, class-string<Action>>
     */
    abstract public function getActions(): array;

    public static function run(mixed ...$arguments): mixed
    {
        $action = static::make()->resolveStrategyAction($arguments);

        return $action->run(...$arguments);
    }

    public function rules(): array
    {
        $rules = [
            'startegies' => 'required|array',
            'startegies.*' => 'required|array',
        ];
        foreach ($this->getActions() as $name => $item) {
            $rules["startegies.*.$name.data"] = 'required|array';
            $rules["startegies.*.$name.data"] = $item::make()->rules();
        }

        return $rules;
    }


    private function resolveStrategyAction(array $data): Action
    {
        return $this->resolveStrategyActionClassName($data)::make();
    }

    /**
     * @return class-string<Action>
     */
    private function resolveStrategyActionClassName(array $data): string
    {
        $action = $data[$this->getStrategyName()];
        if ($this->getStrategyValues() === 'string') {
            $action = $this->getStrategyValues()::tryFrom($action);
        }

        $actionClass = array_key_exists($action, $this->getActions()) ?? throw new \Exception("Strategy not found");

        return $action;
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
}
