# Upgrade guide

## 0.3.0

> You are upgrading a Laravel application from `pion/laravel-lelastico` v0.2.4 to v0.3.0. Inspect the application before you change it. Update the Composer requirements, published configuration, Lelastico builders, query classes, request filters, index classes, service bindings, and `elastic:indices` command calls as described below. Update imports and method type declarations. Do not change application behavior unless this guide requires it. Run the application test suite after the upgrade and report each changed file and each item that needs manual review.

This release has breaking changes. It requires PHP 8.1 and Laravel 10.

Run the search commands in this guide from the application root. They exclude Composer dependencies, so they show only application code.

### 1. Update Composer dependencies

Update the application to PHP 8.1 or later. Update Laravel packages to version 10. Install Lelastico 0.3.0 and allow its required `erichard/elasticsearch-query-builder` 3.0.0-beta dependency.

Remove the custom Composer repository for `pionl/elasticsearch-query-builder` if the application has it. Lelastico now uses the published package version.

Then run:

```sh
composer update pion/laravel-lelastico erichard/elasticsearch-query-builder --with-all-dependencies
```

Read the [elasticsearch-query-builder 3.0 upgrade guide](https://github.com/erichard/elasticsearch-query-builder/blob/main/UPGRADE-3.0.md). Update all direct uses of that package.

Find old dependency settings:

```sh
rg -n --glob '!vendor/**' 'pion/laravel-lelastico|elasticsearch-query-builder|pionl/elasticsearch-query-builder|"php"|illuminate/' .
```

### 2. Publish and update configuration

Publish the new configuration file. Compare it with the existing `config/lelastico.php` file. Keep the application hosts, prefix, and index classes.

Remove the `service` configuration key. Bind a custom index service in an application service provider instead:

```php
use Lelastico\Contracts\IndicesServiceContract;

public function register(): void
{
    $this->app->singleton(IndicesServiceContract::class, YourIndicesService::class);
}
```

Move `log_measurement` to `tracing.enabled`. The v0.3.0 configuration uses `ELASTICSEARCH_LOG_MEASUREMENT` for this value.

```php
'tracing' => [
    'enabled' => env('ELASTICSEARCH_LOG_MEASUREMENT', true),
    'replace' => false,
    'tracers' => [],
],
```

Use `tracing.replace` to replace built-in tracers. Use `tracing.tracers` to add tracer classes.

Find old configuration and custom service bindings:

```sh
rg -n --glob '!vendor/**' 'log_measurement|ELASTICSEARCH_LOG_MEASUREMENT|ELASTICSEARCH_TRACING|lelastico\.service|IndicesServiceContract|IndicesService' config app bootstrap tests .env.example
```

### 3. Update search builders

`AbstractBuilder` is replaced by `AbstractSearchBuilder`. The constructor now receives `BuilderDependencies`. Do not inject `Request`, `LoggerInterface`, or `Repository` into the builder constructor.

Replace `createIndex(): AbstractElasticIndex` with `getIndexClass(): string`. Return the index class name. Lelastico creates the index through the Laravel container.

```php
use Lelastico\Search\Query\AbstractSearchBuilder;

class ProductSearchBuilder extends AbstractSearchBuilder
{
    protected function getIndexClass(): string
    {
        return ProductIndex::class;
    }
}
```

The builder no longer executes searches. Inject `Lelastico\Services\LelasticoService` and call its methods:

```php
$results = $lelasticoService->get($builder);
$paginator = $lelasticoService->paginate($builder, $request->url(), $request->query());
$aggregations = $lelasticoService->aggregations($builder);
```

Replace builder calls to `paginate()` with `LelasticoService::paginate()`. Replace custom query logging with tracing and logging configuration. The `logQuery` method no longer exists. `getResultsFromHits()` is now public.

If the builder uses `HasSorting`, add sorting in the builder. `AbstractSearchBuilder` does not add it automatically. Replace direct use of the old `$filter` property with `$query`, which is a `BoolQuery`.

Find old builders and their execution calls:

```sh
rg -n --glob '*.php' --glob '!vendor/**' 'AbstractBuilder|createIndex\(|->paginate\(|->logQuery\(|\$filter|HasSorting|getResultsFromHits' app src tests
```

### 4. Update query and request-filter classes

Replace `AbstractQuery::createFilters(): array` with `createQuery(): ?QueryInterface`. Return one query object. Wrap multiple query objects in `BoolQuery`.

```php
use Erichard\ElasticQueryBuilder\Contracts\QueryInterface;
use Erichard\ElasticQueryBuilder\Query\BoolQuery;
use Erichard\ElasticQueryBuilder\Query\TermQuery;

public function createQuery(): ?QueryInterface
{
    return new BoolQuery(filter: [
        new TermQuery('status', 'published'),
    ]);
}
```

Rename `GivenFilters` to `GivenFiltersQuery`.

`RequestFilter` and `RequestQueryFilter` now require a `Closure`. Replace a query class string, or a `[query class, field]` array, with a closure that creates and returns the query.

```php
use Erichard\ElasticQueryBuilder\Query\TermQuery;

$filters->addQueryFilters([
    'status' => fn (mixed $value) => new TermQuery('status', $value),
]);
```

Update overridden methods to their new types. In particular, `Filters::apply()` and `Filters::addQueryFilters()` return `void`, and `RequestFilter::convertValue()` has the signature `convertValue(mixed $value): mixed`.

Find old query and filter APIs:

```sh
rg -n --glob '*.php' --glob '!vendor/**' 'createFilters\(|GivenFilters|RequestFilter|RequestQueryFilter|addQueryFilters\(|convertValue\(|class-string.*Query|TermFilter|MatchFilter' app src tests
```

### 5. Update index classes and services

`AbstractElasticIndex` now requires an `Elasticsearch\Client` constructor argument. Do not pass `null` or create the index with `new`. Resolve index classes through Laravel dependency injection.

Update the service namespace from `Lelastico\IndicesService` to `Lelastico\Services\IndicesService`.

Add return types and parameter types to all application classes that extend Lelastico classes or implement its contracts. Lelastico now uses strict types.

Find index construction and old service imports:

```sh
rg -n --glob '*.php' --glob '!vendor/**' 'new [A-Za-z_\\]+Index\(|extends AbstractElasticIndex|Lelastico\\IndicesService|IndicesServiceContract' app src tests
```

### 6. Update commands

Replace the old `--only` option with the optional index argument:

```sh
# Before
php artisan elastic:indices --only=products

# After
php artisan elastic:indices products
```

The existing `--force`, `--delete`, and `--skip-settings-update` options are still available. Their short forms are `-f`, `-d`, and `-s`.

The release also adds this command:

```sh
php artisan elastic:clear [index]
```

Find old command calls in code, scripts, and documentation:

```sh
rg -n --glob '!vendor/**' 'elastic:indices|--only=' .
```

### 7. Verify the upgrade

Clear cached configuration after you update the configuration file. Run the application tests and exercise a normal search, paginated search, aggregations, and index update command. Check application logs and tracers for Elasticsearch failures.
