<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration\Generator\Config;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use SlayerBirden\DFCodeGeneration\CodeLoader;
use SlayerBirden\DFCodeGeneration\PrintFileTrait;

class GetTest extends TestCase
{
    use PrintFileTrait;
    /**
     * @var ObjectProphecy
     */
    private $provider;

    protected function setUp()
    {
        $this->provider = $this->prophesize(DataProviderInterface::class);
        $this->provider->getRouteFactoryName()->willReturn('Routes');
        $this->provider->getInputFilterSpec()->willReturn([
            'bar' => [
                'required' => true,
            ],
            'baz' => [
                'required' => true,
            ],
        ]);
        $this->provider->getInputFilterName()->willReturn('DummyInputFilter');
        $this->provider->getEntitiesSrc()->willReturn('src/Dummy/Entities');
        $this->provider->getCurrentConfig()->willReturn([]);
        $this->provider->getControllerName(Argument::type('string'))->will(function ($args) {
            return ucwords($args[0]) . 'DummyAction';
        });
        $this->provider->getConfigNameSpace()->willReturn('A\B');
    }

    public function testNewConfig()
    {
        $configGenerator = new Config($this->provider->reveal());
        $value = $configGenerator->generate();

        try {
            CodeLoader::loadCode($value, 'dummyProvider.php');
            $class = $configGenerator->getClassName();
            $config = new $class();
            $actual = call_user_func($config);
        } catch (\ParseError $exception) {
            echo 'File', PHP_EOL, $this->getPrintableFile($value), PHP_EOL;
            throw $exception;
        }

        $expected = [
            '\\Zend\\ServiceManager\\AbstractFactory\\ConfigAbstractFactory' => [
                'AddDummyAction' => [
                    EntityManagerInterface::class,
                    '\\Zend\\Hydrator\\ClassMethods',
                    'DummyInputFilter',
                    '\\Psr\\Log\\LoggerInterface',
                    '\\SlayerBirden\\DataFlowServer\\Extractor\\RecursiveEntitiesExtractor',
                ],
                'UpdateDummyAction' => [
                    EntityManagerInterface::class,
                    '\\Zend\\Hydrator\\ClassMethods',
                    'DummyInputFilter',
                    '\\Psr\\Log\\LoggerInterface',
                    '\\SlayerBirden\\DataFlowServer\\Extractor\\RecursiveEntitiesExtractor',
                ],
                'GetDummyAction' => [
                    EntityManagerInterface::class,
                    '\\Psr\\Log\\LoggerInterface',
                    '\\SlayerBirden\\DataFlowServer\\Extractor\\RecursiveEntitiesExtractor',
                ],
                'GetsDummyAction' => [
                    EntityManagerInterface::class,
                    '\\Psr\\Log\\LoggerInterface',
                    '\\SlayerBirden\\DataFlowServer\\Extractor\\RecursiveEntitiesExtractor',
                ],
                'DeleteDummyAction' => [
                    EntityManagerInterface::class,
                    '\\Psr\\Log\\LoggerInterface',
                    '\\SlayerBirden\\DataFlowServer\\Extractor\\RecursiveEntitiesExtractor',
                ],
            ],
            'dependencies' => [
                'delegators' => [
                    '\\Zend\\Expressive\\Application' => [
                        'Routes',
                    ],
                ],
            ],
            'doctrine' => [
                'paths' => [
                    'src/Dummy/Entities'
                ],
            ],
            'input_filter_specs' => [
                'DummyInputFilter' => [
                    'bar' => [
                        'required' => true,
                    ],
                    'baz' => [
                        'required' => true,
                    ],
                ],
            ],
        ];

        $this->assertEquals($expected, $actual);
    }

