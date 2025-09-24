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
use FKS\Swagger\Attributes\Properties\ArrayProperty;
use FKS\Swagger\Attributes\Properties\IntegerProperty;
use FKS\Swagger\Attributes\Properties\StringProperty;
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

        if (in_array('array', $rule, true)) {
            return null;
        }

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

        $type = self::resolveTypeFromRules($rule);

        if ($example === null) {
            $example = self::getTypeExample($type);
        }

        if (str_contains($propertyName, '.*')) {
            $type = 'array';
            $propertyName = str_replace('.*', '', $propertyName);

        }

        return match($type) {
            'array' => new ArrayProperty($propertyName, self::resolveTypeFromRules($rule), $example),
            'string' => new StringProperty($propertyName, example: $example, enum: $enum),
            'integer' => new IntegerProperty(
                $propertyName,
                example: $example ?? 1,
                enum: $enum
            ),
            default => null,
        };
    }

    private static function resolveTypeFromRules(array $rules): ?string
    {
        if (in_array('string', $rules)) {
            return 'string';
        }
        if (in_array('integer', $rules) || in_array('int', $rules)) {
            return 'integer';
        }
        if (in_array('bool', $rules) || in_array('boolean', $rules)) {
            return 'boolean';
        }

        return null;
    }

    private static function getTypeExample(string $type): mixed
    {
        return match($type) {
            'string' => 'string',
            'integer' => 1,
            default => null,
        };
    }
}
