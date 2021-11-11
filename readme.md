# Lelastico - easier elastic search for Laravel

[![Total Downloads](https://poser.pugx.org/pion/laravel-lelastico/downloads?format=flat)](https://packagist.org/packages/pion/laravel-elastico)
[![Latest Stable Version](https://poser.pugx.org/pion/laravel-lelastico/v/stable?format=flat)](https://packagist.org/packages/pion/laravel-elastico)
[![Latest Unstable Version](https://poser.pugx.org/pion/laravel-lelastico/v/unstable?format=flat)](https://packagist.org/packages/pion/laravel-elastico)
[![License](https://poser.pugx.org/pion/laravel-lelastico/license)](https://packagist.org/packages/pion/laravel-lelastico)

## Introduction

Focus of this library is to make it easier to manage elastic indices (wit mappings / settings), create reusable query building (manual or from request).

* Adds ability to manage elasticsearch indices with wrapper class which will help to you create/update index write documents (with bulk mode) etc.
* Adds ability to create a query builder with query filters for each index.
* Adds ability to build a query builder from request data (reusable component).

## Requirements

- Composer
- PHP 7.4+

## Installation

**1. Add custom repository to composer.json**

```
"repositories": {
    {
        "type": "git",
        "url": "https://github.com/pionl/elasticsearch-query-builder.git"
    }
}
```

**2. Install via composer**

```
composer require pion/laravel-lelastico
```
    
**3. Add the service provider (Laravel 5.4 and below - supports Auto discovery)**

```php
\Lelastico\LelasticoServiceProvider::class,
```    

## Dependencies

- [erichard/elasticsearch-query-builder](https://github.com/erichard/elasticsearch-query-builder) - at this moment forked version with additional functions.
- [elasticsearch/elasticsearch](https://github.com/elasticsearch/elasticsearch), version 7 and above (tested with 7.5).

## Usage

**Set elastic hosts**

> For development, you can use default value in the config without password: localhost:9200

Use `ELASTICSEARCH_HOSTS` environment for setting elastic search hosts. [Format](https://www.elastic.co/guide/en/elasticsearch/client/php-api/current/configuration.html).

**Resolve elastic search client**

```
$client = resolve(\Elasticsearch\Client::class);
```

```
$client = $container->make(\Elasticsearch\Client::class);
``` 

**Mapping types constants**

Property mappings types using constants like:

- MappingTypes::KEYWORD
- MappingTypes::TEXT
- MappingTypes::TEXT_WITH_KEYWORD
- MappingTypes::SHORT
- MappingTypes::SHORT_WITH_KEYWORD
- MappingTypes::LONG
- MappingTypes::LONG_WITH_KEYWORD
- MappingTypes::INTEGER
- MappingTypes::INTEGER_WITH_KEYWORD
- MappingTypes::DATE
- MappingTypes::BOOLEAN
- MappingTypes::FLOAT
- MappingTypes::textWithAnalyzer(string $analyzer, string $searchAnalyzer), builds

**Adding indices**

1. Create your indices by extending `AbstractElasticIndex` and implementing `createIndexName` for elastic index
    - Implement `propertyMappings` for custom mappings.
    
    ```php
    protected function propertyMappings(): array
    {
      return [
          'id' => MappingTypes::KEYWORD,
          'name' => MappingTypes::TEXT_WITH_KEYWORD,
          'is_verified' => MappingTypes::BOOLEAN,
          'email' => MappingTypes::textWithAnalyzer('fulltext'),
          'created_at' => MappingTypes::DATE,
          'updated_at' => MappingTypes::DATE,
          'deleted_at' => MappingTypes::DATE,
      ];
    }   
    ```
    - Implement `settings` for custom index settings
    
    ```php
    protected function settings(): array
    {
      // Add support for partial text search
      return [
          'index' => [
              'analysis' => [
                  'filter' => [
                      'fulltext_filter' => [
                          // Always from start of beginning of each token
                          'type' => 'edge_ngram',
                          'min_gram' => 3,
                          'max_gram' => 20,
                      ],
                  ],
                  'analyzer' => [
                      'fulltext' => [
                          'type' => 'custom',
                          'tokenizer' => 'standard',
                          'filter' => ['lowercase', 'fulltext_filter'],
                      ],
                  ],
              ],
          ],
      ];
    }
    ```
2. Create or update `lelastico.php` config with indices classes.

    ```php
    return [
        'indices' => [
            \App\ElasticSearch\Indices\UsersIndex::class,
        ],
    ];
    ```
3. Update or create indices in elastic (stores settings / mapping) using `php artisan elastic:indices`

    ```
    Updates the elastic indices
            --only="only", handle only given index
            --f, will delete the index and data. Will new index with mappings
            --d, will delete the index and data
            --skip-settings-update, when upadting, the index is closed / opened due the settings update. You can skip it
            by provided this option.
    ```

**Sorting**

**By default we are sorting by `_id` after any HasSorting logic to ensure that pagination is correct.** 

You can turn this feature by using `$builder->setSortById(false);`

To enable sortable behavior add `HasSorting` trait to your instance of `AbstractBuilder` and implement method `allowedSortFields`.

```
/**
 * Allowed fields for sorting.
 *
 * Key is the name of the field in the query.
 * Value is the name of the field in the index.
 *
 * @return array
 */
public function allowedSortFields(): array
{
    return [
        'goals' => 'goals_count',
        'minutes' => 'played_minutes',
    ];
}
```

With sorting enabled you can sort the results using `sort` request query parameter. This parameter accepts list of fields for sorting in format `{field_name}:{sort_direction}`.

Available directions for sorting are `asc` and `desc` and if not specified the default sort direction is set to `asc`.

**Examples:**

`sort[]=goals`

`sort[]=goals:asc&sort[]=minutes:desc`

## Configuration

- `log_measurement` Logs every query to log (default false). You can use `ELASTICSEARCH_LOG_MEASUREMENT` env.
- `log_debug` Debug logs every query data to a log (true in local environment). You can use `ELASTICSEARCH_LOG_DEBUG` env.
- `service` Enables to change available indices (implement IndicesServiceContract or  extend IndicesService)
- `prefix` Used prefix for index names - uses APP_NAME and replace '-' to '_', converts name to slug variant.
- `hosts` A list of IPS for your elastic search - https://www.elastic.co/guide/en/elasticsearch/client/php-api/current/configuration.html. Use `;` separator. Default localhost:9200.

## TODO

- improve documentation
- add `make` console.

## Changelog

Can be found in [releases](https://github.com/pionl/laravel-elastico/releases).

## Contribution or extending

See [CONTRIBUTING.md](CONTRIBUTING.md) for how to contribute changes. All contributions are welcome.

## Sponsors

> This library was created and improved thanks to clients projects.

* [Azzurro, Travel agency](https://www.azzurro.cz)
* [Certisys, s.r.o.](https://certisys.cz)

## Copyright and License

[laravel-elastico](https://github.com/pionl/laravel-elastico)
was written by [Martin Kluska](http://kluska.cz) and is released under the 
[MIT License](LICENSE.md).

Copyright (c) 2020 Martin Kluska
