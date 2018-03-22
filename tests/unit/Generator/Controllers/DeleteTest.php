<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration\Generator\Controllers;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use SlayerBirden\DFCodeGeneration\Util\CodeLoader;
use SlayerBirden\DFCodeGeneration\PrintFileTrait;
use Zend\Diactoros\ServerRequest;
use Zend\Expressive\Handler\NotFoundHandler;
use Zend\Hydrator\ExtractionInterface;

class DeleteTest extends TestCase
{
    use PrintFileTrait;
    /**
     * @var ObjectProphecy
     */
    private $provider;

    protected function setUp()
    {
        $this->provider = $this->prophesize(DataProviderInterface::class);
        $this->provider->provide()->willReturn([
            'ns' => 'Dummy\\Controller',
            'entityName' => 'User',
            'hasUnique' => false,
            'uniqueIdxMessage' => 'Test Constraint violation',
            'useStatement' => 'Dummy\\Entities\\User',
            'dataRelationship' => '// testing here',
        ]);
        $this->provider->getClassName(Argument::type('string'))->willReturn('Dummy\\Controller\\DeleteUserAction');
    }

    public function testGenerate()
    {
        $generator = new Delete($this->provider->reveal());

        $body = $generator->generate();

        try {
            CodeLoader::loadCode($body, 'DeleteUserAction.php');
            $className = $generator->getClassName();
            $deleteAction = new $className(
                $this->prophesize(EntityManagerInterface::class)->reveal(),
                $this->prophesize(LoggerInterface::class)->reveal(),
                $this->prophesize(ExtractionInterface::class)->reveal()
            );
            $request = new ServerRequest();
            $delegator = $this->prophesize(NotFoundHandler::class);
            $this->initDangerMessage();
            $resp = $deleteAction->process($request, $delegator->reveal());
        } catch (\Throwable $exception) {
            echo 'File', PHP_EOL, $this->getPrintableFile($body), PHP_EOL;
            throw $exception;
        }

        $this->assertInstanceOf(ResponseInterface::class, $resp);
    }

    private function initDangerMessage()
    {
        $body = <<<'BODY'
<?php

namespace SlayerBirden\DataFlowServer\Notification;
class DangerMessage {}
BODY;
        CodeLoader::loadCode($body, 'danger.php');
    }
}
