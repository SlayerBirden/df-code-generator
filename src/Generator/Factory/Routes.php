<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration\Generator\Factory;

use SlayerBirden\DFCodeGeneration\Generator\GeneratorInterface;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class Routes implements GeneratorInterface
{
    /**
     * @var DataProviderInterface
     */
    private $provider;

    public function __construct(DataProviderInterface $provider)
    {
        $this->provider = $provider;
    }

    /**
     * @return string
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function generate(): string
    {
        $loader = new FilesystemLoader(__DIR__);
        $twig = new Environment($loader);

        return $twig->load('RoutesDelegator.php.twig')->render($this->getParams());
    }

    private function getParams(): array
    {
        return [
            'ns' => $this->provider->getRoutesDelegatorNameSpace(),
            'controllerNs' => $this->provider->getControllerNameSpace(),
            'entityName' => $this->provider->getShortName(),
        ];
    }

    public function getClassName(): string
    {
        return $this->provider->getRoutesDelegatorNameSpace() . '\\' . $this->provider->getShortName() . 'RoutesDelegator';
    }
}