    public function testExistingConfig()
    {
        $this->provider->getCurrentConfig()->willReturn([
            '\\Zend\\ServiceManager\\AbstractFactory\\ConfigAbstractFactory' => [
                'AddSuperAction' => [
                    EntityManagerInterface::class,
                    '\\Zend\\Hydrator\\ClassMethods',
                    'SuperInputFilter',
                    '\\Psr\\Log\\LoggerInterface',
                    '\\SlayerBirden\\DataFlowServer\\Extractor\\RecursiveEntitiesExtractor',
                ],
                'UpdateSuperAction' => [
                    EntityManagerInterface::class,
                    '\\Zend\\Hydrator\\ClassMethods',
                    'SuperInputFilter',
                    '\\Psr\\Log\\LoggerInterface',
                    '\\SlayerBirden\\DataFlowServer\\Extractor\\RecursiveEntitiesExtractor',
                ],
                'GetSuperAction' => [
                    EntityManagerInterface::class,
                    '\\Psr\\Log\\LoggerInterface',
                    '\\SlayerBirden\\DataFlowServer\\Extractor\\RecursiveEntitiesExtractor',
                ],
                'GetsSuperAction' => [
                    EntityManagerInterface::class,
                    '\\Psr\\Log\\LoggerInterface',
                    '\\SlayerBirden\\DataFlowServer\\Extractor\\RecursiveEntitiesExtractor',
                ],
                'DeleteSuperAction' => [
                    EntityManagerInterface::class,
                    '\\Psr\\Log\\LoggerInterface',
                    '\\SlayerBirden\\DataFlowServer\\Extractor\\RecursiveEntitiesExtractor',
                ],
            ],
            'dependencies' => [
                'delegators' => [
                    '\\Zend\\Expressive\\Application' => [
                        'Routes',
                    ],
                ],
            ],
            'doctrine' => [
                'paths' => [
                    'src/Dummy/Entities'
                ],
            ],
            'input_filter_specs' => [
                'SuperInputFilter' => [
                    'bar' => [
                        'required' => true,
                    ],
                    'baz' => [
                        'required' => true,
                    ],
                ],
            ],
        ]);
        $this->provider->getConfigNameSpace()->willReturn('A\C');

        $configGenerator = new Config($this->provider->reveal());
        $value = $configGenerator->generate();

        try {
            CodeLoader::loadCode($value, 'dummyProvider.php');
            $class = $configGenerator->getClassName();
            $config = new $class();
            $actual = call_user_func($config);
        } catch (\ParseError $exception) {
            echo 'File', PHP_EOL, $this->getPrintableFile($value), PHP_EOL;
            throw $exception;
        }

        $expected = [
            '\\Zend\\ServiceManager\\AbstractFactory\\ConfigAbstractFactory' => [
                'AddSuperAction' => [
                    EntityManagerInterface::class,
                    '\\Zend\\Hydrator\\ClassMethods',
                    'SuperInputFilter',
                    '\\Psr\\Log\\LoggerInterface',
                    '\\SlayerBirden\\DataFlowServer\\Extractor\\RecursiveEntitiesExtractor',
                ],
                'UpdateSuperAction' => [
                    EntityManagerInterface::class,
                    '\\Zend\\Hydrator\\ClassMethods',
                    'SuperInputFilter',
                    '\\Psr\\Log\\LoggerInterface',
                    '\\SlayerBirden\\DataFlowServer\\Extractor\\RecursiveEntitiesExtractor',
                ],
                'GetSuperAction' => [
                    EntityManagerInterface::class,
                    '\\Psr\\Log\\LoggerInterface',
                    '\\SlayerBirden\\DataFlowServer\\Extractor\\RecursiveEntitiesExtractor',
                ],
                'GetsSuperAction' => [
                    EntityManagerInterface::class,
                    '\\Psr\\Log\\LoggerInterface',
                    '\\SlayerBirden\\DataFlowServer\\Extractor\\RecursiveEntitiesExtractor',
                ],
                'DeleteSuperAction' => [
                    EntityManagerInterface::class,
                    '\\Psr\\Log\\LoggerInterface',
                    '\\SlayerBirden\\DataFlowServer\\Extractor\\RecursiveEntitiesExtractor',
                ],
                'AddDummyAction' => [
                    EntityManagerInterface::class,
                    '\\Zend\\Hydrator\\ClassMethods',
                    'DummyInputFilter',
                    '\\Psr\\Log\\LoggerInterface',
                    '\\SlayerBirden\\DataFlowServer\\Extractor\\RecursiveEntitiesExtractor',
                ],
                'UpdateDummyAction' => [
                    EntityManagerInterface::class,
                    '\\Zend\\Hydrator\\ClassMethods',
                    'DummyInputFilter',
                    '\\Psr\\Log\\LoggerInterface',
                    '\\SlayerBirden\\DataFlowServer\\Extractor\\RecursiveEntitiesExtractor',
                ],
                'GetDummyAction' => [
                    EntityManagerInterface::class,
                    '\\Psr\\Log\\LoggerInterface',
                    '\\SlayerBirden\\DataFlowServer\\Extractor\\RecursiveEntitiesExtractor',
                ],
                'GetsDummyAction' => [
                    EntityManagerInterface::class,
                    '\\Psr\\Log\\LoggerInterface',
                    '\\SlayerBirden\\DataFlowServer\\Extractor\\RecursiveEntitiesExtractor',
                ],
                'DeleteDummyAction' => [
                    EntityManagerInterface::class,
                    '\\Psr\\Log\\LoggerInterface',
                    '\\SlayerBirden\\DataFlowServer\\Extractor\\RecursiveEntitiesExtractor',
                ],
            ],
            'dependencies' => [
                'delegators' => [
                    '\\Zend\\Expressive\\Application' => [
                        'Routes',
                    ],
                ],
            ],
            'doctrine' => [
                'paths' => [
                    'src/Dummy/Entities'
                ],
            ],
            'input_filter_specs' => [
                'SuperInputFilter' => [
                    'bar' => [
                        'required' => true,
                    ],
                    'baz' => [
                        'required' => true,
                    ],
                ],
                'DummyInputFilter' => [
                    'bar' => [
                        'required' => true,
                    ],
                    'baz' => [
                        'required' => true,
                    ],
                ],
            ],
        ];

        $this->assertEquals($expected, $actual);
    }

    public function testGetClassName()
    {
        $this->assertSame('A\\B\\ConfigProvider', (new Config($this->provider->reveal()))->getClassName());
    }
}
