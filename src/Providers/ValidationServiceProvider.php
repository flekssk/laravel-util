<?php

declare(strict_types=1);

namespace FKS\Providers;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;
use FKS\ValueObjects\Id;

class ValidationServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Validator::extend('uuid_or_hex', function ($attribute, $value, $parameters, $validator) {
            try {
                return Id::createNullable($value) !== null;
            } catch (\Exception $e) {
                return false;
            }
        }, 'The :attribute field is invalid. Please provide a valid uuid or hex.');

        Validator::extend('icd_code', function ($attribute, $value, $parameters, \Illuminate\Validation\Validator $validator) {
            $maxLength = str_contains((string) $value, '.') ? 'max:8' : 'max:7';

            $data = [];
            Arr::set($data, $attribute, $value);


            $customValidator = Validator::make(
                $data,
                [
                    $attribute => [
                        'string',
                        $maxLength,
                        'regex:/^(?![.]$)[A-Za-z0-9.]+$/',
                    ]
                ]
            );
            if ($customValidator->errors()->count() !== 0) {
                $validator->errors()
                    ->add(
                        $attribute,
                        "The $attribute must not be greater than 7 characters without a dot and 8 characters including a dot."
                    );
            }

            return true;
        });

        Validator::extend('metadata_value', function ($attribute, $value, $parameters, $validator) {
            $maxLength = (int) ($parameters[0] ?? 10000);
            if (is_array($value)) {
                $value = json_encode($value);
            }

            if (!is_string($value)) {
                $validator->errors()
                    ->add(
                        $attribute,
                        "The $attribute must be string or object " . gettype($value) . ' given.'
                    );
            }

            if (strlen($value) > $maxLength) {
                $validator->errors()
                    ->add(
                        $attribute,
                        "The $attribute must not be greater $maxLength chars.",
                    );
            }

            return true;
        });
    }
}
