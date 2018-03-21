<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration;

use Doctrine\Common\Annotations\AnnotationRegistry;
use PHPUnit\Framework\TestCase;
use SlayerBirden\DFCodeGeneration\Catalog\Entities\Product;
use SlayerBirden\DFCodeGeneration\Generator\Config\StandardProvider;

class ConfigProviderTest extends TestCase
{
    /** @var StandardProvider */
    private $provider;

    protected function setUp()
    {
        // This is required for annotations to work
        AnnotationRegistry::registerLoader('class_exists');

        $this->provider = new StandardProvider(Product::class);
    }

    public function testGetRouteFactoryName()
    {
        $this->assertSame(
            'SlayerBirden\\DFCodeGeneration\\Catalog\\Factory\\ProductRoutesDelegator',
            $this->provider->getRouteFactoryName()
        );
    }

    public function testGetSpec()
    {
        $expected = [
            'name' => [
                'required' => true,
                'filters' => [
                    [
                        'name' => 'stringtrim',
                    ]
                ],
                'validators' => [
                    [
                        'name' => 'notempty',
                    ]
                ],
            ],
            'sku' => [
                'required' => true,
                'filters' => [
                    [
                        'name' => 'stringtrim',
                    ]
                ],
                'validators' => [
                    [
                        'name' => 'notempty',
                    ]
                ],
            ],
            'title' => [
                'required' => false,
                'filters' => [
                    [
                        'name' => 'stringtrim',
                    ]
                ],
            ],
        ];

        $this->assertEquals($expected, $this->provider->getInputFilterSpec());
    }

    public function testGetSpecName()
    {
        $this->assertSame('ProductInputFilter', $this->provider->getInputFilterName());
    }

    public function testGetCurrentConfig()
    {
        $this->assertEmpty($this->provider->getCurrentConfig());
    }

    public function testGetConfigNameSpace()
    {
        $this->assertEquals('SlayerBirden\\DFCodeGeneration\\Catalog', $this->provider->getConfigNameSpace());
    }

    public function testGetEntitiesSrc()
    {
        $this->assertSame('src/Catalog/Entities', $this->provider->getEntitiesSrc());
    }

    /**
     * @dataProvider controllerNameProvider
     *
     * @param string $type
     * @param string $expected
     * @throws \ReflectionException
     */
    public function testGetControllerName(string $type, string $expected)
    {
        $this->assertSame($expected, $this->provider->getControllerName($type));
    }

    public function controllerNameProvider(): array
    {
        return [
            [
                'get',
                'SlayerBirden\\DFCodeGeneration\\Catalog\\Controller\\GetProductAction',
            ],
            [
                'gets',
                'SlayerBirden\\DFCodeGeneration\\Catalog\\Controller\\GetProductsAction',
            ],
            [
                'add',
                'SlayerBirden\\DFCodeGeneration\\Catalog\\Controller\\AddProductAction',
            ],
            [
                'update',
                'SlayerBirden\\DFCodeGeneration\\Catalog\\Controller\\UpdateProductAction',
            ],
            [
                'delete',
                'SlayerBirden\\DFCodeGeneration\\Catalog\\Controller\\DeleteProductAction',
            ],
        ];
    }
}
