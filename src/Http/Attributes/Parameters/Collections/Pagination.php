<?php

declare(strict_types=1);

namespace FKS\Http\Attributes\Parameters\Collections;

use FKS\Http\Attributes\ElementCollection;
use OpenApi\Attributes\Parameter;
use OpenApi\Attributes\Schema;
use FKS\Http\Requests\SearchRequest;

class Pagination extends ElementCollection
{
    public const SCOPE_PAGE = 'page';

    public function pageScope(): array
    {
        $maxPerPage = 1000;

        if (is_a($this->requestClass, SearchRequest::class, true)) {
            $maxPerPage = $this->requestClass::getPerPageMax();
        }

        return [
            new Parameter(
                name: 'per_page',
                description: "Number of entries per page (1-$maxPerPage)",
                in: 'query',
                required: false,
                schema: new Schema(
                    type: 'integer',
                    default: 20,
                    maximum: $maxPerPage,
                    minimum: 1,
                    example: 20,
                )
            ),
            new Parameter(
                name: 'page',
                description: 'Page number',
                in: 'query',
                required: false,
                schema: new Schema(
                    type: 'integer',
                    default: 1,
                    example: 1
                )
            ),
        ];
    }
}
