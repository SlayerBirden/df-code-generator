<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration\Command;

use Doctrine\Common\Annotations\AnnotationRegistry;
use SlayerBirden\DFCodeGeneration\Generator\Config\Config;
use SlayerBirden\DFCodeGeneration\Generator\Config\StandardProvider;
use SlayerBirden\DFCodeGeneration\Generator\Controllers\Add\Add;
use SlayerBirden\DFCodeGeneration\Generator\Controllers\Add\Delete;
use SlayerBirden\DFCodeGeneration\Generator\Controllers\Add\Get;
use SlayerBirden\DFCodeGeneration\Generator\Controllers\Add\Gets;
use SlayerBirden\DFCodeGeneration\Generator\Controllers\Add\Update;
use SlayerBirden\DFCodeGeneration\Generator\Factory\Routes;
use SlayerBirden\DFCodeGeneration\Generator\Controllers\Tests\Add as TestAdd;
use SlayerBirden\DFCodeGeneration\Generator\Controllers\Tests\Delete as TestDelete;
use SlayerBirden\DFCodeGeneration\Generator\Controllers\Tests\Get as TestGet;
use SlayerBirden\DFCodeGeneration\Generator\Controllers\Tests\Gets as TestGets;
use SlayerBirden\DFCodeGeneration\Generator\Controllers\Tests\Update as TestUpdate;
use SlayerBirden\DFCodeGeneration\Generator\Factory\SimpleProvider;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ApiSuiteCommand extends Command
{
    /**
     * @var OutputInterface
     */
    private $output;
    /**
     * @var string
     */
    private $entityClassName;
    /**
     * @var bool
     */
    private $force;
    /**
     * @var bool
     */
    private $tests;

    protected function configure()
    {
        $this->setName('generate:api')
            ->setDescription('Api Suite for Entity.')
            ->setHelp('This command creates the full Api suite (CRUD + tests) for DataFlow Server Entity. Don\'t forget to use force flag if you want to write files.')
            ->addArgument('entity', InputArgument::REQUIRED, 'Entity class')
            ->addOption('tests', 't', InputOption::VALUE_NONE, 'Whether to create tests.')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Without force flag no writes happen (only output).');
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;
        $this->entityClassName = $input->getArgument('entity');
        $this->force = $input->getOption('force');
        $this->tests = $input->getOption('tests');

        AnnotationRegistry::registerLoader('class_exists');
    }

    /**
     * {@inheritdoc}
     * @throws \Doctrine\Common\Annotations\AnnotationException
     * @throws \ReflectionException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!class_exists($this->entityClassName)) {
            throw new InvalidArgumentException('Entity Class does not exist.');
        }

        $this->generateControllerStack();
        $this->generateRoutes();
        $this->generateConfig();
        if ($this->tests) {
            $this->generateTests();
        }
    }

    /**
     * @throws \Doctrine\Common\Annotations\AnnotationException
     * @throws \ReflectionException
     */
    private function generateTests(): void
    {
        $addBody = (new TestAdd($this->entityClassName))->generate();
        $deleteBody = (new TestDelete($this->entityClassName))->generate();
        $getBody = (new TestGet($this->entityClassName))->generate();
        $getsBody = (new TestGets($this->entityClassName))->generate();
        $updateBody = (new TestUpdate($this->entityClassName))->generate();
        if (!$this->force) {
            $this->output->write($addBody);
            $this->output->write($deleteBody);
            $this->output->write($getBody);
            $this->output->write($getsBody);
            $this->output->write($updateBody);
        }
    }

    private function generateConfig(): void
    {
        $configBody = (new Config(new StandardProvider($this->entityClassName)))->generate();

        if (!$this->force) {
            $this->output->write($configBody);
        }
    }

    /**
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    private function generateRoutes(): void
    {
        $routesBody = (new Routes(new SimpleProvider($this->entityClassName)))->generate();

        if (!$this->force) {
            $this->output->write($routesBody);
        }
    }

    /**
     * @throws \Doctrine\Common\Annotations\AnnotationException
     * @throws \ReflectionException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    private function generateControllerStack(): void
    {
        $addBody = (new Add($this->entityClassName))->generate();
        $deleteBody = (new Delete($this->entityClassName))->generate();
        $getBody = (new Get($this->entityClassName))->generate();
        $getsBody = (new Gets($this->entityClassName))->generate();
        $updateBody = (new Update($this->entityClassName))->generate();

        if (!$this->force) {
            $this->output->write($addBody);
            $this->output->write($deleteBody);
            $this->output->write($getBody);
            $this->output->write($getsBody);
            $this->output->write($updateBody);
        }
    }
}
