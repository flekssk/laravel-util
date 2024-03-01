<?php

namespace Tests\Provisions\Http\Request;

class dataRequestPaginationIncorrectValues
{
    public static function getPerPagePaginationIncorrectValues(): array
    {
        return [
            [
                'data' => [
                    'per_page' => -1,
                ],
                'expected' => 'The per page field must be at least 1.'
            ],
            [
                'data' => [
                    'per_page' => 0,
                ],
                'expected' => 'The per page field must be at least 1.'
            ],
            [
                'data' => [
                    'per_page' => null,
                ],
                'expected' => 'The per page field must be an integer.'
            ],
            [
                'data' => [
                    'per_page' => 'page',
                ],
                'expected' => 'The per page field must be an integer.'
            ],
            [
                'data' => [
                    'per_page' => 10000,
                ],
                'expected' => 'The per page field must not be greater than 200.'
            ],
            [
                'data' => [
                    'page' => -1,
                ],
                'expected' => 'The page field must be at least 1.'
            ],
            [
                'data' => [
                    'page' => 0,
                ],
                'expected' => 'The page field must be at least 1.'
            ],
            [
                'data' => [
                    'page' => null,
                ],
                'expected' => 'The page field must be an integer.'
            ],
            [
                'data' => [
                    'page' => 'page',
                ],
                'expected' => 'The page field must be an integer.'
            ]
        ];
    }

    public static function getLimitOffsetPaginationIncorrectValues(): array
    {
        return [
            [
                'data' => [
                    'limit' => -1,
                ],
                'expected' => 'The limit field must be at least 1.'
            ],
            [
                'data' => [
                    'limit' => 0,
                ],
                'expected' => 'The limit field must be at least 1.'
            ],
            [
                'data' => [
                    'limit' => null,
                ],
                'expected' => 'The limit field must be an integer.'
            ],
            [
                'data' => [
                    'limit' => 'page',
                ],
                'expected' => 'The limit field must be an integer.'
            ],
            [
                'data' => [
                    'limit' => 10000,
                ],
                'expected' => 'The limit field must not be greater than 200.'
            ],
            [
                'data' => [
                    'offset' => -1,
                ],
                'expected' => 'The offset field must be at least 0.'
            ],
            [
                'data' => [
                    'offset' => null,
                ],
                'expected' => 'The offset field must be an integer.'
            ],
            [
                'data' => [
                    'offset' => 'page',
                ],
                'expected' => 'The offset field must be an integer.'
            ]
        ];
    }
}
