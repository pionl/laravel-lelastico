<?php

declare(strict_types=1);

use Symplify\EasyCodingStandard\Config\ECSConfig;
use Symplify\EasyCodingStandard\ValueObject\Option;
use Symplify\EasyCodingStandard\ValueObject\Set\SetList;

return static function (ECSConfig $config): void {
    $config->import(SetList::PSR_12);
    $config->import(SetList::SYMPLIFY);
    $config->import(SetList::COMMON);
    $config->import(SetList::CLEAN_CODE);

    $parameters = $config->parameters();

    $parameters->set(Option::PARALLEL, true);

    $parameters->set(Option::PATHS, [
        __DIR__ . '/src',
        // __DIR__ . '/tests',
        __DIR__ . '/config',
        __DIR__ . '/ecs.php',
    ]);
};
