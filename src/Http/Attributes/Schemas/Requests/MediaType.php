<?php

declare(strict_types=1);

namespace FKS\Http\Attributes\Schemas\Requests;

use OpenApi\Attributes\Schema;
use OpenApi\Generator;
use OpenApi\Attributes\MediaType as BaseMediaType;

class MediaType extends BaseMediaType
{
    private ?Schema $hiddenSchema = null;

    public function __construct(Schema|string $schema)
    {
        if ($schema instanceof Schema) {
            $this->hiddenSchema = $schema;
        }

        parent::__construct(
            mediaType: "multipart/form-data",
            schema: $schema instanceof Schema ? null : new Schema(ref: '#/components/schemas/' . $schema)
        );
    }

    public function getHiddenSchema(): ?Schema
    {
        return $this->hiddenSchema;
    }
}
