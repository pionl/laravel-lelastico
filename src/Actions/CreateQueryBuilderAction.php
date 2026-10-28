<?php

declare(strict_types=1);

namespace Lelastico\Actions;

use Erichard\ElasticQueryBuilder\QueryBuilder;

class CreateQueryBuilderAction
{
    public function execute(): QueryBuilder
    {
        return new QueryBuilder();
    }
}
