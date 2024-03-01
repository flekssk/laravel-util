<?php

namespace Tests\Provisions\Database\Spanner;

use Tests\Provisions\Database\Spanner\Query\SpannerQueryBuilder;
use Tests\Provisions\Database\Spanner\Query\SpannerQueryGrammar;
use Tests\Provisions\Database\Spanner\Schema\BaseSpannerSchemaBuilder;
use Tests\Provisions\Database\Spanner\Schema\BaseSpannerSchemaGrammar;
use Closure;
use Colopl\Spanner\Connection as BaseConnection;
use Google\Cloud\Spanner\Session\SessionPoolInterface;
use Illuminate\Support\Str;
use Psr\Cache\CacheItemPoolInterface;
use Ramsey\Uuid\Uuid;

class BaseSpannerConnection extends BaseConnection
{
    /**
     * Create a new spanner database connection instance.
     *
     * @param string $instanceId instance ID
     * @param string $databaseName
     * @param string $tablePrefix
     * @param array $config
     * @param CacheItemPoolInterface $authCache
     * @param SessionPoolInterface $sessionPool
     */
    public function __construct(
        string $instanceId,
        string $databaseName,
        $tablePrefix = '',
        array $config = [],
        CacheItemPoolInterface $authCache = null,
        SessionPoolInterface $sessionPool = null,
    ) {
        parent::__construct($instanceId, $databaseName, $tablePrefix, $config, $authCache, $sessionPool);
    }

    /**
     * Wraps runQueryCallback with profiling queries via newrelic
     *
     * @param  string    $query
     * @param  array     $bindings
     * @param  Closure  $callback
     * @return mixed
     */
    protected function runQueryCallbackProfiled($query, $bindings, Closure $callback)
    {
        if (extension_loaded('newrelic')) { // Ensure PHP agent is available
            $result = newrelic_record_datastore_segment(function () use ($query, $bindings, $callback) {
                return parent::runQueryCallback($query, $bindings, $callback);
            },
            [
                'product'      => 'MySQL',
                'host'         => '',
                'portPathOrId' => $this->getDatabaseName(),
                'databaseName' => $this->getDatabaseName(),
                'query'        => $query,
            ]);
            return $result;
        }
        return parent::runQueryCallback($query, $bindings, $callback);
    }

    /**
     * Handle "session not found" errors
     *
     * @param  string    $query
     * @param  array     $bindings
     * @param  Closure  $callback
     * @return mixed
     */
    protected function runQueryCallback($query, $bindings, Closure $callback)
    {
        return $this->runQueryCallbackProfiled($query, $bindings, $callback);
    }

    protected static $spannerEmulatorLock = null;

    public function query(): SpannerQueryBuilder
    {
        $queryGrammar = $this->getQueryGrammar();
        assert($queryGrammar instanceof SpannerQueryGrammar);
        return new SpannerQueryBuilder($this, $queryGrammar, $this->getPostProcessor());
    }

    public function getSchemaBuilder(): BaseSpannerSchemaBuilder
    {
        if ($this->schemaGrammar === null) {
            $this->useDefaultSchemaGrammar();
        }

        return new BaseSpannerSchemaBuilder($this);
    }

    protected function getDefaultQueryGrammar(): SpannerQueryGrammar
    {
        return new SpannerQueryGrammar();
    }

    protected function getDefaultSchemaGrammar(): BaseSpannerSchemaGrammar
    {
        return new BaseSpannerSchemaGrammar();
    }

    /**
     * Prepare the query bindings for execution.
     *
     * Supports optional conversion of all uuids into bytes
     *
     * @param  array  $bindings
     * @return array
     */
    public function prepareBindings(array $bindings)
    {
        $bindings = parent::prepareBindings($bindings);

        $uuidCasts = $this->getConfig('convert_string_uuids_params_to_bytes_in_queries');
        $hexStringUuidCasts = $this->getConfig('convert_hex_string_len_32_params_to_bytes_in_queries');

        if ($uuidCasts || $hexStringUuidCasts) {
            // dynamic replacement of string uuids should be enabled per database
            // replaces all uuids params indiscriminately in case of simple queries, like
            // DB::table('')->where
            // DB::statement('', [])
            foreach ($bindings as $key => $value) {
                if (is_string($value)) {
                    $strLen = strlen($value);
                    if ($uuidCasts && $strLen === 36 && Str::isUuid($value)) {
                        $bindings[$key] = new SpannerBinaryUuid($value);
                    }

                    // process uuids without dashes
                    if ($hexStringUuidCasts && $strLen === 32 && preg_match('/^[\da-f]{32}$/iD', $value) > 0) {
                        $bindings[$key] = new SpannerBinaryUuid(Uuid::fromString($value)->toString());
                    }
                }
            }
        }

        return $bindings;
    }
}
