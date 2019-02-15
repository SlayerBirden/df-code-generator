<?php
declare(strict_types=1);

use SlayerBirden\DFCodeGeneration\Command\Controllers\AddActionCommand;
use SlayerBirden\DFCodeGeneration\Command\Controllers\AllActionsCommand;
use SlayerBirden\DFCodeGeneration\Command\Controllers\DeleteActionCommand;
use SlayerBirden\DFCodeGeneration\Command\Controllers\GetActionCommand;
use SlayerBirden\DFCodeGeneration\Command\Controllers\GetsActionCommand;
use SlayerBirden\DFCodeGeneration\Command\Controllers\UpdateActionCommand;
use SlayerBirden\DFCodeGeneration\Command\Tests\Api\AddTestCommand;
use SlayerBirden\DFCodeGeneration\Command\Tests\Api\AllTestsCommand;
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

$configProvider = new \SlayerBirden\DFCodeGeneration\Generator\Config\FileConfigProvider();

$app = new Application();

$writer = new FileWriter($baseDir);
// add
$app->add(new AddActionCommand(null, $writer, $configProvider));
$app->add(new AddTestCommand(null, $writer));
// delete
$app->add(new DeleteActionCommand(null, $writer, $configProvider));
$app->add(new DeleteTestCommand(null, $writer));

// get
$app->add(new GetActionCommand(null, $writer, $configProvider));
$app->add(new GetTestCommand(null, $writer));

//gets
$app->add(new GetsActionCommand(null, $writer, $configProvider));
$app->add(new GetsTestCommand(null, $writer));

//update
$app->add(new UpdateActionCommand(null, $writer, $configProvider));
$app->add(new UpdateTestCommand(null, $writer));

//all
$app->add(new AllActionsCommand(null, $writer, $configProvider));
$app->add(new AllTestsCommand(null, $writer));
$app->run();
