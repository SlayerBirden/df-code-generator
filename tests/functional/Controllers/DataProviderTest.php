<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration\Controllers;

use PHPUnit\Framework\TestCase;
use SlayerBirden\DFCodeGeneration\Catalog\Entities\Product;
use SlayerBirden\DFCodeGeneration\Generator\Controllers\DecoratedProvider;
use SlayerBirden\DFCodeGeneration\Generator\Controllers\RelationsProviderDecorator;
use SlayerBirden\DFCodeGeneration\Generator\Controllers\UniqueProviderDecorator;

class DataProviderTest extends TestCase
{
    /**
     * @var DecoratedProvider
     */
    private $provider;

    protected function setUp()
    {
        $name = Product::class;
        $this->provider = new DecoratedProvider(
            $name,
            new UniqueProviderDecorator($name),
            new RelationsProviderDecorator($name)
        );
    }

    public function testProvider()
    {
        $expected = [
            'ns' => 'SlayerBirden\\DFCodeGeneration\\Catalog\\Controller',
            'useStatement' => Product::class,
            'entityName' => 'Product',
            'hasUnique' => true,
            'idName' => 'id',
            'uniqueIdxMessage' => 'Provided sku already exists.',
            'dataRelationship' => '//TODO process data relationship'
        ];
        $this->assertEquals($expected, $this->provider->provide());
    }

    /**
     * @dataProvider classNameProvider
     *
     * @param string $type
     * @param string $expected
     * @throws \ReflectionException
     */
    public function testGetClassName(string $type, string $expected)
    {
        $this->assertEquals($expected, $this->provider->getClassName($type));
    }

    public function classNameProvider(): array
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
