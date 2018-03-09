<?php
declare(strict_types=1);

$autoloadPaths = [
    dirname(__DIR__) . '/vendor/autoload.php',
    dirname(__DIR__, 2) . '/vendor/autoload.php',
];

foreach ($autoloadPaths as $path) {
    if (file_exists($path)) {
        require $path;
        break;
    }
}

$app = new \Symfony\Component\Console\Application();

$app->add(new \SlayerBirden\DFCodeGeneration\Command\ApiSuiteCommand());

$app->run();
