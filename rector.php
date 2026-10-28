<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/config',
        __DIR__ . '/src',
    ])
    ->withPhpSets(php85: true)
    ->withPreparedSets(codeQuality: true)
    ->withImportNames();
