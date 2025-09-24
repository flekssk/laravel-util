<?php

declare(strict_types=1);

namespace FKS\Swagger\Attributes\Schemas\Responses;

use OpenApi\Attributes\MediaType;
use OpenApi\Attributes\Response;
use OpenApi\Attributes\Schema;

class FileResponse extends Response
{
    public function __construct(string $description = null)
    {
        parent::__construct(
            response: 200,
            description: $description ?? 'File response',
            content: new MediaType(
                mediaType: 'application/octet-stream',
                schema: new Schema(
                    type: 'string',
                    format: 'binary'
                )
            )
        );
    }
}
