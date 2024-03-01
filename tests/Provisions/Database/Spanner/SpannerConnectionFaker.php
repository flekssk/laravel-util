<?php

declare(strict_types=1);

namespace Tests\Provisions\Database\Spanner;

use Illuminate\Support\Facades\DB;

class SpannerConnectionFaker
{
    public static function fake(): void
    {
        DB::extend(
            'base-spanner',
            static function ($config) {
                return new BaseSpannerConnection(
                    $config['instance'],
                    $config['database'],
                    $config['prefix'],
                    $config,
                );
            }
        );
        config()->set('database.default', 'base-spanner');
        config()->set(
            'database.connections.base-spanner',
            [
                'driver' => 'base-spanner',
                'instance' => 'local-instance',
                'database' => 'local-database',
                'prefix' => 'test',
                'client' => [
                    'projectId' => 'local-project',
                    'hasEmulator' => true,
                    'emulatorHost' => 'localhost',
                ],
                'session_pool' => [
                    'minSessions' => 10,
                    'maxSessions' => 1000,
                ],
                'sessionNotFoundErrorMode' => 'CLEAR_SESSION_POOL',
                'convert_string_uuids_params_to_bytes_in_queries' => false,
                'convert_hex_string_len_32_params_to_bytes_in_queries' => false,
                'sessionPoolDriver' => 'array',
            ]
        );
    }
}