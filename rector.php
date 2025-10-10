<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withPaths([
        __DIR__.'/src',
        __DIR__.'/tests',
    ])
    ->withSkipPath(__DIR__.'/tests/TestCase.php')
    ->withPreparedSets(
        deadCode        : true,
        codeQuality     : true,
        typeDeclarations: true,
        privatization   : true,
        earlyReturn     : true,
        strictBooleans  : true,
    )
    ->withPhpSets();
