<?php
declare(strict_types=1);

use SlayerBirden\DFCodeGeneration\Command\Controllers\AddActionCommand;
use SlayerBirden\DFCodeGeneration\Writer\FileWriter;
use Symfony\Component\Console\Application;

$autoloadPaths = [
    dirname(__DIR__) . '/vendor/autoload.php',
    dirname(__DIR__, 3) . '/autoload.php',
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

$writer = new FileWriter($baseDir);
$app->add(new AddActionCommand(null, $writer));

$app->run();
