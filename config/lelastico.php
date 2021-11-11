<?php

use Illuminate\Support\Str;
use Lelastico\IndicesService;

return [
    // https://www.elastic.co/guide/en/elasticsearch/client/php-api/current/configuration.html
    'hosts' => explode(';', env('ELASTICSEARCH_HOSTS', 'localhost:9200')),
    'prefix' => str_replace(
        '-', '_', Str::slug(env('APP_NAME', ''), '_')
    ),
    'indices' => [],
    'log_failure' => true,
    'log_debug' => env('ELASTICSEARCH_LOG_DEBUG', 'local' === env('APP_ENV')),
    'log_measurement' => env('ELASTICSEARCH_LOG_MEASUREMENT', false),
    'service' => IndicesService::class,
];
