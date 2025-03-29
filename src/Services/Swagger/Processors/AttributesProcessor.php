<?php

declare(strict_types=1);

namespace FKS\Services\Swagger\Processors;

use OpenApi\Analysis;
use OpenApi\Attributes\Schema;
use OpenApi\Generator;
use FKS\Http\Attributes\Schemas\Requests\MediaType;

class AttributesProcessor
{
    public function __invoke(Analysis $analysis): void
    {
        foreach ($analysis->annotations as $annotation) {
            if (
                property_exists($annotation, 'elementSchema')
                && $annotation->elementSchema !== Generator::UNDEFINED
            ) {
                $annotation->ref = $this->storeSchema($analysis, $annotation->elementSchema);
            }

            if (
                $annotation instanceof MediaType
                && $annotation->getHiddenSchema() instanceof Schema
            ) {
                $annotation->schema = new Schema(ref: $this->storeSchema($analysis, $annotation->getHiddenSchema()));
            }
        }
    }

    public function storeSchema(Analysis $analysis, Schema $schema): string
    {
        $schemaName = ($schema->schema === Generator::UNDEFINED
            ? str_replace('Schema', '', class_basename($schema))
            : $schema->schema);

        $schema->schema = $schemaName;

        foreach ($analysis->openapi->components->schemas as $index => $existedSchema) {
            if ($existedSchema->schema === $schemaName) {
                unset($analysis->openapi->components->schemas[$index]);
            }
        }

        $analysis->openapi->components->schemas[] = $schema;

        return '#/components/schemas/' . $schemaName;
    }
}
