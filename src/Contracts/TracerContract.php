<?php

declare(strict_types=1);

namespace Lelastico\Contracts;

interface TracerContract
{
    public function start(string $measurementName): bool;

    public function finish(int $took): void;
}
