<?php

declare(strict_types=1);

use Symplify\EasyCodingStandard\Config\ECSConfig;
return ECSConfig::configure()
    ->withPaths([
        __DIR__ . '/src',
        // __DIR__ . '/tests',
        __DIR__ . '/config',
        __DIR__ . '/ecs.php',
    ])
    ->withParallel()
    ->withPreparedSets(psr12: true, common: true, cleanCode: true);
