<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration\Command\Controllers;

use SlayerBirden\DFCodeGeneration\Command\AbstractApiCommand;
use SlayerBirden\DFCodeGeneration\Generator\Config\Code\Parts\DefaultCodeFeederPart;
use SlayerBirden\DFCodeGeneration\Generator\Config\Code\Parts\InputFilterCodeFeederPart;
use SlayerBirden\DFCodeGeneration\Generator\Config\Code\Parts\SpecCodeFeederPart;
use SlayerBirden\DFCodeGeneration\Generator\Config\Code\SplitArrayCodeFeeder;
use SlayerBirden\DFCodeGeneration\Generator\Config\ConfigGenerator;
use SlayerBirden\DFCodeGeneration\Generator\Config\Parts;
use SlayerBirden\DFCodeGeneration\Generator\Config\Providers\Decorators\EntitiesSrcDecorator;
use SlayerBirden\DFCodeGeneration\Generator\Config\Providers\Decorators\EntityIdDecorator;
use SlayerBirden\DFCodeGeneration\Generator\Config\Providers\Decorators\HydratorDecorator;
use SlayerBirden\DFCodeGeneration\Generator\Config\Providers\Decorators\InputFilterDecorator;
use SlayerBirden\DFCodeGeneration\Generator\Config\Providers\Decorators\NameSpaceDecorator as ConfigNsDecorator;
use SlayerBirden\DFCodeGeneration\Generator\Config\Providers\Decorators\OwnerDecorator;
use SlayerBirden\DFCodeGeneration\Generator\Controllers\Providers\Decorators\NameSpaceDecorator as ControlNSDecorator;
use SlayerBirden\DFCodeGeneration\Generator\Controllers\Providers\Decorators\RelationsProviderDecorator;
use SlayerBirden\DFCodeGeneration\Generator\Controllers\Providers\Decorators\UniqueProviderDecorator;
use SlayerBirden\DFCodeGeneration\Generator\Controllers\UpdateGenerator;
use SlayerBirden\DFCodeGeneration\Generator\DataProvider\BaseProvider;
use SlayerBirden\DFCodeGeneration\Generator\DataProvider\CachedProvider;
use SlayerBirden\DFCodeGeneration\Generator\DataProvider\DecoratedProvider;
use SlayerBirden\DFCodeGeneration\Generator\Factory\HydratorFactoryGenerator;
use SlayerBirden\DFCodeGeneration\Generator\Factory\InputFilterMiddlewareFactoryGenerator;
use SlayerBirden\DFCodeGeneration\Generator\Factory\Providers\Decorators\HydratorDecorator as FactoryHydratorDecorator;
use SlayerBirden\DFCodeGeneration\Generator\Factory\Providers\Decorators\NameSpaceDecorator as FactoryNSDecorator;
use SlayerBirden\DFCodeGeneration\Generator\Factory\ResourceMiddlewareFactoryGenerator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class UpdateActionCommand extends AbstractApiCommand
{
    protected function configure()
    {
        parent::configure();
        $this->setName('action:update')
            ->setDescription('Update action controller and support configuration.')
            ->setHelp('This command creates the Update Action for given entity.');
    }

    /**
     * {@inheritdoc}
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $baseProvider = new BaseProvider($this->entityClassName);
        $controllerNsDecorator = new ControlNSDecorator($this->entityClassName);

        $controllerGenerator = new UpdateGenerator(
            new CachedProvider(
                new DecoratedProvider(
                    $baseProvider,
                    new UniqueProviderDecorator($this->entityClassName),
                    new RelationsProviderDecorator($this->entityClassName),
                    $controllerNsDecorator
                )
            )
        );
        $this->writer->write($controllerGenerator->generate(), $controllerGenerator->getFileName());

        $configDataProvider = new CachedProvider(
            new DecoratedProvider(
                $baseProvider,
                new EntitiesSrcDecorator($this->entityClassName),
                new InputFilterDecorator($this->entityClassName),
                new ConfigNsDecorator($this->entityClassName),
                $controllerNsDecorator,
                new HydratorDecorator($this->entityClassName),
                new FactoryNSDecorator($this->entityClassName),
                new FactoryHydratorDecorator($this->entityClassName),
                new OwnerDecorator($this->entityClassName),
                new EntityIdDecorator($this->entityClassName)
            )
        );
        $configGenerator = new ConfigGenerator(
            $configDataProvider,
            new SplitArrayCodeFeeder(
                new InputFilterCodeFeederPart(
                    new SpecCodeFeederPart()
                ),
                new DefaultCodeFeederPart()
            ),
            $this->getConfigProvider(),
            new Parts\Doctrine($configDataProvider),
            new Parts\Update\AbstractFactory($configDataProvider),
            new Parts\Update\Dependencies($configDataProvider),
            new Parts\Add\InputFilter(
                new Parts\Add\InputFilter\Entity($configDataProvider)
            ),
            new Parts\Update\Routes($configDataProvider)
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

        $inputFilterFactoryGenerator = new InputFilterMiddlewareFactoryGenerator(
            new CachedProvider(
                new DecoratedProvider(
                    $baseProvider,
                    new InputFilterDecorator($this->entityClassName),
                    new FactoryNSDecorator($this->entityClassName)
                )
            )
        );
        $this->writer->write($inputFilterFactoryGenerator->generate(), $inputFilterFactoryGenerator->getFileName());

        parent::execute($input, $output);
    }
}
