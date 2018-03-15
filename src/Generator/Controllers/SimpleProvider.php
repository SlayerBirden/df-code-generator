<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration\Generator\Controllers;

class SimpleProvider implements DataProviderInterface
{
    use NamingTrait;

    /**
     * @var string
     */
    protected $entityClassName;

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
            'useStatement' => ltrim($this->entityClassName, '\\'),
            'entityName' => $this->getBaseName($this->entityClassName),
        ];
    }

    /**
     * @param string $type
     * @return string
     * @throws \ReflectionException
     */
    public function getClassName(string $type): string
    {
        if ($type === 'gets') {
            $className = 'Get' . $this->getBaseName($this->entityClassName) . 'sAction';
        } else {
            $className = ucwords($type) . $this->getBaseName($this->entityClassName) . 'Action';
        }

        return $this->getNs($this->entityClassName) . "\\$className";
    }
}
