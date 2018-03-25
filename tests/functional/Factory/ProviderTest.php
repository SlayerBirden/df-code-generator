<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration\Factory;

use PHPUnit\Framework\TestCase;
use SlayerBirden\DFCodeGeneration\Catalog\Entities\Product;
use SlayerBirden\DFCodeGeneration\Generator\Factory\SimpleProvider;

class ProviderTest extends TestCase
{
    /**
     * @var SimpleProvider
     */
    private $provider;

    protected function setUp()
    {
        $this->provider = new SimpleProvider(Product::class);
    }

    public function testProvide()
    {
        $expected = [
            'ns' => 'SlayerBirden\\DFCodeGeneration\\Catalog\\Factory',
            'controllerNs' => 'SlayerBirden\\DFCodeGeneration\\Catalog\\Controller',
            'entityName' => 'Product',
            'pluralEntityName' => 'Products',
            'idName' => 'id',
            'idRegexp' => '\d+',
        ];

        $this->assertEquals(
            $expected,
            $this->provider->provide()
        );
    }

    public function testGetClassName()
    {
        $this->assertSame(
            'SlayerBirden\\DFCodeGeneration\\Catalog\\Factory\\ProductRoutesDelegator',
            $this->provider->getClassName()
        );
    }
}
