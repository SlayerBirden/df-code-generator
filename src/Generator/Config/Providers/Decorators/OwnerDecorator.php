<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration\Generator\Config\Providers\Decorators;

use SlayerBirden\DFCodeGeneration\Generator\DataProvider\DataProviderDecoratorInterface;

final class OwnerDecorator implements DataProviderDecoratorInterface
{
    /**
     * @var string
     */
    private $entityClassName;

    public function __construct(string $entityClassName)
    {
        $this->entityClassName = $entityClassName;
    }

    /**
     * @param array $data
     * @return array
     * @throws \ReflectionException
     */
    public function decorate(array $data): array
    {
        $reflectionClassName = new \ReflectionClass($this->entityClassName);

        $hasOwner = false;
        foreach ($reflectionClassName->getProperties() as $property) {
            if ($property->getName() === 'owner') {
                $hasOwner = true;
                break;
            }
        }
        $data['has_owner'] = $hasOwner;

        return $data;
    }
}
