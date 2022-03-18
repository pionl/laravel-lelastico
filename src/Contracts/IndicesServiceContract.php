<?php

declare(strict_types=1);

namespace Lelastico\Contracts;

interface IndicesServiceContract
{
    public function getAvailableIndices(): array;
}
