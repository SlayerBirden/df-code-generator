<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration\Generator\Factory\Providers\Decorators;

use SlayerBirden\DFCodeGeneration\Generator\DataProvider\DataProviderDecoratorInterface;

class NameSpaceDecorator implements DataProviderDecoratorInterface
{
    /**
     * @var string
     */
    private $entityClassName;

    public function __construct(string $entityClassName)
    {
        $this->entityClassName = $entityClassName;
    }

    public function decorate(array $data): array
    {
        $data['factory_namespace'] = $this->getNamespace();

        return $data;
    }

    private function getNamespace(): string
    {
        $parts = explode('\\', $this->entityClassName);
        array_splice($parts, -2); // Entities\Model
        $parts[] = 'Factory';
        return ltrim(implode('\\', $parts), '\\');
    }
}
