<?php

declare(strict_types=1);

namespace FKS\Http\Attributes\Schemas\Operations;

use FKS\Http\Attributes\ElementCollection;
use L5Swagger\Exceptions\L5SwaggerException;
use OpenApi\Attributes\ExternalDocumentation;
use OpenApi\Attributes\RequestBody;
use OpenApi\Attributes\Response;

trait OperationsTrait
{
    public function __construct(
        ?string $path = null,
        ?string $operationId = null,
        ?string $description = null,
        ?string $summary = null,
        ?array $security = null,
        ?array $servers = null,
        RequestBody|array|null $requestBody = null,
        ?array $tags = null,
        array|ElementCollection|null $parameters = null,
        ?array $responses = null,
        ?array $callbacks = null,
        ?ExternalDocumentation $externalDocs = null,
        ?bool $deprecated = null,
        // annotation
        ?array $x = null,
        ?array $attachables = null
    ) {
        $resultParameters = [];

        if ($parameters !== null) {
            if ($parameters instanceof ElementCollection) {
                $resultParameters += $parameters->toArray();
            }

            foreach ($parameters as $parameter) {
                if ($parameter instanceof ElementCollection) {
                    $resultParameters = array_merge($resultParameters, $parameter->toArray());
                } else {
                    $resultParameters[] = $parameter;
                }
            }
        }

        parent::__construct(
            $path,
            $operationId,
            $description ?? $summary,
            $summary ?? $description,
            $security,
            $servers,
            is_array($requestBody) ? $this->createSchemaFromArray($requestBody) : $requestBody,
            $tags,
            $resultParameters,
            $this->processResponses($responses),
            $callbacks,
            $externalDocs,
            $deprecated,
            $x,
            $attachables,
        );
    }

    public function processResponses(array|Response|null $responses): array
    {
        $processesResponses = [];
        if ($responses !== null) {
            foreach ($responses as $response) {
                if (is_array($response)) {
                    $processesResponses[] = $this->createSchemaFromArray($response);
                } elseif (is_a($response, Response::class)) {
                    $processesResponses[] = $response;
                } elseif (is_a($response, Response::class, true)) {
                    $processesResponses[] = new $response();
                } else {
                    throw new L5SwaggerException(
                        "Can't build response object from type: " . is_object($response)
                            ? $response::class
                            : $response
                    );
                }
            }
        }

        return $processesResponses;
    }

    private function createSchemaFromArray(array $data)
    {
        $responseClass = array_shift($data);

        try {
            $schema = new $responseClass(...$data);
        } catch (\Throwable $e) {
            throw new L5SwaggerException("Can't create schema $responseClass: " . $e->getMessage());
        }

        return $schema;
    }
}
