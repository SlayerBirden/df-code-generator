<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration\Tests;

use PHPUnit\Framework\TestCase;
use SlayerBirden\DFCodeGeneration\Catalog\Entities\Category;
use SlayerBirden\DFCodeGeneration\Catalog\Entities\Product;
use SlayerBirden\DFCodeGeneration\Catalog\Entities\Stock;
use SlayerBirden\DFCodeGeneration\Generator\Tests\FakerValueProvider;
use SlayerBirden\DFCodeGeneration\Generator\Tests\IdRegistry;
use SlayerBirden\DFCodeGeneration\Generator\Tests\ReflectionProvider;

class ReflectionProviderTest extends TestCase
{
    /**
     * @var ReflectionProvider
     */
    private $provider;

    protected function setUp()
    {
        $id = new IdRegistry();
        $this->provider = new ReflectionProvider(
            Product::class,
            new FakerValueProvider(Product::class, $id),
            $id
        );
    }

    public function testGetId()
    {
        $this->assertInternalType('integer', $this->provider->getId());
    }

    public function testGetPostParams()
    {
        $params = $this->provider->getPostParams();
        $this->assertCount(5, $params);

        $this->assertArrayHasKey('name', $params);
        $this->assertArrayHasKey('sku', $params);
        $this->assertArrayHasKey('title', $params);
        $this->assertArrayHasKey('categories', $params);
        $this->assertArrayHasKey('stock', $params);
    }

    public function testGetParams()
    {
        $params = $this->provider->getParams();

        $this->assertCount(6, $params);

        $this->assertArrayHasKey('id', $params);
        $this->assertArrayHasKey('name', $params);
        $this->assertArrayHasKey('sku', $params);
        $this->assertArrayHasKey('title', $params);
        $this->assertArrayHasKey('categories', $params);
        $this->assertArrayHasKey('stock', $params);
    }

    public function testGetIdName()
    {
        $this->assertEquals('id', $this->provider->getIdName());
    }

    public function testHasUnique()
    {
        $this->assertTrue($this->provider->hasUnique());
    }

    public function testGetEntityClassName()
    {
        $this->assertEquals(Product::class, $this->provider->getEntityClassName());
    }

    public function testGetShortName()
    {
        $this->assertEquals('product', $this->provider->getShortName());
    }

    public function testGetBaseName()
    {
        $this->assertEquals('Product', $this->provider->getBaseName());
    }

    public function testGetEntitySpec()
    {
        $expected = [
            [
                'name' => 'id',
                'type' => 'integer',
                'nullable' => false,
                'reference' => null,
            ],
            [
                'name' => 'name',
                'type' => 'string',
                'nullable' => false,
                'reference' => null,
            ],
            [
                'name' => 'sku',
                'type' => 'string',
                'nullable' => false,
                'reference' => null,
            ],
            [
                'name' => 'title',
                'type' => 'string',
                'nullable' => true,
                'reference' => null,
            ],
            [
                'name' => 'categories',
                'type' => 'manytomany',
                'nullable' => true,
                'reference' => [
                    'entity' => '\\' . Category::class,
                    'ref_column_key' => 'id',
                ],
            ],
            [
                'name' => 'stock',
                'type' => 'onetoone',
                'nullable' => false,
                'reference' => [
                    'entity' => '\\' . Stock::class,
                    'ref_column_key' => 'id',
                ],
            ],
        ];

        $this->assertEquals($expected, $this->provider->getEntitySpec());
    }
}
