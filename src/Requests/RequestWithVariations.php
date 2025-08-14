<?php

namespace FKS\Requests;

use App\Services\Scan\Enums\ScanActionsEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

abstract class RequestWithVariations extends FormRequest
{
    /**
     * @return class-string<\BackedEnum>
     */
    abstract public function variationCase(): string;
    abstract public function variationParamKey(): string;
    abstract public function getRules(\BackedEnum $enum): array;

    public function rules(): array
    {
        $action = $this->get($this->variationParamKey());

        if ($action === null || $this->variationCase()::tryFrom($action) === null) {
            throw new ValidationException(
                $this->validator,
                []
            );
        }

        return [
            array_merge(
                [
                    $this->variationParamKey() => [
                        'required',
                        'string',
                        Rule::enum(ScanActionsEnum::class)
                    ]
                ],
                $this->getRules(ScanActionsEnum::tryFrom($action))
            )
        ];
    }
}
