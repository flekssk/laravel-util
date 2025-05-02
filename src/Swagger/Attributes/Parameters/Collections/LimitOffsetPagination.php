<?php

namespace FKS\Swagger\Attributes\Parameters\Collections;

use OpenApi\Attributes\Parameter;
use OpenApi\Attributes\Schema;
use FKS\Search\Requests\SearchRequest;
use FKS\Swagger\Attributes\ElementCollection;

class LimitOffsetPagination extends ElementCollection
{
    public const SCOPE_PAGE = 'page';

    public function pageScope(): array
    {
        if (is_a($this->requestClass, SearchRequest::class, true)) {
            $maxPerPage = $this->requestClass::getPerPageMax();
        } else {
            $maxPerPage = SearchRequest::DEFAULT_PER_PAGE;
        }

        return [
            new Parameter(
                name: 'limit',
                description: 'limit (1 to ' . $maxPerPage . ')',
                in: 'query',
                required: false,
                schema: new Schema(
                    type: 'integer',
                    maximum: $maxPerPage,
                    example: 20
                )
            ),
            new Parameter(
                name: 'offset',
                description: 'offset (0 to max)',
                in: 'query',
                required: false,
                schema: new Schema(
                    type: 'integer',
                    example: 10
                )
            ),
        ];
    }
}
