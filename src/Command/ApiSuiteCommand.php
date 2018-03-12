<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration\Command;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use SlayerBirden\DFCodeGeneration\Generator\Config\Config;
use SlayerBirden\DFCodeGeneration\Generator\Controllers\Add\Add;
use SlayerBirden\DFCodeGeneration\Generator\Controllers\Add\Delete;
use SlayerBirden\DFCodeGeneration\Generator\Controllers\Add\Get;
use SlayerBirden\DFCodeGeneration\Generator\Controllers\Add\Gets;
use SlayerBirden\DFCodeGeneration\Generator\Controllers\Add\Update;
use SlayerBirden\DFCodeGeneration\Generator\Factory\Routes;
use SlayerBirden\DFCodeGeneration\Generator\Controllers\Tests\Add as TestAdd;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ApiSuiteCommand extends Command
{
    protected function configure()
    {
        $this->setName('generate:api')
            ->setDescription('Api Suite for Entity.')
            ->setHelp('This command creates the full Api suite (CRUD + tests) for DataFlow Server Entity. Don\'t forget to use force flag if you want to write files.')
            ->addArgument('entity', InputArgument::REQUIRED, 'Entity class')
            ->addOption('tests', 't', InputOption::VALUE_NONE, 'Whether to create tests.')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Without force flag no writes happen (only output).');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $entityClassName = $input->getArgument('entity');

        if (!class_exists($entityClassName)) {
            throw new InvalidArgumentException('Entity Class does not exist.');
        }
        AnnotationRegistry::registerLoader('class_exists');

        $uniqueFields = [];
        $reflectionClassName = new \ReflectionClass($entityClassName);
        foreach ($reflectionClassName->getProperties() as $property) {
            /** @var \Doctrine\ORM\Mapping\Column $annotation */
            $annotation = (new AnnotationReader())
                ->getPropertyAnnotation($property, \Doctrine\ORM\Mapping\Column::class);
            if ($annotation->unique) {
                $uniqueFields[] = $property->getName();
            }
        }

        $routesPath = dirname($reflectionClassName->getFileName(), 2) . 'Factory/RoutesDelegator.php';

        $this->generateControllerStack($entityClassName, $uniqueFields, $input, $output);
        $this->generateRoutes($entityClassName, $routesPath, $input, $output);
        $this->generateConfig($entityClassName, $input, $output);

        if ($input->getOption('tests')) {
            $addBody = (new TestAdd($entityClassName))->generate();
            if (!$input->getOption('force')) {
                $output->write($addBody);
            }
        }
    }

    private function generateConfig(string $entityClassName, InputInterface $input, OutputInterface $output): void
    {
        $configBody = (new Config($entityClassName))->generate();

        if (!$input->getOption('force')) {
            $output->write($configBody);
        }
    }

    private function generateRoutes(
        string $entityClassName,
        string $fileName,
        InputInterface $input,
        OutputInterface $output
    ): void {
        $routesBody = (new Routes($fileName, $entityClassName))->generate();

        if (!$input->getOption('force')) {
            $output->write($routesBody);
        }
    }

    private function generateControllerStack(
        string $entityClassName,
        array $uniqueFields = [],
        InputInterface $input,
        OutputInterface $output
    ): void {
        $hasUniqueFields = !empty($uniqueFields);

        $addBody = (new Add($entityClassName, $hasUniqueFields, ...$uniqueFields))->generate();
        $deleteBody = (new Delete($entityClassName))->generate();
        $getBody = (new Get($entityClassName))->generate();
        $getsBody = (new Gets($entityClassName))->generate();
        $updateBody = (new Update($entityClassName, $hasUniqueFields, ...$uniqueFields))->generate();

        if (!$input->getOption('force')) {
            $output->write($addBody);
            $output->write($deleteBody);
            $output->write($getBody);
            $output->write($getsBody);
            $output->write($updateBody);
        }
    }
}
