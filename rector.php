<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;

// Configuration alignée sur phpstan.neon (même version PHP cible, mêmes chemins
// analysés, réutilisation du bootstrap PHPStan pour que Rector résolve correctement
// les constantes/classes WordPress via phpstan.bootstrap.php).
return RectorConfig::configure()
                   ->withPaths([
                       __DIR__ . '/src',
                   ])
                   ->withSkip([
                       __DIR__ . '/vendor',
                   ])
                    // phpVersion: 80300 dans phpstan.neon
                   ->withPhpVersion(80300)
                   ->withPhpSets(php83: true)
                    // Réutilise bootstrapFiles/constantes définis pour PHPStan (phpstan.bootstrap.php)
                   ->withPHPStanConfigs([__DIR__ . '/phpstan.neon'])
                   ->withPreparedSets(deadCode: true);
