<?php
declare(strict_types=1);

use SlayerBirden\DFCodeGeneration\Command\ApiSuiteCommand;
use SlayerBirden\DFCodeGeneration\Writer\FileWriter;
use SlayerBirden\DFCodeGeneration\Writer\Psr4FileNameProvider;
use Symfony\Component\Console\Application;

$autoloadPaths = [
    dirname(__DIR__) . '/vendor/autoload.php',
    dirname(__DIR__, 3) . '/vendor/autoload.php',
];

$baseDir = '';

foreach ($autoloadPaths as $path) {
    if (file_exists($path)) {
        $baseDir = dirname($path, 2);
        require $path;
        break;
    }
}

$app = new Application();

$writer = new FileWriter($baseDir, new Psr4FileNameProvider());
$app->add(new ApiSuiteCommand(null, $writer));

$app->run();
