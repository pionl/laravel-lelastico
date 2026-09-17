<?php

declare(strict_types=1);

use Illuminate\Support\Str;

return [
    // https://www.elastic.co/guide/en/elasticsearch/client/php-api/current/configuration.html
    'hosts' => explode(';', (string) env('ELASTICSEARCH_HOSTS', 'localhost:9200')),
    'prefix' => str_replace('-', '_', Str::slug(env('APP_NAME', ''), '_')),

    'log_failure' => true,
    'log_debug' => env('ELASTICSEARCH_LOG_DEBUG', env('APP_ENV') === 'local'),

    'indices' => [],

    /**
     * Logging performance using "tracers".
     */
    'tracing' => [
        'enabled' => env('ELASTICSEARCH_LOG_MEASUREMENT', true),
        /**
         * Do you want to replace built-in tracers? By, default you are extending them.
         */
        'replace' => false,
        'tracers' => [],
    ],
];
