<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration\Generator\Config;

use Doctrine\ORM\EntityManagerInterface;
use SlayerBirden\DFCodeGeneration\Generator\GeneratorInterface;
use Zend\Code\Generator\ClassGenerator;
use Zend\Code\Generator\FileGenerator;
use Zend\Code\Generator\MethodGenerator;

class Config implements GeneratorInterface
{
    /**
     * @var DataProviderInterface
     */
    private $provider;

    public function __construct(DataProviderInterface $configProvider)
    {
        $this->provider = $configProvider;
    }

    public function generate(): string
    {
        $new = [
            '\\Zend\\ServiceManager\\AbstractFactory\\ConfigAbstractFactory' => $this->getAbstractFactoryConfig(),
            'doctrine' => [
                'paths' => [
                    $this->provider->getEntitiesSrc(),
                ],
            ],
            'dependencies' => [
                'delegators' => [
                    '\\Zend\\Expressive\\Application' => [
                        $this->provider->getRouteFactoryName(),
                    ]
                ]
            ],
            'input_filter_specs' => [
                $this->provider->getInputFilterName() => $this->provider->getInputFilterSpec()
            ],
        ];

        $existing = $this->provider->getCurrentConfig();
        $invoke = 'return ' . var_export(array_replace_recursive($existing, $new), true) . ';';

        return (new FileGenerator())
            ->setNamespace($this->provider->getConfigNameSpace())
            ->setClass(
                (new ClassGenerator('ConfigProvider'))
                    ->addMethodFromGenerator(
                        (new MethodGenerator('__invoke'))
                            ->setBody($invoke)
                    )
            )
            ->generate();
    }

    private function getAbstractFactoryConfig(): array
    {
        return [
            $this->provider->getControllerName('add') => [
                EntityManagerInterface::class,
                '\\Zend\\Hydrator\\ClassMethods',
                $this->provider->getInputFilterName(),
                '\\Psr\\Log\\LoggerInterface',
                '\\SlayerBirden\\DataFlowServer\\Extractor\\RecursiveEntitiesExtractor',
            ],
            $this->provider->getControllerName('update') => [
                EntityManagerInterface::class,
                '\\Zend\\Hydrator\\ClassMethods',
                $this->provider->getInputFilterName(),
                '\\Psr\\Log\\LoggerInterface',
                '\\SlayerBirden\\DataFlowServer\\Extractor\\RecursiveEntitiesExtractor',
            ],
            $this->provider->getControllerName('get') => [
                EntityManagerInterface::class,
                '\\Psr\\Log\\LoggerInterface',
                '\\SlayerBirden\\DataFlowServer\\Extractor\\RecursiveEntitiesExtractor',
            ],
            $this->provider->getControllerName('gets') => [
                EntityManagerInterface::class,
                '\\Psr\\Log\\LoggerInterface',
                '\\SlayerBirden\\DataFlowServer\\Extractor\\RecursiveEntitiesExtractor',
            ],
            $this->provider->getControllerName('delete') => [
                EntityManagerInterface::class,
                '\\Psr\\Log\\LoggerInterface',
                '\\SlayerBirden\\DataFlowServer\\Extractor\\RecursiveEntitiesExtractor',
            ],
        ];
    }
}
