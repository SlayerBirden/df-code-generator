<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration\Generator\Config;

use SlayerBirden\DFCodeGeneration\Generator\BaseNameTrait;
use SlayerBirden\DFCodeGeneration\Generator\Factory\SimpleProvider as FactoryProvider;
use SlayerBirden\DFCodeGeneration\Generator\Controllers\SimpleProvider as ControllerProvider;
use SlayerBirden\DFCodeGeneration\Util\Lexer;

class StandardProvider implements DataProviderInterface
{
    use BaseNameTrait;
    /**
     * @var string
     */
    private $entityClassName;

    public function __construct(string $entityClassName)
    {
        $this->entityClassName = $entityClassName;
    }

    /**
     * @return string
     * @throws \ReflectionException
     */
    public function getRouteFactoryName(): string
    {
        $baseName = $this->getBaseName();
        $factoryParams = (new FactoryProvider($this->entityClassName))->provide();

        return $factoryParams['ns'] . "\\{$baseName}RoutesDelegator";
    }

    /**
     * @return array
     * @throws \Doctrine\Common\Annotations\AnnotationException
     * @throws \ReflectionException
     */
    public function getInputFilterSpec(): array
    {
        return (new ReflectionInputFilter($this->entityClassName))->getSpec();
    }

    public function getEntitiesSrc(): string
    {
        // expect to have 3d part as Module
        $parts = explode('\\', $this->entityClassName);
        if (isset($parts[2])) {
            return sprintf('src/%s/Entities', $parts[2]);
        }

        return '';
    }

    public function getCurrentConfig(): array
    {
        $config = $this->getConfigNameSpace() . '\\' . 'ConfigProvider';

        if (class_exists($config)) {
            return (new $config())();
        }

        return [];
    }

    /**
     * @param string $type
     * @return string
     * @throws \ReflectionException
     */
    public function getControllerName(string $type): string
    {
        $baseName = $this->getBaseName();
        $controllerParams = (new ControllerProvider($this->entityClassName))->provide();
        if ($type === 'gets') {
            $baseName = Lexer::getPluralForm($baseName);
            $type = 'get';
        }

        return $controllerParams['ns'] . '\\' . ucwords($type) . $baseName . 'Action';
    }

    public function getConfigNameSpace(): string
    {
        $parts = explode('\\', $this->entityClassName);
        array_splice($parts, -2); // Entities\Model

        return ltrim(implode('\\', $parts), '\\');
    }

    /**
     * @return string
     * @throws \ReflectionException
     */
    public function getInputFilterName(): string
    {
        return $this->getBaseName() . 'InputFilter';
    }
}
