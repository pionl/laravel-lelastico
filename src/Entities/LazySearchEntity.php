<?php

declare(strict_types=1);

namespace Lelastico\Entities;

use GuzzleHttp\Ring\Future\FutureArrayInterface;
use Lelastico\Contracts\TracerContract;
use Lelastico\Search\Query\AbstractSearchBuilder;

class LazySearchEntity
{
    public function __construct(
        public string $measurementName,
        public array $query,
        public AbstractSearchBuilder $builder,
        /**
         * @var array<TracerContract>
         */
        public array $tracers,
        public FutureArrayInterface $result,
    ) {
    }
}
