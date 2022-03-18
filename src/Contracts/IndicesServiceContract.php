<?php

declare(strict_types=1);

namespace Lelastico\Contracts;

use Lelastico\Indices\AbstractElasticIndex;

interface IndicesServiceContract
{
    /**
     * @return array<class-string<AbstractElasticIndex>>
     */
    public function getAvailableIndices(): array;
}
