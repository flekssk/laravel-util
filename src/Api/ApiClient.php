<?php

declare(strict_types=1);

namespace FKS\Api;

use FKS\Api\Enrichers\RequestMiddlewareInterface;
use FKS\Api\ValueObjects\ApiClientConfig;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use JsonException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use RuntimeException;
use FKS\Api\DTO\ApiResponse;
use FKS\Api\DTO\MergableApiResponseInterface;
use FKS\Serializer\Serializer;
use FKS\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Response;

class ApiClient
{
    public const DEFAULT_REQUEST_ATTEMPTS_COUNT = 3;
    public const DEFAULT_SLEEP_SECONDS = 1;

    public function __construct(
        protected Client $client,
        protected ApiClientConfig $config,
    ) {
    }

    /**
     * @template T
     * @psalm-param class-string<T> $responseClass
     * @return T
     * @throws JsonException
     */
    protected function handleListResponse(
        $response,
        string $responseClass = null,
        string $elementClass = null,
        array $propertiesMapping = [],
    ): ApiResponse {
        try {
            $data = json_decode(
                (string) $response->getBody()->getContents(),
                true,
                512,
                JSON_THROW_ON_ERROR,
            );

            $elements = array_map(static function ($data) use ($elementClass, $propertiesMapping) {
                return $elementClass !== null
                    ? $this->getSerializerInstance()->deserializeFromArray($data, $elementClass, $propertiesMapping)
                    : $data;
            }, $data['data']);


            $responseClass = $responseClass ?? ApiResponse::class;
            $responseObject = new $responseClass($elements);
        } catch (JsonException $exception) {
            throw new RuntimeException('Invalid JSON response from server: ' . $exception->getMessage());
        }
        return $responseObject;
    }

    /**
     * @template T
     * @psalm-param class-string<T> $class
     * @return T
     * @throws JsonException
     */
    public function handleResponse(
        $response,
        string $class,
        array $propertiesMapping = [],
        array $excludedKeys = [],
    ) {
        try {
            $responseObject = $this->getSerializerInstance()
                ->deserializeFromJson(
                    (string) $response->getBody()->getContents(),
                    $class,
                    $propertiesMapping,
                    excludedKeys: $excludedKeys,
                );
        } catch (JsonException $exception) {
            throw new RuntimeException('Invalid JSON response from server: ' . $exception->getMessage());
        }
        return $responseObject;
    }

    protected function get(
        UriInterface|string $uri,
        array $options = [],
        int $retryCount = self::DEFAULT_REQUEST_ATTEMPTS_COUNT,
        int $sleepOnRetrySeconds = self::DEFAULT_SLEEP_SECONDS,
    ): ResponseInterface {
        return $this->sendRequest(
            function () use ($uri, $options) {
                return $this->client->get($uri, $this->beforeRequest('get', $uri, $options));
            },
            $retryCount,
            $sleepOnRetrySeconds,
        );
    }

    protected function post(
        UriInterface|string $uri,
        array $options = [],
        int $retryCount = self::DEFAULT_REQUEST_ATTEMPTS_COUNT,
        int $sleepOnRetrySeconds = self::DEFAULT_SLEEP_SECONDS,
    ): ResponseInterface {
        return $this->sendRequest(
            function () use ($uri, $options) {
                return $this->client->post($uri, $this->beforeRequest('post', $uri, $options));
            },
            $retryCount,
            $sleepOnRetrySeconds,
        );
    }

    protected function delete(
        UriInterface|string $uri,
        array $options = [],
        int $retryCount = self::DEFAULT_REQUEST_ATTEMPTS_COUNT,
        int $sleepOnRetrySeconds = self::DEFAULT_SLEEP_SECONDS,
    ): ResponseInterface {
        return $this->sendRequest(
            function () use ($uri, $options) {
                return $this->client->delete($uri, $this->beforeRequest('delete', $uri, $options));
            },
            $retryCount,
            $sleepOnRetrySeconds,
        );
    }

    protected function patch(
        UriInterface|string $uri,
        array $options = [],
        int $retryCount = self::DEFAULT_REQUEST_ATTEMPTS_COUNT,
        int $sleepOnRetrySeconds = self::DEFAULT_SLEEP_SECONDS,
    ): ResponseInterface {
        return $this->sendRequest(
            function () use ($uri, $options) {
                return $this->client->patch($uri, $this->beforeRequest('patch', $uri, $options));
            },
            $retryCount,
            $sleepOnRetrySeconds,
        );
    }

    private function sendRequest(
        callable $callback,
        int $maxRetryCount = self::DEFAULT_REQUEST_ATTEMPTS_COUNT,
        int $sleepOnRetrySeconds = self::DEFAULT_SLEEP_SECONDS,
    ): ResponseInterface {
        $attempt = 0;
        $needRetry = true;

        do {
            $attempt++;
            try {
                /** @var ResponseInterface $response */
                $response = $callback();
                $needRetry = false;
            } catch (GuzzleException $exception) {
                if (
                    $attempt >= $maxRetryCount
                    || !in_array(
                        $exception->getCode(),
                        [Response::HTTP_UNPROCESSABLE_ENTITY, Response::HTTP_FORBIDDEN, Response::HTTP_NOT_FOUND],
                        true,
                    )
                ) {
                    throw $exception;
                }

                usleep($sleepOnRetrySeconds * 1000);
            }
        } while ($needRetry);

        return $response;
    }

    /**
     * @template T
     * @param callable(int, int): T $request
     * @param int $page
     * @param int $limit
     * @return T
     */
    public function walkPages(callable $request, int $page = 1, int $limit = 1000)
    {
        $result = null;
        do {
            $data = $request($page, $limit);

            if ($data instanceof MergableApiResponseInterface) {
                $respondedCount = $data->count();
                $result = $result ? $data->mergeData($result) : $data;
            } else {
                $respondedCount = count($data);
                $result = $result === null ? $data : array_merge($data, $result);
            }
            $page++;
        } while ($respondedCount === $limit);

        return $result;
    }

    protected function getSerializerInstance(): SerializerInterface
    {
        return app(Serializer::class);
    }

    protected function beforeRequest(string $method, string $uri, array $options): array
    {
        foreach ($this->config->requestMiddlewares as $item) {
            /** @var class-string<RequestMiddlewareInterface> $middleware */
            $middleware = app($item);

            $options = $middleware->handle($method, $uri, $options);
        }

        return $options;
    }
}
