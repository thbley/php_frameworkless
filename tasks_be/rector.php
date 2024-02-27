<?php

use Rector\CodeQuality\Rector\Expression\InlineIfToExplicitIfRector;
use Rector\CodeQuality\Rector\Identical\FlipTypeControlToUseExclusiveTypeRector;
use Rector\CodingStyle\Rector\PostInc\PostIncDecToPreIncDecRector;
use Rector\Config\RectorConfig;
use Rector\Naming\Rector\Assign\RenameVariableToMatchMethodCallReturnTypeRector;
use Rector\Php74\Rector\Property\RestoreDefaultNullToNullableTypePropertyRector;
use Rector\Php81\Rector\Property\ReadOnlyPropertyRector;
use Rector\Php83\Rector\ClassMethod\AddOverrideAttributeToOverriddenMethodsRector;
use Rector\Strict\Rector\Ternary\DisallowedShortTernaryRuleFixerRector;
use Rector\ValueObject\PhpVersion;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ])
    ->withPhpSets(php83: true)
    ->withPhpVersion(PhpVersion::PHP_83)
    ->withAttributesSets(phpunit: true)
    ->withRootFiles()
    ->withSkip([
        DisallowedShortTernaryRuleFixerRector::class,
        FlipTypeControlToUseExclusiveTypeRector::class,
        InlineIfToExplicitIfRector::class,
        PostIncDecToPreIncDecRector::class,
        ReadOnlyPropertyRector::class,
        RenameVariableToMatchMethodCallReturnTypeRector::class,
        RestoreDefaultNullToNullableTypePropertyRector::class,
        AddOverrideAttributeToOverriddenMethodsRector::class,
        __DIR__ . '/src/vendor',
        __DIR__ . '/tests/vendor',
        __DIR__ . '/tests/data',
    ])
    ->withPreparedSets(true, true, true, true, true, true, true, true, true);
