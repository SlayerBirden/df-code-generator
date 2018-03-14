<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration\Generator\Factory;

use Interop\Container\ContainerInterface;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
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
        $this->provider->getRoutesDelegatorNameSpace()->willReturn('Dummy\\Factory');
        $this->provider->getControllerNameSpace()->willReturn('Dummy\\Controller');
        $this->provider->getShortName()->willReturn('Dummy');

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

        $root = vfsStream::setup();
        file_put_contents($root->url() . '/dummyRoutesDelegator.php', $body);
        try {
            include $root->url() . '/dummyRoutesDelegator.php';
        } catch (\ParseError $exception) {
            echo 'File', PHP_EOL, $this->getPrintableFile($body), PHP_EOL;
            throw $exception;
        }

        $class = $routesGenerator->getClassName();
        /** @var DelegatorFactoryInterface $delegator */
        $delegator = new $class();

        $container = $this->prophesize(ContainerInterface::class);

        /** @var Application $app */
        $app = $delegator($container->reveal(), 'dummy', function () {
            return $this->app;
        });

        $routes = $app->getRoutes();

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
            'GetDummysAction',
            'AddDummyAction',
            'DeleteDummyAction',
            'UpdateDummyAction'
        ];
        $root = vfsStream::setup();
        foreach ($actions as $action) {
            $body = sprintf($template, $action);
            file_put_contents($root->url() . "/$action.php", $body);
            include $root->url() . "/$action.php";
        }
    }

    public function testGetClass()
    {
        $this->assertSame('Dummy\\Factory\\DummyRoutesDelegator',
            (new Routes($this->provider->reveal()))->getClassName());
    }
}
