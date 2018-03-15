<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration\Generator\Controllers;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use SlayerBirden\DFCodeGeneration\CodeLoader;
use SlayerBirden\DFCodeGeneration\PrintFileTrait;
use Zend\Diactoros\ServerRequest;
use Zend\Expressive\Handler\NotFoundHandler;
use Zend\Hydrator\ExtractionInterface;
use Zend\Hydrator\HydratorInterface;
use Zend\InputFilter\InputFilterInterface;

class UpdateTest extends TestCase
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
        $this->provider->getClassName(Argument::type('string'))->willReturn('Dummy\\Controller\\UpdateUserAction');
    }

    public function testGenerate()
    {
        $generator = new Update($this->provider->reveal());

        $body = $generator->generate();

        try {
            CodeLoader::loadCode($body, 'UpdateUserAction.php');
            $className = $generator->getClassName();

            $inputFilter = $this->prophesize(InputFilterInterface::class);
            $inputFilter->setData(Argument::any())->shouldBeCalled();
            $inputFilter->isValid()->willReturn(false);
            $inputFilter->getInvalidInput()->willReturn([]);

            $action = new $className(
                $this->prophesize(EntityManagerInterface::class)->reveal(),
                $this->prophesize(HydratorInterface::class)->reveal(),
                $inputFilter->reveal(),
                $this->prophesize(LoggerInterface::class)->reveal(),
                $this->prophesize(ExtractionInterface::class)->reveal()
            );

            $request = new ServerRequest();
            $delegator = $this->prophesize(NotFoundHandler::class);

            $resp = $action->process($request, $delegator->reveal());
        } catch (\Throwable $exception) {
            echo 'File', PHP_EOL, $this->getPrintableFile($body), PHP_EOL;
            throw $exception;
        }

        $this->assertInstanceOf(ResponseInterface::class, $resp);
    }
}
