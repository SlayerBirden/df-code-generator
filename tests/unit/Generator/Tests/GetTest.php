<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration\Generator\Tests;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use SlayerBirden\DFCodeGeneration\CodeLoader;

class GetTest extends TestCase
{
    private $provider;
    private $factory;

    protected function setUp()
    {
        $this->provider = $this->prophesize(EntityProviderInterface::class);
        $this->factory = $this->prophesize(EntityProviderFactoryInterface::class);
        $this->factory->create(Argument::any())->willReturn($this->provider->reveal());
    }

    public function testCreateClass()
    {
        $this->provider->getId()->willReturn(1);
        $this->provider->getEntitySpec()->willReturn([
            [
                'name' => 'id',
                'type' => 'integer',
                'nullable' => false,
            ],
            [
                'name' => 'name',
                'type' => 'string',
                'nullable' => true,
            ],
            [
                'name' => 'email',
                'type' => 'string',
                'nullable' => false,
            ],
        ]);
        $this->provider->getPostParams()->willReturn([
            'name' => 'bob',
            'email' => 'bob@google.example.com',
        ]);
        $this->provider->getParams()->willReturn([
            'id' => 1,
            'name' => 'bob',
            'email' => 'bob@google.example.com',
        ]);
        $this->provider->getBaseName()->willReturn('User');
        $this->provider->getShortName()->willReturn('user');
        $this->provider->getEntityClassName()->willReturn('Dummy\\User');
        $this->provider->hasUnique()->willReturn(true);
        $this->provider->getIdName()->willReturn('id');

        $get = new Get('Dummy\\User', $this->factory->reveal());

        $code = $get->generate();
        $this->assertNotEmpty($code);

        // asserting php code is valid and can be loaded
        CodeLoader::loadCode($code, 'GetCest.php');
    }
}
