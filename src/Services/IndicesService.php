<?php

declare(strict_types=1);

namespace Lelastico\Services;

use Lelastico\Configs\LelasticoConfig;
use Lelastico\Contracts\IndicesServiceContract;

class IndicesService implements IndicesServiceContract
{
    public function __construct(protected LelasticoConfig $config)
    {
    }

    public function getAvailableIndices(): array
    {
        return $this->config->getIndices();
    }
}
