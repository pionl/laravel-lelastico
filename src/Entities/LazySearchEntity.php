<?php

declare(strict_types=1);

namespace Lelastico\Entities;

use GuzzleHttp\Ring\Future\FutureArrayInterface;
use Lelastico\Contracts\TracerContract;
use Lelastico\Search\Query\AbstractSearchBuilder;

class LazySearchEntity
{
    public function __construct(
        public readonly string $measurementName,
        public readonly array $query,
        public readonly AbstractSearchBuilder $builder,
        /**
         * @var array<TracerContract>
         */
        public readonly array $tracers,
        public readonly FutureArrayInterface $result,
    ) {
    }
}
