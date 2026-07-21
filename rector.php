<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;

// Config aligned with phpstan.neon (same target PHP version, same analysed
// paths, reuses the PHPStan bootstrap so Rector correctly resolves
// WordPress constants/classes via phpstan.bootstrap.php).
return RectorConfig::configure()
                   ->withPaths([
                       __DIR__ . '/src',
                   ])
                   ->withSkip([
                       __DIR__ . '/vendor',
                   ])
                    // phpVersion: 80300 in phpstan.neon
                   ->withPhpVersion(80300)
                   ->withPhpSets(php83: true)
                    // Reuses the bootstrapFiles/constants defined for PHPStan (phpstan.bootstrap.php)
                   ->withPHPStanConfigs([__DIR__ . '/phpstan.neon'])
                   ->withPreparedSets(deadCode: true);
