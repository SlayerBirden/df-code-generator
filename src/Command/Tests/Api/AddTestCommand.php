<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration\Command\Tests\Api;

use SlayerBirden\DFCodeGeneration\Command\AbstractApiCommand;
use SlayerBirden\DFCodeGeneration\Generator\Controllers\Providers\Decorators\UniqueProviderDecorator;
use SlayerBirden\DFCodeGeneration\Generator\DataProvider\BaseProvider;
use SlayerBirden\DFCodeGeneration\Generator\DataProvider\CachedProvider;
use SlayerBirden\DFCodeGeneration\Generator\DataProvider\DecoratedProvider;
use SlayerBirden\DFCodeGeneration\Generator\Tests\Api\AddGenerator;
use SlayerBirden\DFCodeGeneration\Generator\Tests\Api\Providers\Decorators\EntityDataDecorator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class AddTestCommand extends AbstractApiCommand
{
    protected function configure()
    {
        parent::configure();
        $this->setName('generate:test:api:add')
            ->setDescription('Add Api Test for add action.')
            ->setHelp('This command creates the Codeception Api Test for Add Action for given entity.');
    }

    /**
     * {@inheritdoc}
     * @param InputInterface $input
     * @param OutputInterface $output
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $baseProvider = new BaseProvider($this->entityClassName);

        $generator = new AddGenerator(
            new CachedProvider(
                new DecoratedProvider(
                    $baseProvider,
                    new UniqueProviderDecorator($this->entityClassName),
                    new EntityDataDecorator($this->entityClassName)
                )
            )
        );
        $this->writer->write($generator->generate(), $generator->getFileName());

        parent::execute($input, $output);
    }
}
