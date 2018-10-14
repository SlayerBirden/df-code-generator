<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration\Command\Controllers;

use SlayerBirden\DFCodeGeneration\Command\AbstractApiCommand;
use SlayerBirden\DFCodeGeneration\Generator\Config\Code\Parts\DefaultCodeFeederPart;
use SlayerBirden\DFCodeGeneration\Generator\Config\Code\SplitArrayCodeFeeder;
use SlayerBirden\DFCodeGeneration\Generator\Config\ConfigGenerator;
use SlayerBirden\DFCodeGeneration\Generator\Config\Parts;
use SlayerBirden\DFCodeGeneration\Generator\Config\Providers\Decorators\EntitiesSrcDecorator;
use SlayerBirden\DFCodeGeneration\Generator\Config\Providers\Decorators\HydratorDecorator;
use SlayerBirden\DFCodeGeneration\Generator\Config\Providers\Decorators\NameSpaceDecorator as ConfigNsDecorator;
use SlayerBirden\DFCodeGeneration\Generator\Config\Providers\Decorators\OwnerDecorator;
use SlayerBirden\DFCodeGeneration\Generator\Controllers\DeleteGenerator;
use SlayerBirden\DFCodeGeneration\Generator\Controllers\Providers\Decorators\NameSpaceDecorator as ControllerNSDecorator;
use SlayerBirden\DFCodeGeneration\Generator\Controllers\Providers\Decorators\RelationsProviderDecorator;
use SlayerBirden\DFCodeGeneration\Generator\DataProvider\BaseProvider;
use SlayerBirden\DFCodeGeneration\Generator\DataProvider\CachedProvider;
use SlayerBirden\DFCodeGeneration\Generator\DataProvider\DecoratedProvider;
use SlayerBirden\DFCodeGeneration\Generator\Factory\HydratorFactoryGenerator;
use SlayerBirden\DFCodeGeneration\Generator\Factory\Providers\Decorators\HydratorDecorator as FactoryHydratorDecorator;
use SlayerBirden\DFCodeGeneration\Generator\Factory\Providers\Decorators\NameSpaceDecorator as FactoryNSDecorator;
use SlayerBirden\DFCodeGeneration\Generator\Factory\ResourceMiddlewareFactoryGenerator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class DeleteActionCommand extends AbstractApiCommand
{
    protected function configure()
    {
        parent::configure();
        $this->setName('generate:action:delete')
            ->setDescription('Delete action controller and support configuration.')
            ->setHelp('This command creates the Delete Action for given entity.');
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
        $controllerNsDecorator = new ControllerNSDecorator($this->entityClassName);

        $addGenerator = new DeleteGenerator(
            new CachedProvider(
                new DecoratedProvider(
                    $baseProvider,
                    $controllerNsDecorator
                )
            )
        );
        $this->writer->write($addGenerator->generate(), $addGenerator->getFileName());

        $configDataProvider = new CachedProvider(
            new DecoratedProvider(
                $baseProvider,
                new EntitiesSrcDecorator($this->entityClassName),
                new ConfigNsDecorator($this->entityClassName),
                new ControllerNSDecorator($this->entityClassName),
                new HydratorDecorator($this->entityClassName),
                new FactoryNSDecorator($this->entityClassName),
                new FactoryHydratorDecorator($this->entityClassName),
                new OwnerDecorator($this->entityClassName)
            )
        );
        $configGenerator = new ConfigGenerator(
            $configDataProvider,
            new SplitArrayCodeFeeder(
                new DefaultCodeFeederPart()
            ),
            new Parts\Doctrine($configDataProvider),
            new Parts\Delete\AbstractFactory($configDataProvider),
            new Parts\Delete\Dependencies($configDataProvider),
            new Parts\Delete\Routes($configDataProvider)
        );
        $this->writer->write($configGenerator->generate(), $configGenerator->getFileName());

        $hydratorFactoryGenerator = new HydratorFactoryGenerator(
            new CachedProvider(
                new DecoratedProvider(
                    $baseProvider,
                    new RelationsProviderDecorator($this->entityClassName),
                    new FactoryHydratorDecorator($this->entityClassName),
                    new FactoryNSDecorator($this->entityClassName)
                )
            )
        );
        $this->writer->write($hydratorFactoryGenerator->generate(), $hydratorFactoryGenerator->getFileName());

        $resourceMiddlewareFactoryGenerator = new ResourceMiddlewareFactoryGenerator(
            new CachedProvider(
                new DecoratedProvider(
                    $baseProvider,
                    new FactoryNSDecorator($this->entityClassName)
                )
            )
        );
        $this->writer->write(
            $resourceMiddlewareFactoryGenerator->generate(),
            $resourceMiddlewareFactoryGenerator->getFileName()
        );

        parent::execute($input, $output);
    }
}
