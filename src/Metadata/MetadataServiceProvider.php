<?php

declare(strict_types=1);

namespace FKS\Metadata;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;

class MetadataServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
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
