<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration\Command\Tests\Api;

use SlayerBirden\DFCodeGeneration\Command\AbstractApiCommand;
use SlayerBirden\DFCodeGeneration\Generator\Config\Providers\Decorators\EntityIdDecorator;
use SlayerBirden\DFCodeGeneration\Generator\Config\Providers\Decorators\OwnerDecorator;
use SlayerBirden\DFCodeGeneration\Generator\Controllers\Providers\Decorators\RelationsProviderDecorator;
use SlayerBirden\DFCodeGeneration\Generator\DataProvider\BaseProvider;
use SlayerBirden\DFCodeGeneration\Generator\DataProvider\CachedProvider;
use SlayerBirden\DFCodeGeneration\Generator\DataProvider\DecoratedProvider;
use SlayerBirden\DFCodeGeneration\Generator\Tests\Api\GetGenerator;
use SlayerBirden\DFCodeGeneration\Generator\Tests\Api\Providers\Decorators\EntityDataDecorator;
use SlayerBirden\DFCodeGeneration\Generator\Tests\Api\ReflectionEntitySpecProvider;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class GetTestCommand extends AbstractApiCommand
{
    protected function configure()
    {
        parent::configure();
        $this->setName('test:api:get')
            ->setDescription('Api Test for get action.')
            ->setHelp('This command creates the Codeception Api Test for Get Action for given entity.');
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

        $generator = new GetGenerator(
            new CachedProvider(
                new DecoratedProvider(
                    $baseProvider,
                    new EntityDataDecorator(
                        $this->entityClassName,
                        new ReflectionEntitySpecProvider($this->entityClassName)
                    ),
                    new EntityIdDecorator($this->entityClassName),
                    new RelationsProviderDecorator($this->entityClassName),
                    new OwnerDecorator($this->entityClassName)
                )
            )
        );
        $this->writer->write($generator->generate(), $generator->getFileName());

        parent::execute($input, $output);
    }
}
