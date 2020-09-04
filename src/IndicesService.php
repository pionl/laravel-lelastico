<?php

namespace Lelastico;

use Illuminate\Contracts\Config\Repository;
use Lelastico\Contracts\IndicesServiceContract;

class IndicesService implements IndicesServiceContract
{
    protected Repository $configRepository;

    public function __construct(Repository $configRepository)
    {
        $this->configRepository = $configRepository;
    }

    public function getAvailableIndices(): array
    {
        return $this->configRepository->get(LelasticoServiceProvider::CONFIG_NAME.'.indices', []);
    }
}
