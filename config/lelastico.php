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
    'debugbar_log' => app()->isLocal() && function_exists('debugbar'),
    'service' => IndicesService::class,
];
