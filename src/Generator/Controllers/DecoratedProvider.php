<?php
declare(strict_types=1);

namespace SlayerBirden\DFCodeGeneration\Generator\Controllers;

class DecoratedProvider extends SimpleProvider
{
    /**
     * @var DataProviderDecoratorInterface[]
     */
    private $decorators;

    public function __construct(string $entityClassName, DataProviderDecoratorInterface ...$decorators)
    {
        parent::__construct($entityClassName);
        $this->decorators = $decorators;
    }

    /**
     * @return array
     * @throws \ReflectionException
     */
    public function provide(): array
    {
        $data = parent::provide();

        foreach ($this->decorators as $decorator) {
            $data = $decorator->decorate($data);
        }

        return $data;
    }
}
