<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/app/Modules',
        // __DIR__ . '/bootstrap',
        // __DIR__ . '/config',
        // __DIR__ . '/lang',
        // __DIR__ . '/public',
        // __DIR__ . '/resources',
        // __DIR__ . '/routes',
        // __DIR__ . '/tests',
    ])
    ->withPreparedSets(
        deadCode : true,
        codeQuality : true,
        codingStyle : true,
        typeDeclarations : true,
        privatization : true,
        naming : true,
        earlyReturn : true,
        strictBooleans : true,
        carbon : true,
        rectorPreset : true,
        phpunitCodeQuality : true,
        doctrineCodeQuality : true,
        symfonyCodeQuality : true,
        symfonyConfigs : true
    );
