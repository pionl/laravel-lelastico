<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Core\Configuration\Option;
use Rector\Core\ValueObject\PhpVersion;
use Rector\Php74\Rector\Property\RestoreDefaultNullToNullableTypePropertyRector;
use Rector\Php74\Rector\Property\TypedPropertyRector;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;
use Rector\Strict\Rector\BooleanNot\BooleanInBooleanNotRuleFixerRector;
use Rector\Strict\Rector\Ternary\BooleanInTernaryOperatorRuleFixerRector;
use Rector\TypeDeclaration\Rector\ClassMethod\AddVoidReturnTypeWhereNoReturnRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (RectorConfig $config): void {
    $config->paths([
        __DIR__ . '/config',
        __DIR__ . '/src',
    ]);
    $config->phpVersion(PhpVersion::PHP_80);
    $config->importShortClasses();
    $config->importNames();
    $config->import(LevelSetList::UP_TO_PHP_80);
    $config->import(SetList::CODE_QUALITY);

    $config->rule(AddVoidReturnTypeWhereNoReturnRector::class);
    $config->ruleWithConfiguration(
        TypedPropertyRector::class,
        [
            TypedPropertyRector::INLINE_PUBLIC => true,
        ]
    );
    $config->ruleWithConfiguration(
        BooleanInBooleanNotRuleFixerRector::class,
        [
            BooleanInTernaryOperatorRuleFixerRector::TREAT_AS_NON_EMPTY => false,
        ]
    );

    // Does not work with trait
    $config->skip([RestoreDefaultNullToNullableTypePropertyRector::class]);
};
