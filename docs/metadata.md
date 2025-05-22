# Swagger metadata

## Description

Provides ability to create and manage entity metadata. Metadata is a support table that contains key/value structure.

## Setup

1. Create or use existed table.

```PHP
Schema::create('{entity}_metadata', static function (Blueprint $table) {
    $table->binary('{entity}_id', 16);
    $table->binary('{entity}_metadata_id', 16);
    $table->string('metadata_key', 50);
    $table->string('metadata_value', 10000);

    $table->binary('created_at_day_id', 16);
    $table->dateTime('created_at');
    $table->binary('created_by', 16)->nullable();

    $table->binary('updated_at_day_id', 16)->nullable();
    $table->dateTime('updated_at')->nullable();
    $table->binary('updated_by', 16)->nullable();

    $table->binary('deleted_at_day_id', 16)->nullable();
    $table->dateTime('deleted_at')->nullable();
    $table->binary('deleted_by', 16)->nullable();

    $table->primary(['{entity}_id', '{entity}_metadata_id']);
    $table->unique(['{entity}_id', '{entity}_metadata_id', 'task_metadata_key', 'deleted_at'], 'idx_{entity}_metadata_unique');
})
```
2. Configure your entity settings in `config/FKS-metadata.php`

```PHP
return [
    'entities' => [
        App\Models\Entity::class => [
            'table' => 'entity_metadata',
            'primary_key' => 'entity_metadata_id',
            'entity_table' => 'entities',
            'entity_primary_key' => 'entity_id',
            'metadata_key_field_name' => 'metadata_key',
            'metadata_value_field_name' => 'metadata_value',
        ],
    ],
];
```

3. Create metadata service

```PHP
<?php

namespace App\Services\Entity;

use App\Models\Entity;
use FKS\Metadata\MetadataService;

class EntityMetadataService extends MetadataService
{
    public static function getEntity(): string
    {
        return Entity::class;
    }
}
```

## Basic usage

### Store metadata

```PHP

use App\Services\Entity\EntityMetadataService;

class TestService 
{
    /**
    * @param EntityMetadataService|\FKS\Metadata\MetadataService $metadataService
    */
    public function __construct(private readonly EntityMetadataService $metadataService)
    {
    }
    
    public function create(array $data, array $metadata)
    {
        ...
        $this->metadataService->upsertMetadataChunk($metadata);
        ...
    }
}
```

An argument of `upsertMetadataChunk` must be an array where key is metadatada_key and value is a metadata_value. You can store string, numeric, array, it will be automaticaly cast to string on set, and restor from string on get via 
`src/Casts/MetadataValueCast.php`
```PHP
$metadata = [
    'ket_1' => 'value_1',
    'key_2' => [
        'test' => 'test',
    ],
    'key_3' => 1111
];
```
Note: If metadata already exists for given entity id, is will be updated.

### Get metadata

You can apply `src/Services/Metadata/Models/Traits/HasMetadata.php` trait to model and metadata relations was be abel into your model automaticaly based on configuration.

### Aggregate metadata values

You can use `MetadataService::aggregate()` method to get aggregated int/decimal value. You need to pass your model query as a first parameter and aggregations will be applied for it.

### Filtration

You can add metadata filter to yours `SearchRequest`. This filter is dynemic which means you can filtrate by any metadata_key and metadata_value. 
We have several filter types: contains_hex, integer, contains_string, search_string. Example:

```JSON
{
  "filter": {
    "metadata": [
      {
        "filter_type": "contains_hex",
        "metadata_key": "metadata_key",
        "data": {
          "contains": [
            "d46ea2b3-e5ad-4fd7-8b0f-d5ebb784bb00"
          ],
          "notcontains": [
            "d46ea2b3-e5ad-4fd7-8b0f-d5ebb784bb00"
          ]
        }
      },
      {
        "filter_type": "integer",
        "metadata_key": "metadata_key",
        "data": {
          "eq": 1,
          "ne": 1,
          "le": 1,
          "lt": 1,
          "gt": 1,
          "ge": 1
        }
      },
      {
        "filter_type": "contains_string",
        "metadata_key": "metadata_key",
        "data": {
          "contains": [
            "string"
          ],
          "notcontains": [
            "string"
          ]
        }
      },
      {
        "filter_type": "search_string",
        "metadata_key": "metadata_key",
        "data": "value"
      }
    ]
  }
}
```