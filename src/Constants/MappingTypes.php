<?php

namespace Lelastico\Constants;

final class MappingTypes
{
    /**
     * Keyword for numeric value is faster (does not support range, not required for id).
     */
    const KEYWORD = ['type' => 'keyword', 'ignore_above' => 200];
    const TEXT = ['type' => 'text'];
    /**
     * Use field.raw for sorting / filter on exact value, field for match.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/multi-fields.html
     */
    const TEXT_WITH_KEYWORD = ['type' => 'text', 'fields' => ['raw' => ['type' => 'keyword']]];
    const SHORT = ['type' => 'short'];
    /**
     * Use field.raw for sorting / filter on exact value, field for match.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/multi-fields.html
     */
    const SHORT_WITH_KEYWORD = ['type' => 'short', 'fields' => ['raw' => ['type' => 'keyword']]];
    const LONG = ['type' => 'long'];
    /**
     * Use field.raw for sorting / filter on exact value, field for match.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/multi-fields.html
     */
    const LONG_WITH_KEYWORD = ['type' => 'long', 'fields' => ['raw' => ['type' => 'keyword']]];
    const INTEGER = ['type' => 'integer'];
    /**
     * Use field.raw for sorting / filter on exact value, field for match.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/multi-fields.html
     */
    const INTEGER_WITH_KEYWORD = ['type' => 'integer', 'fields' => ['raw' => ['type' => 'keyword']]];
    const DATE = ['type' => 'date'];
    const BOOLEAN = ['type' => 'boolean'];
    const FLOAT = ['type' => 'float'];

    public static function textWithAnalyzer(string $analyzer, string $searchAnalyzer = 'standard')
    {
        return ['type' => 'text', 'analyzer' => $analyzer, 'search_analyzer' => $searchAnalyzer];
    }
}
