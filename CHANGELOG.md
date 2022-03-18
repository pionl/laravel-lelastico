# v0.3

## Breaking changes

- `RequestQueryFilter` does not support `[TermFilter::class, 'test'], TermFilter::class` syntax. Always use closure that will return desired builder query.
- Filters `addQueryFilters` method does not accept `[TermFilter::class, 'test'], TermFilter::class` syntax. Use same closure as for `RequestQueryFilter`
- [elasticsearch-query-builder](https://github.com/erichard/elasticsearch-query-builder) has been updated. [Upgrade your code](https://github.com/erichard/elasticsearch-query-builder/blob/main/UPGRADE-3.0.md).
