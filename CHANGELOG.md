# v0.3

## New features

- Aggregations -> `$builder->aggregations()`. Returns raw data.

## Breaking changes

- **PHP version 8.1**
- `RequestQueryFilter` does not support `[TermFilter::class, 'test'], TermFilter::class` syntax. Always use closure that will return desired builder query.
- Filters `addQueryFilters` method does not accept `[TermFilter::class, 'test'], TermFilter::class` syntax. Use same closure as for `RequestQueryFilter`
- [elasticsearch-query-builder](https://github.com/erichard/elasticsearch-query-builder) has been updated. [Upgrade your code](https://github.com/erichard/elasticsearch-query-builder/blob/main/UPGRADE-3.0.md).
- `AbstractElasticIndex` does not accept null. Use depedency injection.
- `IndicesService` moved to `Services` namespace
- `AbstractQuery->createFilters` has been renamed to createQuery
- `AbstractQuery->createFilters` returns instead of `array` `?QueryInterface`. If you were returning multiple queries than wrap it in BoolQuery (as the package previously did). This was made for easier usability of queries.
```php
public function createQuery(): ?QueryInterface
{
    return new BoolQuery(filter: [
        new TermFilter('...', '...'),
    ]);
}
```
- `RequestFilter` -> does not accepts string to create a query -> always use closure to force type strict.
    - `$queryClass` property renamed to `$createQuery`.
- `GivenFilters` renamed to `GivenFiltersQuery`
- **Type strict added to all class:** If you are extending any functions from builder and other classes, ensure that methods and their typehints are updated  (use reactor, phpcsfixer, to auto fix).
    - Builder
      - logQuery does not exists, see `logging` / `tracing`  Check the new usage documentation.
      - **getResultsFromHits** changed from protected to public
      - `pagination` has been removed. Refactor to `buildForPagination`. Check the new usage documentation.
    - Filters
      - apply -> `void` on return
    - Queries
      - convertValue -> `convertValue(mixed $value): mixed`
    
- `AbstractBuilder` 
  - constructor now accepts 
  - `$query` property removed
  - `createIndex` renamed to `getIndexClass` and now returns class string - index will be built with dependency injection.

- **Config** - `service` removed in favor of `IndicesServiceContract` and in your app service
```php
register ()  {
    parent::register();
    
    $this->app->singleton(IndicesServiceContract::class, YourIndicesService::class);
}
```
- **Config** - `log_measurement` moved to `tracing.enabled` and env renamed from `ELASTICSEARCH_LOG_MEASUREMENT` to `ELASTICSEARCH_TRACING`


# TODO

- sort
- rewrite "request"
- rename convention query to something else because of the package naming convention change

```

    /**
     * @var array<string, string>
     */
    private array $availableTraits;
   // Add sort if enabled
        $this->availableTraits = class_uses($this) ?? [];
        // TODO move out
        $this->hasSorting = array_key_exists(HasSorting::class, $this->availableTraits);
        $this->index = $this->dependencies->container->make($this->getIndexClass());
```
