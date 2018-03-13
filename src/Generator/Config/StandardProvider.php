<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration\Generator\Config;

use SlayerBirden\DFCodeGeneration\Generator\Factory\NamingTrait as FactoryNamingTrait;
use SlayerBirden\DFCodeGeneration\Generator\Controllers\NamingTrait as ControllerNamingTrait;

class StandardProvider implements DataProviderInterface
{
    use FactoryNamingTrait, ControllerNamingTrait {
        FactoryNamingTrait::getBaseName insteadof ControllerNamingTrait;
        FactoryNamingTrait::getNs insteadof ControllerNamingTrait;
        FactoryNamingTrait::getNs as getFactoryNs;
        ControllerNamingTrait::getNs as getControllerNs;
    }

    /**
     * @var string
     */
    private $entityClassName;

    public function __construct(string $entityClassName)
    {
        $this->entityClassName = $entityClassName;
    }

    public function getRouteFactoryName(): string
    {
        return $this->getFactoryNs($this->entityClassName) . '\\RoutesDelegator';
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
        $baseName = $this->getBaseName($this->entityClassName);
        if ($type === 'gets') {
            $baseName .= 's';
            $type = 'get';
        }

        return $this->getControllerNs($this->entityClassName) . '\\' . ucwords($type) . $baseName . 'Action';
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
        return $this->getBaseName($this->entityClassName) . 'InputFilter';
    }
}
