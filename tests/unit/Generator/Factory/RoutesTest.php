<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration\Generator\Factory;

use Interop\Container\ContainerInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use SlayerBirden\DFCodeGeneration\Util\CodeLoader;
use SlayerBirden\DFCodeGeneration\PrintFileTrait;
use Zend\Expressive\Application;
use Zend\Expressive\Router\RouterInterface;
use Zend\ServiceManager\Factory\DelegatorFactoryInterface;

class RoutesTest extends TestCase
{
    use PrintFileTrait;
    /**
     * @var ObjectProphecy
     */
    private $provider;
    private $router;
    /**
     * @var Application
     */
    private $app;

    protected function setUp()
    {
        $this->provider = $this->prophesize(DataProviderInterface::class);
        $this->provider->provide()->willReturn([
            'ns' => 'Dummy\\Factory',
            'controllerNs' => 'Dummy\\Controller',
            'entityName' => 'Dummy',
            'pluralEntityName' => 'Dummies',
        ]);
        $this->provider->getClassName()->willReturn('Dummy\\Factory\\DummyRoutesDelegator');

        $this->router = $this->prophesize(RouterInterface::class);

        $this->app = new Application(
            $this->router->reveal()
        );
    }

    public function testAddRoutesDelegator()
    {
        $routesGenerator = new Routes($this->provider->reveal());
        $body = $routesGenerator->generate();
        $this->loadDummyControllers();

        try {
            CodeLoader::loadCode($body, 'dummyRoutesDelegator.php');
            $class = $routesGenerator->getClassName();
            /** @var DelegatorFactoryInterface $delegator */
            $delegator = new $class();

            $container = $this->prophesize(ContainerInterface::class);

            /** @var Application $app */
            $app = $delegator($container->reveal(), 'dummy', function () {
                return $this->app;
            });

            $routes = $app->getRoutes();
        } catch (\ParseError $exception) {
            echo 'File', PHP_EOL, $this->getPrintableFile($body), PHP_EOL;
            throw $exception;
        }

        $this->assertCount(5, $routes);
    }

    private function loadDummyControllers()
    {
        $template = <<<'CONTROLLER'
<?php
namespace Dummy\Controller;
        
use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;

class %s implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, DelegateInterface $handler): ResponseInterface
    {
        return $handler->process($request);
    }
}
CONTROLLER;

        $actions = [
            'GetDummyAction',
            'GetDummiesAction',
            'AddDummyAction',
            'DeleteDummyAction',
            'UpdateDummyAction'
        ];
        foreach ($actions as $action) {
            $body = sprintf($template, $action);
            CodeLoader::loadCode($body, "$action.php");
        }
    }

    public function testGetClass()
    {
        $this->assertSame('Dummy\\Factory\\DummyRoutesDelegator',
            (new Routes($this->provider->reveal()))->getClassName());
    }
}
