<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration\Generator\Config\Providers\Decorators;

use SlayerBirden\DFCodeGeneration\Generator\DataProvider\DataProviderDecoratorInterface;

final class EntitiesSrcDecorator implements DataProviderDecoratorInterface
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
        $data['entities_src'] = $this->getEntitiesSrc();

        return $data;
    }

    private function getEntitiesSrc(): string
    {
        // expect to have 3d part as Module
        $parts = explode('\\', $this->entityClassName);
        if (isset($parts[2])) {
            return sprintf('src/%s/Entities', $parts[2]);
        }

        return '';
    }
}
