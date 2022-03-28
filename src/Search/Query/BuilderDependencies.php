<?php

declare(strict_types=1);

namespace Lelastico\Search\Query;

use Illuminate\Contracts\Container\Container;
use Lelastico\Actions\CreateQueryBuilderAction;

class BuilderDependencies
{
    public function __construct(
        public readonly Container $container,
        public readonly CreateQueryBuilderAction $createQueryBuilderAction,
    ) {
    }
}