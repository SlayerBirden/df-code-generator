<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration\Generator\Factory;

use SlayerBirden\DFCodeGeneration\Generator\Controllers\NamingTrait as ControllerNamingTrait;

class SimpleProvider implements DataProviderInterface
{
    use NamingTrait, ControllerNamingTrait {
        NamingTrait::getBaseName insteadof ControllerNamingTrait;
        NamingTrait::getNs insteadof ControllerNamingTrait;
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

    /**
     * @return array
     * @throws \ReflectionException
     */
    public function provide(): array
    {
        return [
            'ns' => $this->getNs($this->entityClassName),
            'controllerNs' => $this->getControllerNs($this->entityClassName),
            'entityName' => $this->getBaseName($this->entityClassName),
        ];
    }

    /**
     * @return string
     * @throws \ReflectionException
     */
    public function getClassName(): string
    {
        return $this->getNs($this->entityClassName) . '\\' . $this->getBaseName($this->entityClassName) . 'RoutesDelegator';
    }
}
