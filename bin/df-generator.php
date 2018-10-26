<?php
declare(strict_types=1);

use SlayerBirden\DFCodeGeneration\Command\Controllers\AddActionCommand;
use SlayerBirden\DFCodeGeneration\Command\Controllers\DeleteActionCommand;
use SlayerBirden\DFCodeGeneration\Command\Controllers\GetActionCommand;
use SlayerBirden\DFCodeGeneration\Command\Controllers\GetsActionCommand;
use SlayerBirden\DFCodeGeneration\Command\Controllers\UpdateActionCommand;
use SlayerBirden\DFCodeGeneration\Command\Tests\Api\AddTestCommand;
use SlayerBirden\DFCodeGeneration\Command\Tests\Api\DeleteTestCommand;
use SlayerBirden\DFCodeGeneration\Command\Tests\Api\GetsTestCommand;
use SlayerBirden\DFCodeGeneration\Command\Tests\Api\GetTestCommand;
use SlayerBirden\DFCodeGeneration\Command\Tests\Api\UpdateTestCommand;
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
// add
$app->add(new AddActionCommand(null, $writer));
$app->add(new AddTestCommand(null, $writer));
// delete
$app->add(new DeleteActionCommand(null, $writer));
$app->add(new DeleteTestCommand(null, $writer));

// get
$app->add(new GetActionCommand(null, $writer));
$app->add(new GetTestCommand(null, $writer));

//gets
$app->add(new GetsActionCommand(null, $writer));
$app->add(new GetsTestCommand(null, $writer));

//update
$app->add(new UpdateActionCommand(null, $writer));
$app->add(new UpdateTestCommand(null, $writer));
$app->run();
