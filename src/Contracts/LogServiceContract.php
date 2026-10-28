<?php

declare(strict_types=1);

namespace Lelastico\Contracts;

use Exception;

interface LogServiceContract
{
    public function logFailure(string $measurementName, string $index, array $query, Exception $exception): void;

    public function logResult(string $measurementName, string $index, array $query, array $result, int $took): void;
}
