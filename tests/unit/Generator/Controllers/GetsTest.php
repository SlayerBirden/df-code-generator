<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration\Generator\Controllers;

use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMException;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\MethodProphecy;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use SlayerBirden\DFCodeGeneration\Util\CodeLoader;
use SlayerBirden\DFCodeGeneration\PrintFileTrait;
use Zend\Diactoros\ServerRequest;
use Zend\Expressive\Handler\NotFoundHandler;
use Zend\Hydrator\ExtractionInterface;

class GetsTest extends TestCase
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
            'pluralEntityName' => 'Users',
            'hasUnique' => false,
            'uniqueIdxMessage' => 'Test Constraint violation',
            'useStatement' => 'Dummy\\Entities\\User',
            'dataRelationship' => '// testing here',
        ]);
        $this->provider->getClassName(Argument::type('string'))->willReturn('Dummy\\Controller\\GetUsersAction');
    }

    public function testGenerate()
    {
        $generator = new Gets($this->provider->reveal());

        $body = $generator->generate();

        $em = $this->prophesize(EntityManagerInterface::class);
        $em->addMethodProphecy(
            (new MethodProphecy($em, 'getRepository', [Argument::any()]))
                ->willThrow(new ORMException())
        );

        try {
            CodeLoader::loadCode($body, 'GetUsersAction.php');
            $className = $generator->getClassName();
            $deleteAction = new $className(
                $em->reveal(),
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
