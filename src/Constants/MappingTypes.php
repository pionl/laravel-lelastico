<?php

declare(strict_types=1);

namespace Lelastico\Constants;

final class MappingTypes
{
    /**
     * Keyword for numeric value is faster (does not support range, not required for id).
     *
     * @var array<string, int|string>
     */
    public const KEYWORD = [
        'type' => 'keyword',
        'ignore_above' => 200,
    ];

    /**
     * @var array<string, string>
     */
    public const TEXT = [
        'type' => 'text',
    ];

    /**
     * Use field.raw for sorting / filter on exact value, field for match.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/multi-fields.html
     * @var array<string, string|array<string, array<string, string>>>
     */
    public const TEXT_WITH_KEYWORD = [
        'type' => 'text',
        'fields' => [
            'raw' => [
                'type' => 'keyword',


            ],
        ],
    ];

    /**
     * @var array<string, string>
     */
    public const SHORT = [
        'type' => 'short',
    ];

    /**
     * Use field.raw for sorting / filter on exact value, field for match.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/multi-fields.html
     * @var array<string, string|array<string, array<string, string>>>
     */
    public const SHORT_WITH_KEYWORD = [
        'type' => 'short',
        'fields' => [
            'raw' => [
                'type' => 'keyword',


            ],
        ],
    ];

    /**
     * @var array<string, string>
     */
    public const LONG = [
        'type' => 'long',
    ];

    /**
     * Use field.raw for sorting / filter on exact value, field for match.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/multi-fields.html
     * @var array<string, string|array<string, array<string, string>>>
     */
    public const LONG_WITH_KEYWORD = [
        'type' => 'long',
        'fields' => [
            'raw' => [
                'type' => 'keyword',


            ],
        ],
    ];

    /**
     * @var array<string, string>
     */
    public const INTEGER = [
        'type' => 'integer',
    ];

    /**
     * Use field.raw for sorting / filter on exact value, field for match.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/multi-fields.html
     * @var array<string, string|array<string, array<string, string>>>
     */
    public const INTEGER_WITH_KEYWORD = [
        'type' => 'integer',
        'fields' => [
            'raw' => [
                'type' => 'keyword',


            ],
        ],
    ];

    /**
     * @var array<string, string>
     */
    public const DATE = [
        'type' => 'date',
    ];

    /**
     * @var array<string, string>
     */
    public const BOOLEAN = [
        'type' => 'boolean',
    ];

    /**
     * @var array<string, string>
     */
    public const FLOAT = [
        'type' => 'float',
    ];

    public static function textWithAnalyzer(string $analyzer, string $searchAnalyzer = 'standard'): array
    {
        return [
            'type' => 'text',
            'analyzer' => $analyzer,
            'search_analyzer' => $searchAnalyzer,
        ];
    }
}
