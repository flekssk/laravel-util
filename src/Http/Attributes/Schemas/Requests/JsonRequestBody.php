<?php

declare(strict_types=1);

namespace FKS\Http\Attributes\Schemas\Requests;

use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes\RequestBody;
use OpenApi\Attributes\Schema;
use OpenApi\Generator;
use FKS\Http\Attributes\Helpers\SchemaExtractorHelper;

class JsonRequestBody extends RequestBody
{
    public string|Schema $elementSchema = Generator::UNDEFINED;

    public function __construct(string|Schema|FormRequest $schema)
    {
        if (is_a($schema, FormRequest::class, true)) {
            $schema = SchemaExtractorHelper::extractFromRequestRules($schema);
        }

        if (is_a($schema, Schema::class, true)) {
            $content = new JsonContent(
                type: 'object',
            );
            $content->elementSchema = is_string($schema) ? new $schema() : $schema;
        } else {
            $content = new JsonContent(
                ref: '#/components/schemas/' . $schema,
                type: 'object',
            );
        }

        parent::__construct(
            required: true,
            content: [
                $content
            ]
        );
    }
}
