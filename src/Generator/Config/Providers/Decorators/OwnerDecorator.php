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
     */
    public function decorate(array $data): array
    {
        $data['has_owner'] = in_array(
            'SlayerBirden\DataFlowServer\Domain\Entities\ClaimedResourceInterface',
            class_implements($this->entityClassName),
            true
        );

        return $data;
    }
}
