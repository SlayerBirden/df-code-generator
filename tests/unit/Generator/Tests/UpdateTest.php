<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration\Generator\Tests;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use SlayerBirden\DFCodeGeneration\Util\CodeLoader;
use SlayerBirden\DFCodeGeneration\PrintFileTrait;

class UpdateTest extends TestCase
{
    use PrintFileTrait;

    private $provider;
    private $factory;

    protected function setUp()
    {
        $this->provider = $this->prophesize(EntityProviderInterface::class);
        $this->factory = $this->prophesize(EntityProviderFactoryInterface::class);
        $this->factory->create(Argument::any())->willReturn($this->provider->reveal());
    }

    /**
     * @doesNotPerformAssertions
     */
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

        $update = new Update('Dummy\\User', $this->factory->reveal(), new NullValuesRandomizer(.5));

        $code = $update->generate();
        $this->assertNotEmpty($code);

        // asserting php code is valid and can be loaded
        try {
            CodeLoader::loadCode($code, 'UpdateCest.php');
        } catch (\Throwable $exception) {
            echo 'File', PHP_EOL, $this->getPrintableFile($code), PHP_EOL;
            throw $exception;
        }
    }
}
