<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration\Command;

use Doctrine\Common\Annotations\AnnotationRegistry;
use SlayerBirden\DFCodeGeneration\Generator\Config\Config;
use SlayerBirden\DFCodeGeneration\Generator\Config\StandardProvider;
use SlayerBirden\DFCodeGeneration\Generator\Controllers\Add;
use SlayerBirden\DFCodeGeneration\Generator\Controllers\DecoratedProvider;
use SlayerBirden\DFCodeGeneration\Generator\Controllers\Delete;
use SlayerBirden\DFCodeGeneration\Generator\Controllers\EntityNamePluralDecorator;
use SlayerBirden\DFCodeGeneration\Generator\Controllers\Get;
use SlayerBirden\DFCodeGeneration\Generator\Controllers\Gets;
use SlayerBirden\DFCodeGeneration\Generator\Controllers\RelationsProviderDecorator;
use SlayerBirden\DFCodeGeneration\Generator\Controllers\UniqueProviderDecorator;
use SlayerBirden\DFCodeGeneration\Generator\Controllers\Update;
use SlayerBirden\DFCodeGeneration\Generator\Factory\Routes;
use SlayerBirden\DFCodeGeneration\Generator\Tests\Add as TestAdd;
use SlayerBirden\DFCodeGeneration\Generator\Tests\Delete as TestDelete;
use SlayerBirden\DFCodeGeneration\Generator\Tests\Get as TestGet;
use SlayerBirden\DFCodeGeneration\Generator\Tests\Gets as TestGets;
use SlayerBirden\DFCodeGeneration\Generator\Tests\IdRegistry;
use SlayerBirden\DFCodeGeneration\Generator\Tests\ReflectionProviderFactory;
use SlayerBirden\DFCodeGeneration\Generator\Tests\Update as TestUpdate;
use SlayerBirden\DFCodeGeneration\Generator\Factory\SimpleProvider;
use SlayerBirden\DFCodeGeneration\Writer\OutputWriter;
use SlayerBirden\DFCodeGeneration\Writer\Psr4FileNameProvider;
use SlayerBirden\DFCodeGeneration\Writer\WriteInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Exception\LogicException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use SlayerBirden\DFCodeGeneration\Generator\Controllers\SimpleProvider as ControllerSimpleProvider;

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
    /**
     * @var null|WriteInterface
     */
    private $writer;

    public function __construct(?string $name = null, ?WriteInterface $writer = null)
    {
        parent::__construct($name);
        $this->writer = $writer;
    }

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
        // If it's not force mode we're using output writer
        if (!$this->force) {
            $this->writer = new OutputWriter($this->output, new Psr4FileNameProvider());
        }

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

        $output->writeln([
            '<error>IMPORTANT: please check all generated files before committing.</error>',
            '<error># You might want to run something like "php-cs-fixer" to make sure formatting is correct.</error>',
        ]);
    }

    private function generateTests(): void
    {
        $entityProviderFactory = new ReflectionProviderFactory(new IdRegistry());

        $files = [];
        $files[] = (new TestAdd($this->entityClassName, $entityProviderFactory))->generate();
        $files[] = (new TestDelete($this->entityClassName, $entityProviderFactory))->generate();
        $files[] = (new TestGet($this->entityClassName, $entityProviderFactory))->generate();
        $files[] = (new TestGets($this->entityClassName, $entityProviderFactory))->generate();
        $files[] = (new TestUpdate($this->entityClassName, $entityProviderFactory))->generate();

        array_walk($files, function ($contents) {
            $this->writer->write($contents);
        });

        $this->output->writeln('<info>Acceptance tests generated</info>');
    }

    private function generateConfig(): void
    {
        $this->getWriter()->write(
            (new Config(new StandardProvider($this->entityClassName)))->generate()
        );

        $this->output->writeln('<info>Config generated</info>');
    }

    /**
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    private function generateRoutes(): void
    {
        $this->getWriter()->write(
            (new Routes(new SimpleProvider($this->entityClassName)))->generate()
        );

        $this->output->writeln('<info>Routes provider generated</info>');
    }

    /**
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    private function generateControllerStack(): void
    {
        $files = [];
        $files[] = (new Add(
            new DecoratedProvider(
                $this->entityClassName,
                new UniqueProviderDecorator($this->entityClassName),
                new RelationsProviderDecorator($this->entityClassName)
            )
        ))->generate();
        $files[] = (new Delete(new ControllerSimpleProvider($this->entityClassName)))->generate();
        $files[] = (new Get(new ControllerSimpleProvider($this->entityClassName)))->generate();
        $files[] = (new Gets(
            new DecoratedProvider(
                $this->entityClassName,
                new EntityNamePluralDecorator()
            )
        ))->generate();
        $files[] = (new Update(
            new DecoratedProvider(
                $this->entityClassName,
                new UniqueProviderDecorator($this->entityClassName),
                new RelationsProviderDecorator($this->entityClassName)
            )
        ))->generate();

        array_walk($files, function ($contents) {
            $this->writer->write($contents);
        });

        $this->output->writeln('<info>Controller stack generated</info>');
    }

    /**
     * @return WriteInterface
     */
    private function getWriter(): WriteInterface
    {
        if ($this->writer === null) {
            throw new LogicException('Writer is not defined!');
        }
        return $this->writer;
    }
}
