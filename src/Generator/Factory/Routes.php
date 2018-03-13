<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration\Generator\Factory;

use SlayerBirden\DFCodeGeneration\Generator\Controllers\NamingTrait as ControllerNamingTrait;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class Routes
{
    use NamingTrait, ControllerNamingTrait {
        NamingTrait::getBaseName insteadof ControllerNamingTrait;
        NamingTrait::getNs insteadof ControllerNamingTrait;
        ControllerNamingTrait::getNs as getControllerNs;
    }

    /**
     * @var string
     */
    private $filePath;
    /**
     * @var string
     */
    private $entityClassName;

    public function __construct(string $filePath, string $entityClassName)
    {
        $this->filePath = $filePath;
        $this->entityClassName = $entityClassName;
    }

    /**
     * @return string
     * @throws \ReflectionException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function generate(): string
    {
        return file_exists($this->filePath) ? $this->updateExistingFile() : $this->generateNewFile();
    }

    /**
     * @return string
     * @throws \ReflectionException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    private function updateExistingFile(): string
    {
        $loader = new FilesystemLoader(__DIR__);
        $twig = new Environment($loader);

        $routes = $twig->load('Routes.twig')->render($this->getParams());

        $contents = file_get_contents($this->filePath);
        $parts = explode('#=====', $contents);
        if (count($parts) > 1) {
            $endPart = array_pop($parts);
            return implode('#=====', $parts) . '#=====' . $routes . $endPart;
        }

        return $this->generateNewFile();
    }

    /**
     * @return string
     * @throws \ReflectionException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    private function generateNewFile(): string
    {
        $loader = new FilesystemLoader(__DIR__);
        $twig = new Environment($loader);
        $routes = $twig->load('Routes.twig')->render($this->getParams());

        $params = $this->getParams();
        $params['routes'] = $routes;

        return $twig->load('RoutesDelegator.php.twig')->render($params);
    }

    /**
     * @return array
     * @throws \ReflectionException
     */
    private function getParams(): array
    {
        return [
            'ns' => $this->getNs($this->entityClassName),
            'controllerNs' => '\\' . $this->getControllerNs($this->entityClassName),
            'entityName' => $this->getBaseName($this->entityClassName),
        ];
    }
}
