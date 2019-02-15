<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration\Command;

use Doctrine\Common\Annotations\AnnotationRegistry;
use SlayerBirden\DFCodeGeneration\Writer\OutputWriter;
use SlayerBirden\DFCodeGeneration\Writer\WriteInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Exception\LogicException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractApiCommand extends Command
{
    /**
     * @var OutputInterface
     */
    protected $output;
    /**
     * @var string
     */
    protected $entityClassName;
    /**
     * @var bool
     */
    protected $force;
    /**
     * @var null|WriteInterface
     */
    protected $writer;

    public function __construct(?string $name = null, ?WriteInterface $writer = null)
    {
        parent::__construct($name);
        $this->writer = $writer;
    }

    protected function configure()
    {
        $this->addArgument('entity', InputArgument::REQUIRED, 'Entity class')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Without force flag no writes happen (only output).');
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        // we need to validate before assigning values
        $input->validate();
        $this->output = $output;
        $this->entityClassName = $input->getArgument('entity');
        if (!class_exists($this->entityClassName)) {
            throw new InvalidArgumentException('Entity Class does not exist.');
        }
        $this->force = $input->getOption('force');
        // If it's not force mode we're using output writer
        if (!$this->force) {
            $this->writer = new OutputWriter($this->output);
        }

        // we need to register the default Annotation loader
        AnnotationRegistry::registerLoader('class_exists');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln([
            '<error>IMPORTANT: please check all generated files before committing.</error>',
            '<error># You might want to run something like "php-cs-fixer" to make sure formatting is correct.</error>',
        ]);
    }

    /**
     * @return WriteInterface
     */
    protected function getWriter(): WriteInterface
    {
        if ($this->writer === null) {
            throw new LogicException('Writer is not defined!');
        }
        return $this->writer;
    }
}
