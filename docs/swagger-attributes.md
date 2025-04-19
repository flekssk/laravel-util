# Swagger attributes

l5-swagger packages provided build swagger documentation usinf
[php attributes ](https://www.php.net/manual/en/language.attributes.overview.php).
Our suetes provides easiest way to build FKS conventional api documentation.

## Setup

Make sure `\FKS\Providers\SwaggerProvider::class` is registered in `providers` section of `config/app.php`

## Samples

### List and counts endpoints documentation

To determine request body of search request you can pass array to requestBody property,
where first ellemnt is base list requet class `\FKS\Http\Attributes\Attributes\Schemas\Requests\SearchRequest::class`
and second element is laraver request class based on `\FKS\Http\Requests\SearchRequest::class`
```PHP
[SearchRequest::class, SearchRequestClass::class]
```

To determine response body you can pass array element to `responses:` where first is
a base list response class `\FKS\Http\Attributes\Schemas\Responses\EntityListResponse::class`
second element is entity schema class bases on `\OpenApi\Attributes\Schema` and third is a response version

```PHP
[EntityListResponse::class, Entity::class, '2.0']
```

```PHP
use FKS\Http\Attributes\Parameters\Collections\Pagination;
use FKS\Http\Attributes\Schemas\Operations\FKSPost;
use FKS\Http\Attributes\Schemas\Requests\CountsRequest;
use FKS\Http\Attributes\Schemas\Requests\SearchRequest;
use FKS\Http\Attributes\Schemas\Responses\EntityListResponse;
use FKS\Http\Attributes\Schemas\Responses\EntityResponse;
use FKS\Http\Attributes\Schemas\Responses\Error404Response;
use FKS\Http\Attributes\Schemas\Responses\Error422Response;
use FKS\Http\Attributes\Schemas\Responses\Error500Response;
use FKS\Http\Attributes\Schemas\Responses\OkResponse;
use OpenApi\Attributes\Parameter;
use OpenApi\Attributes\Schema;
use App\Http\Requests\EntitiesGetListRequest;
use App\Http\Requests\EntitiesGetCountsRequest;

class FilesController extends BaseController
{
     #[FKSPost(
        path: '/api/v1/entity/list',
        operationId: 'entity-list-v1',
        description: 'Entities list',
        security: ['ApiKeyAuth' => []],
        requestBody: [SearchRequest::class, EntitiesGetListRequest::class],
        tags: ['Entities API - v2 - Entities'],
        parameters: new Pagination(Pagination::SCOPE_PAGE, EntitiesGetListRequest::class),
        responses: [
            [EntityListResponse::class, Entity::class, '1.0'],
            Error404Response::class,
            Error422Response::class,
            Error500Response::class,
        ],
    )]
    public function list(SearchRequestClass $request): JsonResponse
    {
    }

    #[FKSPost(
        path: '/api/v1/entity/counts',
        operationId: 'entity-counts-v1',
        description: 'Entities counts',
        security: ['ApiKeyAuth' => []],
        requestBody: [CountsRequest::class, EntitiesGetCountsRequest::class],
        tags: ['Upload API - v2 - Master Outbox Cards'],
        responses: [
            [CountsResponse::class, FileGetCountsRequest::class, '2.0'],
            Error404Response::class,
            Error422Response::class,
            Error500Response::class,
        ],

    )]
    public function counts(EntitiesGetCountsRequest $request): JsonResponse
    {
    }

}
```
if you use pagination based on limit/offset parameters, you can use the following implementation 
```PHP
     #[FKSPost(
        ...
        parameters: new LimitOffsetPagination(LimitOffsetPagination::SCOPE_PAGE, EntitiesGetListRequest::class),
        ...
    )]
```
### Create update and delete endpoints

#### Request body

To determine request body you can pass array to requestBody property,
where first element is base list request class 
`\FKS\Http\Attributes\Attributes\Schemas\Requests\FormDataRequestBody::class` or 
`\FKS\Http\Attributes\Attributes\Schemas\Requests\JsonRequestBody::class` 
and second element is request schema `\OpenApi\Attributes\Schema::class`
```PHP
[JsonRequestBody::class, Schema::class]
```
Or you can pass an object of `\FKS\Http\Attributes\Attributes\Schemas\Requests\FormDataRequestBody::class` or
`\FKS\Http\Attributes\Attributes\Schemas\Requests\JsonRequestBody::class` to request body parameter

```PHP
    #[FKSPost(
        ...
        requestBody: new JsonRequestBody(Schema::class),
        ...
    )]
```

The `JsonRequestBody` can also work with `Illuminate\Foundation\Http\FormRequest` classes and convert rules into schemas.

Suppose we have request class: 

```PHP
class TestRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'string' => [
                'required',
                'string',
                Rule::in(["1", "2"])
            ],
            'integer' => [
                'required',
                'integer',
                Rule::in([1, 2])
            ],
            'uuid_or_hex' => 'required|uuid_or_hex'
        ];
    }
}
```

You can pass class-string into a `JsonRequestBody` as a first parameter, and the scheme will be formed based 
on the rules declared in request class: 

```PHP
    #[FKSPost(
        ...
        requestBody: new JsonRequestBody(TestRequest::class),
        ...
    )]
```

In the end, schema will be looks like:

```json
{
  "TestRequest": {
    "properties": {
      "string": {
        "type": "string",
        "enum": [
          "1",
          "2"
        ],
        "example": "1"
      },
      "integer": {
        "type": "integer",
        "enum": [
          1,
          2
        ],
        "example": 1
      },
      "uuid_or_hex": {
        "type": "string",
        "example": "d46ea2b3-e5ad-4fd7-8b0f-d5ebb784bb00"
      }
    },
    "type": "object"
  }
}
```

#### Responses

To determine response body you can pass array element to responses: where first is a base list response class 
`\FKS\Http\Attributes\Schemas\Responses\EntityResponse::class` second element is entity schema 
class bases on `\OpenApi\Attributes\Schema` and third is a response version

```PHP
[EntityResponse::class, Entity::class, '2.0']
```

```PHP
<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use FKS\Http\Attributes\Parameters\Collections\Pagination;
use FKS\Http\Attributes\Schemas\Operations\Delete;
use FKS\Http\Attributes\Schemas\Operations\Get;
use FKS\Http\Attributes\Schemas\Operations\Patch;
use FKS\Http\Attributes\Schemas\Operations\Post;
use App\Http\Attributes\Schemas\Models\V1\Entity\Entity;
use FKS\Http\Attributes\Schemas\Requests\FormDataRequestBody;
use App\Http\Attributes\Schemas\Requests\V1\Entity\EntityCreateRequest as EntityCreateRequestSchema;
use App\Http\Attributes\Schemas\Requests\V1\Entity\EntityUpdateRequest as EntityUpdateRequestSchema;
use FKS\Http\Attributes\Schemas\Responses\EntityResponse;
use FKS\Http\Attributes\Schemas\Responses\Error404Response;
use FKS\Http\Attributes\Schemas\Responses\Error422Response;
use FKS\Http\Attributes\Schemas\Responses\Error500Response;
use FKS\Http\Attributes\Schemas\Responses\OkResponse;
use App\Http\Requests\Api\V1\Entity\EntityCreateRequest;
use App\Http\Requests\Api\V1\Entity\EntityUpdateRequest;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes\Parameter;
use OpenApi\Attributes\Schema;

class FilesController extends BaseController
{
    #[Post(
        path: '/api/v1/entity',
        operationId: 'entity-create-v1',
        description: 'Create entity',
        security: ['ApiKeyAuth' => []],
        requestBody: [FormDataRequestBody::class, EntityCreateRequestSchema::class],
        tags: ['Upload API - v2 - Master Outbox Cards'],
        responses: [
            [EntityResponse::class, Entity::class],
            Error422Response::class,
            Error500Response::class,
        ]
    )]
    public function create(EntityCreateRequest $request): JsonResponse
    {
    }

    #[Patch(
        path: '/api/v1/entity/{fileId}',
        operationId: 'entity-update-v1',
        description: 'Update entity',
        summary: 'Update entity',
        security: ['ApiKeyAuth' => []],
        requestBody: new FormDataRequestBody(FileUpdateRequestSchema::class),
        tags: ['Entity API - v2 - Entity'],
        parameters: [
            new Parameter(
                parameter: 'entityId', name: 'entityId', description: 'Entity id', in: 'path', required: true,
                schema: new Schema(type: 'string', example: '01aff7a9-6fb8-4edc-8b48-06d4b0535d33')
            ),
        ],
        responses: [
            [EntityResponse::class, Entity::class],
            Error404Response::class,
            Error422Response::class,
            Error500Response::class,
        ]
    )]
    public function update(string $entityId, EntityUpdateRequest $request): JsonResponse
    {
    }
    
        #[Delete(
        path: '/api/v1/entity/{fileId}',
        operationId: 'entity-delete-v1',
        description: 'Delete entity',
        summary: 'Delete entity',
        security: ['ApiKeyAuth' => []],
        tags: ['Upload API - v2 - Master Outbox Cards'],
        parameters: [
            new Parameter(
                parameter: 'entityId', name: 'entityId', description: 'Entity id', in: 'path', required: true,
                schema: new Schema(type: 'string', example: '01aff7a9-6fb8-4edc-8b48-06d4b0535d33')
            ),
        ],
        responses: [
            OkResponse::class,
            Error404Response::class,
            Error422Response::class,
            Error500Response::class,
        ]
    )]
    public function delete(string $entityId): JsonResponse
    {
    }
}
```

### Entity schema example

```PHP
<?php

declare(strict_types=1);

namespace App\Http\Attributes\Schemas\Models\V2\MasterOutbox;

use FKS\Http\Attributes\Properties\BooleanProperty;
use FKS\Http\Attributes\Properties\DateProperty;
use FKS\Http\Attributes\Properties\IntegerProperty;
use FKS\Http\Attributes\Properties\ObjectsArrayProperty;
use FKS\Http\Attributes\Properties\StringProperty;
use FKS\Http\Attributes\Properties\UuidProperty;
use OpenApi\Attributes\Schema;

class Entity extends Schema
{
    public function __construct()
    {
        parent::__construct(
            properties: [
                new UuidProperty('entity_id'),
                new UuidProperty('data_owner_id'),
                new StringProperty('description'),
                new DateProperty('assigned_period'),
                new IntegerProperty('visibility_type_id'),
                new UuidProperty('another_entity_id'),
                new BooleanProperty('flag'),
                new IntegerProperty('integer_property', 50000),
                new StringProperty('md5_checksum', 'd46ea2b3e5ad4fd78b0fd5ebb784bb00'),
                new ObjectsArrayProperty(
                    'allowed_entities',
                    [
                        new IntegerProperty('entity_type_id'),
                        new UuidProperty('entity_id')
                    ]
                )
            ]
        );
    }
}
```
