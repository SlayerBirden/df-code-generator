<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration\Generator\Controllers;

use SlayerBirden\DFCodeGeneration\Generator\BaseNameTrait;

class SimpleProvider implements DataProviderInterface
{
    use BaseNameTrait;

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
            'ns' => $this->getNs(),
            'useStatement' => ltrim($this->entityClassName, '\\'),
            'entityName' => $this->getBaseName(),
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
            $className = 'Get' . $this->getBaseName() . 'sAction';
        } else {
            $className = ucwords($type) . $this->getBaseName() . 'Action';
        }

        return $this->getNs() . "\\$className";
    }

    private function getNs(): string
    {
        $parts = explode('\\', $this->entityClassName);
        array_splice($parts, -2); // Entities\Model

        $parts[] = 'Controller';

        return ltrim(implode('\\', $parts), '\\');
    }
}
