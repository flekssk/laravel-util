<?php

declare(strict_types=1);

namespace FKS\Swagger\Attributes\Helpers;

use BackedEnum;
use DomainException;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Rules\In;
use OpenApi\Attributes\Property;
use OpenApi\Attributes\Schema;
use FKS\Swagger\Attributes\Properties\IntegerProperty;
use FKS\Swagger\Attributes\Properties\StringProperty;
use FKS\Swagger\Attributes\Properties\UuidProperty;
use ReflectionClass;

class SchemaExtractorHelper
{
    /**
     * @param class-string $request
     */
    public static function extractFromRequestRules(string $request, string $schemaName = null): Schema
    {
        if (!is_a($request, FormRequest::class, true)) {
            throw new DomainException('Request class must be instance of ' . FormRequest::class);
        }

        if (!method_exists($request, 'rules')) {
            throw new DomainException("Request $request does not have rules method");
        }

        $reflectionRequest = new ReflectionClass($request);

        $rules = $reflectionRequest->getMethod('rules')->invoke($reflectionRequest->newInstance());

        if (!is_array($rules)) {
            throw new DomainException("Rules method of $request does not return an array");
        }

        return new Schema(
            schema: $schemaName ?? collect(explode('\\', $reflectionRequest->getName()))->last(),
            properties: self::convertRulesToProperties($rules),
        );
    }

    public static function convertRulesToProperties(array $rules): array
    {
        $properties = [];

        foreach ($rules as $propertyName => $rule) {
            $property = self::convertRulesToProperty($propertyName, $rule);

            if ($property !== null) {
                $properties[] = $property;
            }
        }

        return $properties;
    }

    public static function convertRulesToProperty(string $propertyName, string|array $rule): Property|null
    {
        $rule = is_string($rule) ? explode('|', $rule) : $rule;

        $enum = null;
        $example = null;
        foreach ($rule as $item) {
            if ($item instanceof In) {
                $enum = (new ReflectionClass(In::class))->getProperty('values')->getValue($item);
            }
            if ($item instanceof Enum) {
                $type = (new ReflectionClass(Enum::class))->getProperty('type')->getValue($item);
                if (is_a($type, BackedEnum::class, true)) {
                    $enum = array_map(static fn ($value) => $value->value, $type::cases());
                }
            }
            $example = Arr::first($enum ?? []);
        }

        return match(true) {
            in_array('string', $rule) => new StringProperty($propertyName, example: $example, enum: $enum),
            in_array('integer', $rule), in_array('int', $rule) => new IntegerProperty($propertyName,
                example: $example,
                enum: $enum
            ),
            in_array('uuid_or_hex', $rule) => new UuidProperty($propertyName),
            in_array('icd_code', $rule) => new StringProperty($propertyName, 'E1151'),
            default => null,
        };
    }
}
