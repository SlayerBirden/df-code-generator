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

    public function getRoutesDelegatorNameSpace(): string
    {
        return $this->getNs($this->entityClassName);
    }

    public function getControllerNameSpace(): string
    {
        return $this->getControllerNs($this->entityClassName);
    }

    /**
     * @return string
     * @throws \ReflectionException
     */
    public function getShortName(): string
    {
        return $this->getBaseName($this->entityClassName);
    }
}
